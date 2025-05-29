<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index(){
        $authors=Author::all();
        return response()->json(['authors'=>$authors],200);
    }

    public function store(Request $request) {
         $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'about' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('authors_images', 'public');
        }

        $author = Author::create($validated);

        return response()->json([
            'message' => 'Author created successfully',
            'author' => $author
        ], 201);
    }

     public function show($id)
    {
        // Find the author by ID or fail with a 404 error
        $author = Author::findOrFail($id);
        // Generate the URL for the author's image if it exists
        $author->image_url = $author->image ? 'http://127.0.0.1:8000/storage/' . $author->image : null;
        $author->books=Book::where('author_id','=',$id)->get();
        return response()->json([
            'author' => $author
        ], 200);
    }
}
