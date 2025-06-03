<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Http\Requests\StoreFavoriteRequest;
use App\Http\Resources\FavoriteResource;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = Favorite::with(['user', 'book'])->get();
        return FavoriteResource::collection($favorites);
    }

    public function store(StoreFavoriteRequest $request)
    {
        $favorite = Favorite::create($request->validated());
        return new FavoriteResource($favorite->load(['user', 'book']));
    }

    public function show(Favorite $favorite)
    {
        return new FavoriteResource($favorite->load(['user', 'book']));
    }

    public function destroy(Favorite $favorite)
    {
        $favorite->delete();
        return response()->json(['message' => 'Favorite removed successfully.']);
    }
}
