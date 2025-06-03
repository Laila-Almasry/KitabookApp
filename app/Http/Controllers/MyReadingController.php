<?php

namespace App\Http\Controllers;

use App\Models\MyReading;
use App\Http\Requests\MyReadingRequest;
use App\Http\Resources\MyReadingResource;

class MyReadingController extends Controller
{
    public function index()
    {
        $readings = MyReading::with(['user', 'book'])->get();
        return MyReadingResource::collection($readings);
    }

    public function store(MyReadingRequest $request)
    {
        $myReading = MyReading::create($request->validated());
        return new MyReadingResource($myReading->load(['user', 'book']));
    }

    public function show(MyReading $myReading)
    {
        return new MyReadingResource($myReading->load(['user', 'book']));
    }

    public function update(MyReadingRequest $request, MyReading $myReading)
    {
        $myReading->update($request->validated());
        return new MyReadingResource($myReading->load(['user', 'book']));
    }

    public function destroy(MyReading $myReading)
    {
        $myReading->delete();
        return response()->json(['message' => 'Reading entry deleted successfully.']);
    }
}
