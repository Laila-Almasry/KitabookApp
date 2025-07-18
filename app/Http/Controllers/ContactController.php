<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email',
            'subject' => 'required|string|max:150',
            'message' => 'required|string',
        ]);

        Mail::raw($data['message'], function ($mail) use ($data) {
            $mail->to('petersalamoun2004@gmail.com')
                ->from($data['email'], $data['name'])
                ->subject($data['subject']);
        });

        return response()->json(['message' => 'Email sent successfully']);
    }
}
