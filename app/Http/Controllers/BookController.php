<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use Illuminate\Support\Facades\Storage;


class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $books = Book::with(['author', 'category', 'ratings.user'])->get();
        return response()->json(['books' => BookResource::collection($books)], 200);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        $book = $request->createBookWithCopies();
        return response()->json([
            'message' => 'Book and copies created successfully',
            'book' => $book,
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $book = Book::with(['author', 'category', 'ratings.user'])->findOrFail($id);
        $copies = BookCopy::where('book_id', $book->id)->get();

        return response()->json([
            'book' => new BookResource($book),
            'copies' => $copies,
        ]);
    }



    // Update book info

    public function update(UpdateBookRequest $request, $id)
    {
        $book = Book::findOrFail($id);

        $book->fill($request->validated());

        if ($request->hasFile('cover_image')) {
            $book->cover_image = $request->file('cover_image')->store('covers', 'public');
        }

        if ($request->hasFile('file_path')) {
            $book->file_path = $request->file('file_path')->store('books', 'public');
        }

        if ($request->hasFile('sound_path')) {
            $book->sound_path = $request->file('sound_path')->store('audio', 'public');
        }

        $book->save();

        return response()->json([
            'message' => 'Book updated successfully',
            'book' => new BookResource($book),
        ]);
    }


    private function deleteBookFiles(Book $book)
    {
        foreach (['cover_image', 'file_path', 'sound_path'] as $fileField) {
            if ($book->$fileField && Storage::disk('public')->exists($book->$fileField)) {
                Storage::disk('public')->delete($book->$fileField);
            }
        }
    }

    // Delete a book
    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        $this->deleteBookFiles($book);
        $book->delete();

        return response()->json(['message' => 'Book deleted successfully']);
    }


    public function latestReleases()
    {
        $books = Book::latest()->take(10)->get();
        return response()->json(['books' => BookResource::collection($books)], 200);
    }



    public function bestSellers()
    {
        $books = Book::select('books.*', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->join('order_items', 'books.id', '=', 'order_items.book_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('books.id')
            ->orderByDesc('total_sold')
            ->take(10)
            ->get();

        return response()->json(['books' => BookResource::collection($books)], 200);
    }


    public function search(Request $request)
    {
        $query = Book::with(['author', 'category']);

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', "%$keyword%")
                    ->orWhere('preview', 'LIKE', "%$keyword%")
                    ->orWhere('publisher', 'LIKE', "%$keyword%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $books = $query->get();

        return response()->json([
            'books' => BookResource::collection($books),
            'total' => $books->count(),
        ]);
    }
}
