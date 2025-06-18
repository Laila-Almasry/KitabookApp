<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Http\Requests\StoreFavoriteRequest;
use App\Http\Resources\FavoriteResource;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = Favorite::with('book')
            ->where('user_id', $request->user()->id)
            ->get();

        return FavoriteResource::collection($favorites);
    }

    public function store(StoreFavoriteRequest $request)
    {
        $user = $request->user();

        $favorite = Favorite::firstOrCreate([
            'user_id' => $user->id,
            'book_id' => $request->book_id,
        ]);

        return new FavoriteResource($favorite->load('book'));
    }

    public function destroy($bookId, Request $request)
    {
        $favorite = Favorite::where('user_id', $request->user()->id)
                            ->where('book_id', $bookId)
                            ->firstOrFail();

        $favorite->delete();

        return response()->json(['message' => 'Book removed from favorites.']);
    }
}
