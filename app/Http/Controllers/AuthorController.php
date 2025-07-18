<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AuthorController extends Controller
{
    public function index(){
        $authors=Author::all();
        for($i=0;$i<count($authors);$i++){
                    $authors[$i]->image_url = $authors[$i]->image ? env('APP_URL').':8000/storage/' . $authors[$i]->image : null;
        }
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
        $author->image_url = $author->image ? env('APP_URL').':8000/storage/' . $author->image : null;
        $author->books=Book::where('author_id','=',$id)->get();
        for($i=0;$i<count($author->books);$i++){
                $author->books[$i]->cover_image_url = $author->books[$i]->cover_image ? env('APP_URL').':8000/storage/' . $author->books[$i]->cover_image : null;
        $author->books[$i]->file_path_url = $author->books[$i]->file_path ? env('APP_URL').':8000/storage/' . $author->books[$i]->file_path : null;
        $author->books[$i]->sound_path_url = $author->books[$i]->sound_path ? env('APP_URL').':8000/storage/' . $author->books[$i]->sound_path : null;
        }
        return response()->json([
            'author' => $author
        ], 200);
    }

     // Update an author
    public function update(Request $request, $id) {
        $author = Author::findOrFail($id);

        $validated = $request->validate([
            'fullname' => 'sometimes|string|max:255',
            'image' => 'nullable|image|max:2048',
            'about' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            // Delete the old image
            if ($author->image) {
                Storage::disk('public')->delete($author->image);
            }
            $validated['image'] = $request->file('image')->store('authors_images', 'public');
        }

        $author->update($validated);
        $author->image_url = $author->image ? asset('storage/' . $author->image) : null;

        return response()->json([
            'message' => 'Author updated successfully',
            'author' => $author
        ], 200);
    }
     // Delete an author
    public function destroy($id) {
        $author = Author::findOrFail($id);

        // Delete image if exists
        if ($author->image) {
            Storage::disk('public')->delete($author->image);
        }

        $author->delete();

        return response()->json([
            'message' => 'Author deleted successfully'
        ], 200);
    }
}
