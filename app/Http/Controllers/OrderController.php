<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Wallet;

class OrderController extends Controller
{
     public function placeOrder(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.book_id' => 'required|integer|exists:books,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        $items = $request->input('items');
        $rejected = [];

        DB::beginTransaction();

        try {
            $total = 0;
            $copiesToSell = [];
            $orderItemsData = [];

            foreach ($items as $item) {
                $bookId = $item['book_id'];
                $quantity = $item['quantity'];

                $book = Book::find($bookId);

                $availableCopies = BookCopy::where('book_id', $bookId)
                    ->where('status', 'available')
                    ->take($quantity)
                    ->get();

                if ($availableCopies->count() < $quantity) {
                    $rejected[] = [
                        'book_id' => $bookId,
                        'title' => $book->title,
                        'reason' => "Only {$availableCopies->count()} available out of {$quantity} requested."
                    ];
                    continue;
                }

                $copiesToSell[] = $availableCopies;
                $total += $book->price * $quantity;

                $orderItemsData[] = [
                    'book_id' => $bookId,
                    'quantity' => $quantity,
                    'unit_price' => $book->price,
                ];
            }

            if (count($rejected) > 0) {
                DB::rollBack();
                return response()->json([
                    'status' => 'rejected',
                    'message' => 'Some items are unavailable',
                    'reasons' => $rejected
                ], 422);
            }

            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            if (!$wallet || $wallet->credits < $total) {
                DB::rollBack();
                return response()->json([
                    'status' => 'rejected',
                    'message' => 'Insufficient balance in wallet.'
                ], 402);
            }

            $wallet->credits -= $total;
            $wallet->save();

            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'confirmed',
                'total_price' => $total,
            ]);

            foreach ($orderItemsData as $index => $itemData) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    ...$itemData
                ]);

                foreach ($copiesToSell[$index] as $copy) {
                    $copy->update([
                        'status' => 'sold',
                        'order_item_id' => $orderItem->id
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'confirmed',
                'message' => 'Order placed successfully.',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while placing the order.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
