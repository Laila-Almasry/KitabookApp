<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\BookCopy;
use App\Models\BorrowReservation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BorrowReservationController extends Controller
{
    public function index(){
        $reservations=BorrowReservation::all();
        $result=[];
     foreach ($reservations as $reservation) {
        $user = User::find($reservation->user_id);
        $username = $user->username;
        $bookCopy = BookCopy::find($reservation->book_copy_id);
        $bookTitle =Book::find($bookCopy->book_id)->title;
         $formattedDate = Carbon::parse($reservation->reserved_at)->format('F j, Y, g:i A');
        $result[] = [
        'id'=>$reservation->id,
            'username' => $username,
            'bookTitle' => $bookTitle,
            'reserved_at' => $formattedDate,
            'status' => $reservation->status,
        ];
    }
    return response()->json($result, 200);
    }

    public function show($id){
        $reservation=BorrowReservation::findOrFail($id);
        $user=User::find($reservation->user_id);
        $username=$user->username;
        $bookCopy=BookCopy::find($reservation->book_copy_id);
        $barcode=$bookCopy->barcode;
        $book=Book::find($bookCopy->book_id);
        $bookTitle=$book->title;
        $bookPrice=$book->price;
        return response()->json([
            'username'=>$username,
            'user_id'=>$reservation->user_id,
            'bookTitle'=>$bookTitle,
            'bookPrice'=>$bookPrice,
            'book_copy_id'=>$reservation->book_copy_id,
            'barcode'=>$barcode,
            'status'=>$reservation->status
        ],200);
    }

}
