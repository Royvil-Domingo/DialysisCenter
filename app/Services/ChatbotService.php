<?php

namespace App\Services;

use App\Models\ChatbotFaq;
use Illuminate\Support\Facades\Http;

class ChatbotService
{
    public function ask(string $message): string
    {
        return match (config('services.chatbot.provider')) {
            'gemini' => $this->askGemini($message),
            'openai' => $this->askOpenAI($message),
            default  => $this->fallbackFaq($message),
        };
    }

    protected function askGemini(string $message): string
    {
        $key = config('services.chatbot.gemini_key');

        $response = Http::post(
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$key}",
            ['contents' => [['parts' => [['text' => $this->buildPrompt($message)]]]]]
        );

        return $response->json('candidates.0.content.parts.0.text') ?? $this->fallbackFaq($message);
    }

    protected function askOpenAI(string $message): string
    {
        $key = config('services.chatbot.openai_key');

        $response = Http::withToken($key)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $this->systemPrompt()],
                ['role' => 'user', 'content' => $message],
            ],
        ]);

        return $response->json('choices.0.message.content') ?? $this->fallbackFaq($message);
    }

    protected function fallbackFaq(string $message): string
    {
        $faq = ChatbotFaq::where('is_active', true)->get()
            ->first(function ($f) use ($message) {
                foreach (explode(',', $f->keywords ?? '') as $kw) {
                    if ($kw !== '' && str_contains(strtolower($message), strtolower(trim($kw)))) {
                        return true;
                    }
                }
                return false;
            });

        return $faq->answer ?? "I'm not sure about that — please contact our staff directly.";
    }

    protected function systemPrompt(): string
    {
        return 'You are Diabot, an assistant for a dialysis center management system. '
            . 'Only answer general questions about appointments, dialysis basics, and how to use the system. '
            . 'Never give medical diagnosis or treatment advice. Keep answers short.';
    }

    protected function buildPrompt(string $message): string
    {
        return $this->systemPrompt() . "\n\nUser: " . $message;
    }
}