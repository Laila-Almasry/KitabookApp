<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrUpdateBookRatingRequest;
use App\Models\Book;
use App\Models\BookRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookRatingController extends Controller
{
    public function storeOrUpdate(StoreOrUpdateBookRatingRequest $request, $bookId)
    {
        $book = Book::findOrFail($bookId);
        BookRating::updateOrCreate(
            ['user_id' => Auth::user()->id, 'book_id' => $book->id],
            ['rating' => $request->rating, 'comment' => $request->comment]
        );

        $this->updateBookStats($book);

        return response()->json(['message' => 'Rating submitted successfully']);
    }

    public function destroy($bookId)
    {
        $rating = BookRating::where('user_id', Auth::user()->id)
            ->where('book_id', $bookId)
            ->firstOrFail();

        $rating->delete();

        $book = Book::findOrFail($bookId);
        $this->updateBookStats($book);

        return response()->json(['message' => 'Rating deleted successfully']);
    }

    private function updateBookStats(Book $book)
    {
        $stats = $book->ratings()
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
            ->first();  // a raw SQL query to select the average rating and count of ratings from the ratings relationship of the Book model

        $book->update([
            'rating' => round($stats->avg_rating ?? 0, 2),
            'raterscount' => $stats->count ?? 0,
        ]);
    }
}
