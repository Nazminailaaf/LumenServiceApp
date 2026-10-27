<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostsController extends Controller
{
    // GET /api/posts?status=draft&category_id=1&q=kata&per_page=10
    public function index(Request $request)
    {
        $query = Post::with(['user:id,name', 'category:id,name', 'tags:id,name'])
            ->withCount('comments');

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->has('q')) {
            $keyword = $request->input('q');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('content', 'like', "%{$keyword}%");
            });
        }

        $posts = $query->orderBy('id', 'DESC')
            ->paginate((int) $request->input('per_page', 10));

        return response()->json($posts, 200);
    }

    // GET /api/posts/stats  (contoh query agregat: groupBy + count)
    public function stats()
    {
        $perStatus = Post::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $perCategory = DB::table('posts')
            ->leftJoin('categories', 'categories.id', '=', 'posts.category_id')
            ->select('categories.name as category', DB::raw('COUNT(posts.id) as total'))
            ->groupBy('categories.name')
            ->get();

        return response()->json([
            'message'      => 'stats',
            'total_posts'  => Post::count(),
            'per_status'   => $perStatus,
            'per_category' => $perCategory,
        ], 200);
    }

    // GET /api/posts/{id}
    public function show($id)
    {
        $post = Post::with(['user:id,name', 'category', 'tags', 'comments.user:id,name'])->find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return response()->json($post, 200);
    }

    // POST /api/posts
    public function store(Request $request)
    {
        $this->validate($request, [
            'title'       => 'required|max:100',
            'content'     => 'required',
            'status'      => 'in:draft,published',
            'user_id'     => 'required|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
            'tag_ids'     => 'array',
            'tag_ids.*'   => 'exists:tags,id',
        ]);

        $post = Post::create($request->only(['title', 'content', 'status', 'user_id', 'category_id']));

        if ($request->has('tag_ids')) {
            $post->tags()->sync($request->input('tag_ids'));
        }

        return response()->json([
            'message' => 'Post created',
            'result'  => $post->load('tags'),
        ], 201);
    }

    // PUT /api/posts/{id}
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $this->validate($request, [
            'title'       => 'max:100',
            'status'      => 'in:draft,published',
            'user_id'     => 'exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
            'tag_ids'     => 'array',
            'tag_ids.*'   => 'exists:tags,id',
        ]);

        $post->update($request->only(['title', 'content', 'status', 'user_id', 'category_id']));

        if ($request->has('tag_ids')) {
            $post->tags()->sync($request->input('tag_ids'));
        }

        return response()->json([
            'message' => 'Post updated',
            'result'  => $post->load('tags'),
        ], 200);
    }

    // DELETE /api/posts/{id}
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted'], 200);
    }
}
