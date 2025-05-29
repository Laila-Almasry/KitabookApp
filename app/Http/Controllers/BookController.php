<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Models\Book;
use App\Models\BookCopy;
use App\Services\BookService;
use BookService as GlobalBookService;
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
        $book->cover_image_url = $book->cover_image ? 'http://127.0.0.1:8000/storage/' . $book->cover_image : null;
        $book->file_path_url = $book->file_path ? 'http://127.0.0.1:8000/storage/' . $book->file_path : null;
        $book->sound_path_url = $book->sound_path ? 'http://127.0.0.1:8000/storage/' . $book->sound_path : null;

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
    $book->cover_image_url = $book->cover_image ? 'http://127.0.0.1:8000/storage/' . $book->cover_image : null;
    $book->file_path_url = $book->file_path ? 'http://127.0.0.1:8000/storage/' . $book->file_path : null;
    $book->sound_path_url = $book->sound_path ? 'http://127.0.0.1:8000/storage/' . $book->sound_path : null;

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


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

public function latestReleases()
{
    $books = Book::latest('created_at')->take(10)->get();
    return response()->json($books);
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

    return response()->json($books);
}


}
