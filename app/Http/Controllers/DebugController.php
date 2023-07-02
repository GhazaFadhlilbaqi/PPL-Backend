<?php

namespace App\Http\Controllers;

use App\Mail\DebugMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DebugController extends Controller
{
    public function sendDummyMail(Request $request)
    {
        try {
            $request->validate([
                'recepient' => 'required|email'
            ]);

            Mail::to($request->recepient)->send(new DebugMail($request->recepient));

            return response()->json([
                'status' => 'success',
                'message' => 'Dummy E-Mail has sended',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
