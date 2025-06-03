<?php

namespace App\Http\Controllers;

use App\Models\DigitalProduct;
use App\Http\Requests\DigitalProductRequest;
use App\Http\Resources\DigitalProductResource;

class DigitalProductController extends Controller
{
    public function index()
    {
        $digitalProducts = DigitalProduct::with(['user'])->get();
        return DigitalProductResource::collection($digitalProducts);
    }

    public function store(DigitalProductRequest $request)
    {
        $digitalProduct = DigitalProduct::create($request->validated());
        return new DigitalProductResource($digitalProduct->load(['user']));
    }

    public function show(DigitalProduct $digitalProduct)
    {
        return new DigitalProductResource($digitalProduct->load(['user']));
    }

    public function update(DigitalProductRequest $request, DigitalProduct $digitalProduct)
    {
        $digitalProduct->update($request->validated());
        return new DigitalProductResource($digitalProduct->load(['user']));
    }

    public function destroy(DigitalProduct $digitalProduct)
    {
        $digitalProduct->delete();
        return response()->json(['message' => 'Digital product deleted successfully.']);
    }
} 