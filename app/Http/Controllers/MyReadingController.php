<?php

namespace App\Http\Controllers;

use App\Models\MyReading;
use App\Http\Requests\MyReadingRequest;
use App\Http\Resources\MyReadingResource;
use Illuminate\Http\Request;

class MyReadingController extends Controller
{
public function index(Request $request)
{
    $query = MyReading::with('book')->where('user_id', $request->user()->id);

    if ($request->has('status')) {
        $query->where('status', $request->status);
    }

    $readings = $query->get();

    return MyReadingResource::collection($readings);
}

    public function store(MyReadingRequest $request)
    {
        $reading = MyReading::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'book_id' => $request->book_id,
            ],
            [
                'status' => $request->status,
            ]
        );

        return new MyReadingResource($reading->load('book'));
    }

    public function show($id, Request $request)
    {
        $reading = MyReading::where('user_id', $request->user()->id)
                            ->where('id', $id)
                            ->with('book')
                            ->firstOrFail();

        return new MyReadingResource($reading);
    }

    public function update(MyReadingRequest $request, $id)
    {
        $reading = MyReading::where('user_id', $request->user()->id)
                            ->where('id', $id)
                            ->firstOrFail();

        $reading->update($request->validated());

        return new MyReadingResource($reading->load('book'));
    }

    public function destroy($id, Request $request)
    {
        $reading = MyReading::where('user_id', $request->user()->id)
                            ->where('id', $id)
                            ->firstOrFail();

        $reading->delete();

        return response()->json(['message' => 'Reading entry deleted successfully.']);
    }
}
