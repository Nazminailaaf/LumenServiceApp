<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    // GET /api/tags
    public function index()
    {
        $tags = Tag::withCount('posts')->orderBy('name', 'ASC')->get();

        return response()->json(['message' => 'tags', 'results' => $tags], 200);
    }

    // GET /api/tags/{id}
    public function show($id)
    {
        $tag = Tag::with('posts:id,title,status')->find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        return response()->json($tag, 200);
    }

    // POST /api/tags
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50|unique:tags,name',
        ]);

        $tag = Tag::create($request->only(['name']));

        return response()->json(['message' => 'Tag created', 'result' => $tag], 201);
    }

    // PUT /api/tags/{id}
    public function update(Request $request, $id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        $this->validate($request, [
            'name' => 'required|max:50|unique:tags,name,' . $id,
        ]);

        $tag->update($request->only(['name']));

        return response()->json(['message' => 'Tag updated', 'result' => $tag], 200);
    }

    // DELETE /api/tags/{id}
    public function destroy($id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        $tag->delete();

        return response()->json(['message' => 'Tag deleted'], 200);
    }
}
