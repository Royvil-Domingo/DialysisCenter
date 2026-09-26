<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function respond(Request $request, ChatbotService $chatbot)
    {
        $request->validate(['message' => 'required|string|max:500']);

        return response()->json([
            'reply' => $chatbot->ask($request->message),
        ]);
    }
}