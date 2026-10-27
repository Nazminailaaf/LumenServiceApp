<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentsController extends Controller
{
    // GET /api/posts/{postId}/comments
    public function index($postId)
    {
        if (!Post::find($postId)) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $comments = Comment::with('user:id,name')
            ->where('post_id', $postId)
            ->orderBy('id', 'DESC')
            ->paginate(10);

        return response()->json($comments, 200);
    }

    // POST /api/posts/{postId}/comments
    public function store(Request $request, $postId)
    {
        if (!Post::find($postId)) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $this->validate($request, [
            'user_id' => 'required|exists:users,id',
            'body'    => 'required',
        ]);

        $comment = Comment::create([
            'post_id' => $postId,
            'user_id' => $request->input('user_id'),
            'body'    => $request->input('body'),
        ]);

        return response()->json(['message' => 'Comment created', 'result' => $comment], 201);
    }

    // GET /api/comments/{id}
    public function show($id)
    {
        $comment = Comment::with(['user:id,name', 'post:id,title'])->find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        return response()->json($comment, 200);
    }

    // PUT /api/comments/{id}
    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $this->validate($request, ['body' => 'required']);

        $comment->update($request->only(['body']));

        return response()->json(['message' => 'Comment updated', 'result' => $comment], 200);
    }

    // DELETE /api/comments/{id}
    public function destroy($id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted'], 200);
    }
}
