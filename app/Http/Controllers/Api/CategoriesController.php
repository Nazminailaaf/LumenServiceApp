<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    // GET /api/categories
    public function index()
    {
        $categories = Category::withCount('posts')->orderBy('name', 'ASC')->get();

        return response()->json(['message' => 'categories', 'results' => $categories], 200);
    }

    // GET /api/categories/{id}
    public function show($id)
    {
        $category = Category::with('posts:id,category_id,title,status')->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category, 200);
    }

    // POST /api/categories
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'        => 'required|max:100|unique:categories,name',
            'description' => 'nullable',
        ]);

        $category = Category::create($request->only(['name', 'description']));

        return response()->json(['message' => 'Category created', 'result' => $category], 201);
    }

    // PUT /api/categories/{id}
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $this->validate($request, [
            'name'        => 'max:100|unique:categories,name,' . $id,
            'description' => 'nullable',
        ]);

        $category->update($request->only(['name', 'description']));

        return response()->json(['message' => 'Category updated', 'result' => $category], 200);
    }

    // DELETE /api/categories/{id}
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted'], 200);
    }
}
