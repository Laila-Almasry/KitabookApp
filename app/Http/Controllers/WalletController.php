<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wallet;
use App\Models\BookCopy;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
     public function search(Request $request)
    {
        $request->validate(['username' => 'required|string']);

        $users = User::where('username', 'like', '%' . $request->username . '%')->with('wallet')->get();
        $wallets=$users->map(function ($user){
            return [
                 'wallet' => [
                'username' => $user->username, // Add username to the wallet object
                'details' => $user->wallet,    // Assuming wallet has other details
            ],
            ];
        });

        return response()->json(['wallets'=>$wallets],200);
    }

    public function show($walletId)
    {
        $wallet = Wallet::with('user')->findOrFail($walletId);

        return response()->json([
            'wallet' => $wallet,
            'history' => $wallet->transactions()->latest()->take(5)->get()
        ]);
    }
    public function showUserWallet()
    {
        $userId=Auth::user()->id;
        $wallet = Wallet::where('user_id','=',$userId)->first();
        if (!$wallet) {
    return response()->json(['message' => 'Wallet not found'], 404);
}

        return response()->json([
            'wallet' => $wallet,
        ],200);
    }

    public function charge(Request $request, $walletId)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);

        $wallet = Wallet::findOrFail($walletId);
        $wallet->credits += $request->amount;
        $wallet->save();

        $wallet->transactions()->create([
            'type' => 'charge',
            'amount' => $request->amount,
        ]);

        return response()->json(['message' => 'Credits charged successfully']);
    }

    public function freeze(Request $request, $walletId)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);

        $wallet = Wallet::findOrFail($walletId);

        if ($wallet->credits < $request->amount) {
            return response()->json(['message' => 'Insufficient credits'], 422);
        }

        $wallet->credits -= $request->amount;
        $wallet->freezed_money += $request->amount;
        $wallet->save();

        $wallet->transactions()->create([
            'type' => 'freeze',
            'amount' => $request->amount,
        ]);

        return response()->json(['message' => 'Amount frozen successfully']);
    }

    public function unfreeze(Request $request, $walletId)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);

        $wallet = Wallet::findOrFail($walletId);

        if ($wallet->freezed_money < $request->amount) {
            return response()->json(['message' => 'Insufficient frozen money'], 422);
        }

        $wallet->freezed_money -= $request->amount;
        $wallet->credits += $request->amount;
        $wallet->save();

        $wallet->transactions()->create([
            'type' => 'unfreeze',
            'amount' => $request->amount,
        ]);

        return response()->json(['message' => 'Amount unfrozen successfully']);
    }

    public function buyWithCredits(Request $request, $walletId)
    {
        $request->validate([
            'book_copy_barcode' => 'required|string'
        ]);

        $wallet = Wallet::findOrFail($walletId);
        $bookCopy = BookCopy::where('barcode', $request->book_copy_barcode)->first();

        if (!$bookCopy || $bookCopy->status !== 'available') {
            return response()->json(['message' => 'Invalid or unavailable book copy'], 404);
        }

        $book = $bookCopy->book;
        $price = $book->price;

        if ($wallet->credits < $price) {
            return response()->json(['message' => 'Insufficient credits'], 422);
        }

        // Deduct and mark as sold
        $wallet->credits -= $price;
        $wallet->save();

        $bookCopy->status = 'sold';
        $bookCopy->save();

        $wallet->transactions()->create([
            'type' => 'buy',
            'amount' => $price,
            'book_copy_barcode' => $bookCopy->barcode
        ]);

        return response()->json(['message' => 'Book purchased successfully']);
    }
}
