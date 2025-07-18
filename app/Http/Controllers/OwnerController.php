<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Owner;
use Illuminate\Support\Facades\Hash;

class OwnerController extends Controller
{
    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to log the user in
        if (Auth::guard('owner')->attempt($request->only('email', 'password'))) {
            $owner = Auth::guard('owner')->user();
            $token = $owner->createToken('API Token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'owner' => $owner,
                'token' => $token,
            ], 200);
        }

        // If authentication fails
        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }

public function checkPassword(Request $request) {
    $request->validate([
        'password' => 'required',
         'ownerId' => 'required|exists:owners,id'
    ]);



 $owner = Owner::find($request->ownerId);
    if ( Hash::check($request->password, $owner->password)) {
        return response()->json(['result' => true], 200);
    } else {
        return response()->json(['result' => false], 200);
    }
}


}


