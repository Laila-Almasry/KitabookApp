<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Http\Request;

class BookCopyController extends Controller
{
    public function getBookByCopyBarcode(Request $request)
{
    $request->validate([
        'barcode' => 'required|string'
    ]);

    $bookCopy = BookCopy::where('barcode', $request->barcode)->first();

    if (!$bookCopy) {
        return response()->json([
            'message' => 'Book copy not found',
            'status' => 'not_found'
        ], 200);
    }

    if ($bookCopy->status !== 'available') {
        return response()->json([
            'message' => 'Book copy unavailable',
            'status' => $bookCopy->status
        ], 200);
    }

    $book = $bookCopy->book;

    return response()->json([
        'title' => $book->title,
        'price' => $book->price
    ],200);
}

}
