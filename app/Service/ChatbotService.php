// app/Services/ChatbotService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChatbotService
{
    public function ask(string $message): string
    {
        $provider = config('services.chatbot.provider');

        return match ($provider) {
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
            [
                'contents' => [[
                    'parts' => [['text' => $this->buildPrompt($message)]],
                ]],
            ]
        );

        return $response->json('candidates.0.content.parts.0.text')
            ?? 'Sorry, I could not process that right now.';
    }

    protected function askOpenAI(string $message): string
    {
        $key = config('services.chatbot.openai_key');

        $response = Http::withToken($key)->post(
            'https://api.openai.com/v1/chat/completions',
            [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $message],
                ],
            ]
        );

        return $response->json('choices.0.message.content')
            ?? 'Sorry, I could not process that right now.';
    }

    protected function fallbackFaq(string $message): string
    {
        // keyword match against chatbot_faqs table, used if no API key is set
        $faq = \App\Models\ChatbotFaq::where('is_active', true)
            ->get()
            ->first(fn ($f) => str_contains(strtolower($message), strtolower(explode(',', $f->keywords)[0] ?? '###')));

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