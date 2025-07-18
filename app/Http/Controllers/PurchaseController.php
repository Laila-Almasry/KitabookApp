<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BookCopy;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{

public function walkInPurchase(Request $request)
{
    $request->validate([
        'barcode' => 'required|exists:book_copies,barcode',
    ]);

    $bookCopy = BookCopy::where('barcode', $request->barcode)->first();

    if ($bookCopy->status !== 'available') {
        return response()->json([
            'message' => 'Book copy is not available for purchase.',
            'status' => $bookCopy->status,
        ], 400);
    }

    $book = $bookCopy->book;

    DB::beginTransaction();
    try {
        // Update book copy status to sold
        $bookCopy->status = 'sold';
        $bookCopy->save();

        // Create purchase record
        $purchase = Purchase::create([
            'book_copy_id' => $bookCopy->id,
            'price' => $book->price,
            'purchased_at' => Carbon::now(),
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Walk-in purchase recorded successfully.',
            'purchase' => $purchase,
        ], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to record walk-in purchase.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function index()
{
    $purchases = Purchase::with('bookCopy.book')
        ->orderBy('purchased_at', 'desc')
        ->get()
        ->map(function ($purchase) {
            return [
                'id' => $purchase->id,
                'book_title' => $purchase->bookCopy->book->title,
                'barcode' => $purchase->bookCopy->barcode,
                'price' => $purchase->price,
                'purchased_at' => $purchase->purchased_at,
            ];
        });

    return response()->json(['purchases'=>$purchases],200);
}

}
