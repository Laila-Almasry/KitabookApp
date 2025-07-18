<?php

namespace App\Http\Controllers;

use App\Models\BookCopy;
use Illuminate\Http\Request;
use App\Models\Borrow;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\BorrowReservation;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class BorrowController extends Controller
{
    public function reservationToBorrowing(Request $request){
     $request->validate([
        'user_id' => 'required|exists:users,id',
        'book_copy_id' => 'required|exists:book_copies,id',
        'borrowed_at' => 'required|date',
        'due_date' => 'required|date|after:borrowed_at',
        'status' => 'required|in:active',
        'borrow_fee' => 'required|numeric|min:0',
        'freeze_amount' => 'required|numeric|min:0',
    ]);

    $reservation = BorrowReservation::where('user_id', $request->user_id)
        ->where('book_copy_id', $request->book_copy_id)
        ->where('status', 'pending')
        ->first();

    if (!$reservation) {
        return response()->json(['message' => 'Reservation not found or already used.'], 404);
    }

    if (Carbon::now()->gt($reservation->expires_at)) {
        return response()->json(['message' => 'Reservation has expired.'], 400);
    }

    $wallet = Wallet::where('user_id', $request->user_id)->first();

    if (!$wallet) {
        return response()->json(['message' => 'User wallet not found.'], 404);
    }

    if ($wallet->credits < $request->borrow_fee) {
        return response()->json(['message' => 'Insufficient credits for borrowing fee.'], 400);
    }

    if (($wallet->credits - $request->borrow_fee) < $request->freeze_amount) {
        return response()->json(['message' => 'Not enough credits to freeze deposit.'], 400);
    }

    $book_copy=BookCopy::find($request->book_copy_id);
    // Transactional safety
    DB::beginTransaction();
    try {
        // Create borrow record
        $borrow = Borrow::create([
            'user_id' => $request->user_id,
            'book_copy_id' => $request->book_copy_id,
            'borrowed_at' => $request->borrowed_at,
            'due_date' => $request->due_date,
            'status' => $request->status,
        ]);

        // Update reservation
        $reservation->status = 'confirmed';
        $reservation->save();

        //Update book copy status
         $book_copy->status = 'borrowed';
        $book_copy->save();

        // Update wallet
        $wallet->credits -= $request->borrow_fee;
        $wallet->credits -= $request->freeze_amount;

        $wallet->freezed_money += $request->freeze_amount;
        $wallet->save();

        //create a wallet transaction
        WalletTransaction::create([
            'wallet_id'=>$wallet->id,
            'type'=>'borrow',
            'amount'=>$request->borrow_fee,
            'book_copy_barcode'=>$book_copy->barcode,
        ]);
        WalletTransaction::create([
            'wallet_id'=>$wallet->id,
            'type'=>'freeze',
            'amount'=>$request->freeze_amount,
            'book_copy_barcode'=>$book_copy->barcode,
        ]);
        DB::commit();

        return response()->json([
            'message' => 'Borrow confirmed successfully',
            'borrow' => $borrow,
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Error confirming borrowing', 'error' => $e->getMessage()], 500);
    }
}

public function walkInBorrowing(Request $request){
    $request->validate([
        'barcode' => 'required|exists:book_copies,barcode',
        'borrowed_at' => 'required|date',
        'due_date' => 'required|date|after:borrowed_at',
        'borrower_name'=>'required|string',
        'borrower_phone_number'=>'required|string',
    ]
    );

     $bookCopy = BookCopy::where('barcode',$request->barcode)->first();

     $request['book_copy_id']=$bookCopy->id;
    if ($bookCopy->status !== 'available') {
        return response()->json([
            'message' => 'Book copy is not available for borrowing.',
            'status' => $bookCopy->status
        ], 400);
    }

    DB::beginTransaction();
    try {
        // Create borrow record (visitor has no user_id)
        $borrow = Borrow::create([
            'user_id' => null,
            'book_copy_id' => $request->book_copy_id,
            'borrowed_at' => $request->borrowed_at,
            'due_date' => $request->due_date,
            'status' => 'active',
            'borrower_name' => $request->borrower_name,
            'borrower_phone_number' => $request->borrower_phone_number,
        ]);

        // Update book copy status
        $bookCopy->status = 'borrowed';
        $bookCopy->save();

        DB::commit();

        return response()->json([
            'message' => 'Visitor borrowing recorded successfully',
            'borrow' => $borrow
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to record borrowing for visitor',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function index()
{
    $borrows = Borrow::with(['bookCopy.book', 'user'])
        ->orderBy('borrowed_at', 'desc')
        ->get()
        ->map(function ($borrow) {
            $book = $borrow->bookCopy->book;
            $price = $book->price;
            $borrowedAt = Carbon::parse($borrow->borrowed_at);
            $dueDate = Carbon::parse($borrow->due_date);
            $durationWeeks = ceil($borrowedAt->diffInDays($dueDate) / 7);

            // Fee percent logic
            $percentages = [1 => 0.04, 2 => 0.06, 3 => 0.08, 4 => 0.10];
            $feePercentage = $percentages[$durationWeeks] ?? 0.10;
            $fee = round($price * $feePercentage, 2);
            $deposit = round($price - $fee, 2);

            return [
                'id' => $borrow->id,
                'bookTitle' => $book->title,
                'status' => ucfirst($borrow->status),
                'type' => $borrow->user_id ? 'App user' : 'Visitor',
                'username' => $borrow->user_id ? $borrow->user->username : $borrow->borrower_name,
                'barcode' => $borrow->bookCopy->barcode,
                'fee' => $fee,
                'deposit' => $deposit,
                'dueDate' => $borrow->due_date,
                'borrower_name'=>$borrow->borrower_name,
                'borrower_phone_number'=>$borrow->borrower_phone_number
            ];
        });

    return response()->json($borrows);
}

public function returnBorrowing(Request $request)
{
    $request->validate([
        'borrow_id' => 'required|exists:borrows,id',
    ]);

    $borrow = Borrow::with('bookCopy')->find($request->borrow_id);

    if ($borrow->status !== 'active') {
        return response()->json(['message' => 'This borrowing has already been returned.'], 400);
    }

    DB::beginTransaction();
    try {
        // Step 1: Update borrow record
        $borrow->status = 'returned';
        $borrow->returned_at = Carbon::now();
        $borrow->save();

        // Step 2: Update book copy status
        $bookCopy = $borrow->bookCopy;
        $bookCopy->status = 'available';
        $bookCopy->save();

        // Step 3: Handle user wallet if it's an app user
        if ($borrow->user_id) {
            $book = $bookCopy->book;
            $price = $book->price;

            // Duration
            $borrowedAt = Carbon::parse($borrow->borrowed_at);
            $dueDate = Carbon::parse($borrow->due_date);
            $weeks = ceil($borrowedAt->diffInDays($dueDate) / 7);
            $feePercentages = [1 => 0.04, 2 => 0.06, 3 => 0.08, 4 => 0.10];
            $fee = round($price * ($feePercentages[$weeks] ?? 0.10), 2);
            $deposit = round($price - $fee, 2);

            $wallet = Wallet::where('user_id', $borrow->user_id)->first();
            if ($wallet) {
                $wallet->credits += $deposit;
                $wallet->freezed_money -= $deposit;
                $wallet->save();

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'unfreez',
                    'amount' => $deposit,
                    'book_copy_barcode' => $bookCopy->barcode,
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'message' => 'Book returned successfully',
            'borrow_id' => $borrow->id,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to return the book',
            'error' => $e->getMessage()
        ], 500);
    }
}

}

