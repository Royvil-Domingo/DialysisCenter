<?php

namespace App\Services;

use App\Models\ChatbotFaq;
use Illuminate\Support\Facades\Http;
use Throwable;

class ChatbotService
{
    public function ask(string $message): string
    {
        $provider = strtolower((string) config('services.chatbot.provider'));

        if ($provider === 'gemini' && filled(config('services.chatbot.gemini_key'))) {
            return $this->askGemini($message);
        }

        if ($provider === 'openai' && filled(config('services.chatbot.openai_key'))) {
            return $this->askOpenAI($message);
        }

        return $this->fallbackFaq($message);
    }

    protected function askGemini(string $message): string
    {
        $key = config('services.chatbot.gemini_key');

        try {
            $response = Http::timeout(15)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$key}",
                ['contents' => [['parts' => [['text' => $this->buildPrompt($message)]]]]]
            );

            if (!$response->successful()) {
                return $this->fallbackFaq($message);
            }

            $reply = $response->json('candidates.0.content.parts.0.text');
            return is_string($reply) && trim($reply) !== '' ? $reply : $this->fallbackFaq($message);
        } catch (Throwable) {
            return $this->fallbackFaq($message);
        }
    }

    protected function askOpenAI(string $message): string
    {
        $key = config('services.chatbot.openai_key');

        try {
            $response = Http::withToken($key)->timeout(15)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $message],
                ],
            ]);

            if (!$response->successful()) {
                return $this->fallbackFaq($message);
            }

            $reply = $response->json('choices.0.message.content');
            return is_string($reply) && trim($reply) !== '' ? $reply : $this->fallbackFaq($message);
        } catch (Throwable) {
            return $this->fallbackFaq($message);
        }
    }

    protected function fallbackFaq(string $message): string
    {
        $faqs = ChatbotFaq::where('is_active', true)->get();

        if ($faqs->isEmpty()) {
            $faqs = collect($this->defaultFaqs());
        }

        $normalizedMessage = $this->normalizeText($message);

        $faq = $faqs->first(function ($f) use ($normalizedMessage) {
            $candidateTexts = collect([
                $f->question ?? '',
                $f->answer ?? '',
                $f->keywords ?? '',
            ]);

            foreach ($candidateTexts as $text) {
                $normalizedText = $this->normalizeText($text);

                if ($normalizedText === '') {
                    continue;
                }

                if (str_contains($normalizedMessage, $normalizedText) || str_contains($normalizedText, $normalizedMessage)) {
                    return true;
                }

                foreach ($this->extractKeywords($f) as $keyword) {
                    if ($keyword !== '' && str_contains($normalizedMessage, $keyword)) {
                        return true;
                    }
                }
            }

            return false;
        });

        if ($faq && isset($faq->answer)) {
            return $faq->answer;
        }

        foreach ($this->defaultFaqs() as $defaultFaq) {
            if ($this->matchesDefaultFaq($normalizedMessage, $defaultFaq['keywords'])) {
                return $defaultFaq['answer'];
            }
        }

        return "I'm not sure about that — please contact our staff directly.";
    }

    protected function defaultFaqs(): array
    {
        return [
            [
                'question' => 'What are your operating hours?',
                'answer' => 'Our center is open Monday to Saturday, 6:00 AM to 6:00 PM. Please contact our staff to confirm holiday schedules.',
                'keywords' => ['hours', 'open', 'opening', 'operating hours', 'what time', 'when are you open', 'working hours', 'hours of operation'],
            ],
            [
                'question' => 'How do I check my appointment schedule?',
                'answer' => 'You can check your appointment using the "Check My Appointment" page on our homepage. You will need your patient reference code and birthdate.',
                'keywords' => ['schedule', 'appointment', 'check appointment', 'view appointment', 'when is my appointment', 'reference code', 'my dialysis schedule'],
            ],
            [
                'question' => 'What should I bring to my appointment?',
                'answer' => 'Please bring a valid ID, your patient reference code, and any current medications or prescriptions. Arrive at least 15 minutes early.',
                'keywords' => ['bring', 'prepare', 'appointment requirements', 'what to bring', 'documents', 'id', 'medications'],
            ],
            [
                'question' => 'How do I reschedule or cancel my appointment?',
                'answer' => 'Please contact our staff directly by phone or visit the center at least 24 hours before your scheduled appointment.',
                'keywords' => ['reschedule', 'cancel', 'change appointment', 'move appointment', 'postpone', 'rebook', 'cancel my session'],
            ],
            [
                'question' => 'What is hemodialysis?',
                'answer' => 'Hemodialysis is a treatment that filters waste, extra fluid, and salt from your blood when your kidneys can no longer do this well enough on their own.',
                'keywords' => ['hemodialysis', 'dialysis', 'kidney treatment', 'what is dialysis', 'what happens in dialysis'],
            ],
        ];
    }

    protected function matchesDefaultFaq(string $normalizedMessage, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            $normalizedKeyword = $this->normalizeText((string) $keyword);

            if ($normalizedKeyword !== '' && str_contains($normalizedMessage, $normalizedKeyword)) {
                return true;
            }
        }

        return false;
    }

    protected function extractKeywords($faq): array
    {
        if (! $faq || ! isset($faq->keywords)) {
            return [];
        }

        $list = preg_split('/[,;\n]+/', (string) $faq->keywords);

        return array_values(array_filter(array_map(fn ($keyword) => $this->normalizeText((string) $keyword), $list ?? []), fn ($keyword) => $keyword !== ''));
    }

    protected function normalizeText(string $text): string
    {
        $normalized = strtolower(preg_replace('/[^a-z0-9\s]/u', ' ', $text) ?? '');

        return preg_replace('/\s+/', ' ', trim($normalized)) ?? '';
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