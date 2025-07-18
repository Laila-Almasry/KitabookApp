<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DigitalProduct;
use App\Http\Requests\DigitalProductRequest;
use App\Http\Resources\DigitalProductResource;
class DigitalProductsController extends Controller
{
     public function index()
    {
        return DigitalProductResource::collection(DigitalProduct::all());
    }

    public function store(DigitalProductRequest $request)
    {
        $digitalProduct = DigitalProduct::create($request->validated());
        return new DigitalProductResource($digitalProduct);
    }

    public function show(DigitalProduct $digitalProduct)
    {
        return new DigitalProductResource($digitalProduct);
    }

    public function update(DigitalProductRequest $request, DigitalProduct $digitalProduct)
    {
        $digitalProduct->update($request->validated());
        return new DigitalProductResource($digitalProduct);
    }

    public function destroy(DigitalProduct $digitalProduct)
    {
        $digitalProduct->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
