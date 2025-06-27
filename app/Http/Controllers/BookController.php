<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */

public function index()
{
    $books = Book::with([
        'author',
        'category',
        'ratings.user' // Include user info for ratings
    ])->get();

    // Format each book with additional data
    $books = $books->map(function ($book) {
        // Generate URLs
        $book->cover_image_url = $book->cover_image ? env('APP_URL').':8000/storage/' . $book->cover_image : null;
        $book->file_path_url = $book->file_path ? env('APP_URL').':8000/storage/' . $book->file_path : null;
        $book->sound_path_url = $book->sound_path ? env('APP_URL').':8000/storage/' . $book->sound_path : null;

        // Build clean ratings list with user info
        $book->ratings = $book->ratings->map(function ($rating) {
            return [
                'user_id' => $rating->user_id,
                'user_name' => $rating->user->name ?? 'Unknown',
                'rating' => $rating->rating,
                'comment' => $rating->comment,
                'created_at' => $rating->created_at->toDateTimeString(),
            ];
        });

        return $book;
    });

    return response()->json([
        'books' => $books
    ], 200);
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
    $book = Book::with([
        'author',
        'category',
        'ratings.user' // Include user info for ratings
    ])->findOrFail($id);

    $bookCopies = BookCopy::where('book_id', $id)->get();

    // Generate URLs
    $book->cover_image_url = $book->cover_image ? asset('storage/' . $book->cover_image) : null;
    $book->file_path_url = $book->file_path ? asset('storage/' . $book->file_path) : null;
    $book->sound_path_url = $book->sound_path ? asset('storage/' . $book->sound_path) : null;
    
    // Build clean ratings list with user info
    $ratings = $book->ratings->map(function ($rating) {
        return [
            'user_id' => $rating->user_id,
            'user_name' => $rating->user->name ?? 'Unknown',
            'rating' => $rating->rating,
            'comment' => $rating->comment,
            'created_at' => $rating->created_at->toDateTimeString(),
        ];
    });

    return response()->json([
        'book' => $book,
        'copies' => $bookCopies,
        'ratings' => $ratings,
        'avg_rating' => $book->rating,
        'raters_count' => $book->raterscount,
    ]);
}


 // Update book info
public function update(Request $request, $id)
{
    $book = Book::findOrFail($id);

    $validated = $request->validate([
        'title' => 'nullable|string|max:255',
        'preview' => 'nullable|string',
        'price' => 'nullable|numeric',
        'publisher' => 'nullable|string|max:255',
        'language' => 'nullable|string|in:english,arabic,french',
        'author_id' => 'nullable|exists:authors,id',
        'category_id' => 'nullable|exists:categories,id',
        'cover_image' => 'nullable|image|max:2048',
        'file_path' => 'nullable|mimes:pdf|max:10240',
        'sound_path'    => 'nullable|file|mimes:mpeg,mp3|max:51200', // optional audio file (max 50MB)

    ]);

    $book->fill($validated);

    if ($request->hasFile('cover_image')) {
        $book->cover_image = $request->file('cover_image')->store('covers', 'public');
    }

    if ($request->hasFile('file_path')) {
        $book->file_path = $request->file('file_path')->store('books', 'public');
    }
    if ($request->hasFile('audio_path')) {
        $book->audio_path = $request->file('audio_path')->store('audio', 'public');
    }

    $book->save();

    return response()->json(['message' => 'Book updated successfully', 'book' => $book]);
}


    // Delete a book
    public function destroy($id)
    {
        $book = Book::findOrFail($id);

            $book->book_copies()->delete();

        // $book->copies()->delete();
$book->delete();


        return response()->json(['message' => 'Book deleted successfully']);
    }


public function latestReleases()
{
    $books = Book::latest('created_at')->take(10)->get();
    return response()->json(['books'=>$books],200);
}



public function bestSellers()
{
    $books = Book::select('books.*', DB::raw('SUM(order_items.quantity) as total_sold'))
        ->join('order_items', 'books.id', '=', 'order_items.book_id')
        ->join('orders', 'orders.id', '=', 'order_items.order_id')
        ->where('orders.status', '!=', 'cancelled') // optional: ignore cancelled orders
        ->groupBy('books.id')
        ->orderByDesc('total_sold')
        ->take(10)
        ->get();

    return response()->json(['books'=>$books],200);
}

public function search(Request $request)
{
    $query = Book::query()->with(['author', 'category']);

    if ($request->has('keyword')) {
        $keyword = $request->keyword;

        $query->where(function ($q) use ($keyword) {
            $q->where('title', 'LIKE', '%' . $keyword . '%')
              ->orWhere('preview', 'LIKE', '%' . $keyword . '%')
              ->orWhere('publisher', 'LIKE', '%' . $keyword . '%');
        });
    }

    if ($request->has('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    if ($request->has('author_id')) {
        $query->where('author_id', $request->author_id);
    }

    // language filter
    if ($request->has('language')) {
        $query->where('language', $request->language);
    }
       // Filter by price range
    if ($request->filled('min_price')) {
        $query->where('price', '>=', $request->min_price);
    }
    if ($request->filled('max_price')) {
        $query->where('price', '<=', $request->max_price);
    }

    $books = $query->get();

    $books->map(function ($book) {
        $book->cover_image_url = $book->cover_image ? asset('storage/' . $book->cover_image) : null;
        $book->file_path_url = $book->file_path ? asset('storage/' . $book->file_path) : null;
        $book->sound_path_url = $book->sound_path ? asset('storage/' . $book->sound_path) : null;
        return $book;
    });

    return response()->json([
        'books' => $books,
        'total' => $books->count(),
    ]);
}
}
