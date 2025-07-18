<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    // Get all categories
    public function index()
    {
        $categories = Category::all();

        $categories->map(function ($category) {
            $category->image_url = $category->image ? asset('storage/' . $category->image) : null;
        });

        return response()->json(['categories' => $categories], 200);
    }

    // Create a new category
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);



        $category = Category::create($validated);
        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    // Show a specific category
    public function show($id)
    {
        $category = Category::findOrFail($id);

        return response()->json(['category' => $category], 200);
    }

    // Update a category
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
        ]);


        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ], 200);
    }

    // Delete a category
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ], 200);
    }
}
