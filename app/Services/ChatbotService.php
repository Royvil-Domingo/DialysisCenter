<?php

namespace App\Services;

use App\Models\ChatbotFaq;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChatbotService
{
    protected int $maxHistoryTurns = 4;

    public function ask(string $message): string
    {
        $message = trim((string) $message);

        if ($message === '') {
            return 'Please enter a question so I can help you.';
        }

        $matchedFaq = $this->findBestFaqMatch($message);

        if ($matchedFaq !== null) {
            return $matchedFaq['answer'];
        }

        $provider = $this->configuredProvider();

        if ($provider === 'none') {
            return 'The AI assistant is not configured for this clinic. Please contact our staff directly for help.';
        }

        $this->storeUserHistory($message);

        if ($provider === 'gemini') {
            if (filled(config('services.chatbot.gemini_key'))) {
                return $this->askGemini($message);
            }

            return 'The AI assistant is not configured for this clinic. Please contact our staff directly for help.';
        }

        if ($provider === 'openai') {
            if (filled(config('services.chatbot.openai_key'))) {
                return $this->askOpenAI($message);
            }

            return 'The AI assistant is not configured for this clinic. Please contact our staff directly for help.';
        }

        return 'The AI assistant is not configured for this clinic. Please contact our staff directly for help.';
    }

    protected function askGemini(string $message): string
    {
        $key = (string) config('services.chatbot.gemini_key');
        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $this->systemPrompt()],
                ],
            ],
            'contents' => $this->buildGeminiContents($message),
            'generationConfig' => [
                'maxOutputTokens' => 400,
            ],
        ];

        try {
            $response = Http::acceptJson()
                ->timeout(20)
                ->connectTimeout(10)
                ->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent?key=' . urlencode($key),
                    $payload
                );

            if (! $response->successful()) {
                Log::error('Gemini chatbot API request failed.', [
                    'status' => $response->status(),
                    'provider' => 'gemini',
                    'error' => is_string($response->json('error.message')) ? $response->json('error.message') : 'No error message returned',
                ]);

                return 'The AI assistant is temporarily unavailable. Please contact our staff directly.';
            }

            $reply = $response->json('candidates.0.content.parts.0.text');

            if (! is_string($reply) || trim($reply) === '') {
                Log::warning('Gemini chatbot returned an empty response.', ['provider' => 'gemini']);

                return 'The AI assistant is temporarily unavailable. Please contact our staff directly.';
            }

            $this->storeAiHistory('assistant', trim($reply));

            return trim($reply);
        } catch (ConnectionException $exception) {
            Log::error('Gemini chatbot connection failed.', [
                'provider' => 'gemini',
                'exception' => class_basename($exception),
            ]);

            return 'The AI assistant is temporarily unavailable. Please contact our staff directly.';
        } catch (Throwable $exception) {
            Log::error('Unexpected Gemini chatbot error.', [
                'provider' => 'gemini',
                'exception' => class_basename($exception),
            ]);

            return 'The AI assistant is temporarily unavailable. Please contact our staff directly.';
        }
    }

    protected function askOpenAI(string $message): string
    {
        $key = (string) config('services.chatbot.openai_key');
        $messages = $this->buildOpenAiMessages($message);

        try {
            $response = Http::withToken($key)
                ->acceptJson()
                ->timeout(20)
                ->connectTimeout(10)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => $messages,
                    'temperature' => 0.5,
                ]);

            if (! $response->successful()) {
                Log::error('OpenAI chatbot API request failed.', [
                    'status' => $response->status(),
                    'provider' => 'openai',
                    'error' => is_string($response->json('error.message')) ? $response->json('error.message') : 'No error message returned',
                ]);

                return 'The AI assistant is temporarily unavailable. Please contact our staff directly.';
            }

            $reply = $response->json('choices.0.message.content');

            if (! is_string($reply) || trim($reply) === '') {
                Log::warning('OpenAI chatbot returned an empty response.', ['provider' => 'openai']);

                return 'The AI assistant is temporarily unavailable. Please contact our staff directly.';
            }

            $this->storeAiHistory('assistant', trim($reply));

            return trim($reply);
        } catch (ConnectionException $exception) {
            Log::error('OpenAI chatbot connection failed.', [
                'provider' => 'openai',
                'exception' => class_basename($exception),
            ]);

            return 'The AI assistant is temporarily unavailable. Please contact our staff directly.';
        } catch (Throwable $exception) {
            Log::error('Unexpected OpenAI chatbot error.', [
                'provider' => 'openai',
                'exception' => class_basename($exception),
            ]);

            return 'The AI assistant is temporarily unavailable. Please contact our staff directly.';
        }
    }

    protected function configuredProvider(): string
    {
        $provider = strtolower((string) config('services.chatbot.provider'));

        if ($provider === '' || $provider === 'none' || $provider === 'null') {
            return 'none';
        }

        if (in_array($provider, ['gemini', 'openai'], true)) {
            return $provider;
        }

        return 'none';
    }

    protected function findBestFaqMatch(string $message): ?array
    {
        $normalizedMessage = $this->normalizeText($message);

        if ($normalizedMessage === '') {
            return null;
        }

        $candidates = $this->collectFaqCandidates();
        $bestMatch = null;

        foreach ($candidates as $candidate) {
            $score = $this->scoreFaqMatch($normalizedMessage, $candidate);

            if ($score <= 0) {
                continue;
            }

            if ($bestMatch === null || $score > $bestMatch['score']) {
                $bestMatch = [
                    'answer' => $candidate['answer'],
                    'score' => $score,
                ];
            }
        }

        return $bestMatch !== null && $bestMatch['score'] >= 25 ? $bestMatch : null;
    }

    protected function collectFaqCandidates(): array
    {
        $databaseFaqs = ChatbotFaq::where('is_active', true)
            ->get()
            ->map(function ($faq) {
                return [
                    'question' => (string) ($faq->question ?? ''),
                    'answer' => (string) ($faq->answer ?? ''),
                    'keywords' => (string) ($faq->keywords ?? ''),
                ];
            })
            ->all();

        return [...$databaseFaqs, ...$this->defaultFaqs()];
    }

    protected function scoreFaqMatch(string $normalizedMessage, array $faq): int
    {
        $question = $this->normalizeText((string) ($faq['question'] ?? ''));
        $keywords = $this->extractKeywordsFromCsv((string) ($faq['keywords'] ?? ''));

        if ($question !== '' && $normalizedMessage === $question) {
            return 100;
        }

        $messageTokens = $this->tokenize($normalizedMessage);
        $score = 0;

        foreach ($keywords as $keyword) {
            $normalizedKeyword = $this->normalizeText((string) $keyword);

            if ($normalizedKeyword === '') {
                continue;
            }

            if ($normalizedMessage === $normalizedKeyword) {
                $score += $this->isWeakKeyword($normalizedKeyword) ? 18 : 80;
                continue;
            }

            $keywordTokens = $this->tokenize($normalizedKeyword);

            if ($keywordTokens === []) {
                continue;
            }

            $strongOverlap = array_values(array_intersect($messageTokens, $keywordTokens));
            $wholePhrase = $this->containsWholePhrase($normalizedMessage, $normalizedKeyword);

            if ($wholePhrase && ! $this->isWeakKeyword($normalizedKeyword)) {
                $score += 25;
                continue;
            }

            if (count($strongOverlap) >= 2) {
                $score += $this->isWeakKeyword($normalizedKeyword) ? 12 : 30;
                continue;
            }

            if ($this->isWeakKeyword($normalizedKeyword)) {
                $contextMatch = $this->hasWeakKeywordContext($messageTokens, $normalizedKeyword);

                if ($contextMatch && $wholePhrase) {
                    $score += 18;
                }
            }
        }

        if ($question !== '' && $this->sameIntent($normalizedMessage, $question)) {
            $score += 20;
        }

        return $score;
    }

    protected function sameIntent(string $normalizedMessage, string $question): bool
    {
        $messageTokens = $this->tokenize($normalizedMessage);
        $questionTokens = $this->tokenize($question);

        if ($messageTokens === [] || $questionTokens === []) {
            return false;
        }

        $overlap = array_intersect($messageTokens, $questionTokens);

        return count($overlap) >= 2 || (count($overlap) >= 1 && count($questionTokens) <= 3);
    }

    protected function isWeakKeyword(string $keyword): bool
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            return true;
        }

        if (strlen($keyword) <= 3) {
            return true;
        }

        return in_array($keyword, ['open', 'hours', 'schedule', 'time', 'when', 'bring', 'appointment'], true);
    }

    protected function hasWeakKeywordContext(array $messageTokens, string $keyword): bool
    {
        $contextWords = ['when', 'what', 'time', 'hours', 'open', 'schedule', 'appointment', 'bring'];

        if ($keyword === '' || $messageTokens === []) {
            return false;
        }

        $contextHits = array_intersect($messageTokens, $contextWords);

        return count($contextHits) >= 2;
    }

    protected function buildGeminiContents(string $message): array
    {
        $contents = [];

        foreach ($this->recentConversationHistory() as $turn) {
            $role = ($turn['role'] ?? 'user') === 'assistant' ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => (string) ($turn['content'] ?? '')]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]],
        ];

        return $contents;
    }

    protected function buildOpenAiMessages(string $message): array
    {
        $messages = [[
            'role' => 'system',
            'content' => $this->systemPrompt(),
        ]];

        foreach ($this->recentConversationHistory() as $turn) {
            $messages[] = [
                'role' => $turn['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => (string) ($turn['content'] ?? ''),
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        return $messages;
    }

    protected function recentConversationHistory(): array
    {
        $history = session('chatbot_history', []);

        if (! is_array($history)) {
            return [];
        }

        return array_slice($history, -($this->maxHistoryTurns * 2));
    }

    protected function storeAiHistory(string $role, string $content): void
    {
        $history = session('chatbot_history', []);

        if (! is_array($history)) {
            $history = [];
        }

        $history[] = ['role' => $role, 'content' => trim($content)];

        if (count($history) > ($this->maxHistoryTurns * 2)) {
            $history = array_slice($history, -($this->maxHistoryTurns * 2));
        }

        session(['chatbot_history' => $history]);
    }

    protected function storeUserHistory(string $message): void
    {
        $history = session('chatbot_history', []);

        if (! is_array($history)) {
            $history = [];
        }

        $history[] = ['role' => 'user', 'content' => trim($message)];

        if (count($history) > ($this->maxHistoryTurns * 2)) {
            $history = array_slice($history, -($this->maxHistoryTurns * 2));
        }

        session(['chatbot_history' => $history]);
    }

    protected function fallbackFaq(string $message): string
    {
        $matchedFaq = $this->findBestFaqMatch($message);

        if ($matchedFaq !== null) {
            return $matchedFaq['answer'];
        }

        return 'I am not sure about that — please contact our staff directly.';
    }

    protected function defaultFaqs(): array
    {
        return [
            [
                'question' => 'What are your operating hours?',
                'answer' => 'Our center is open Monday to Saturday, 6:00 AM to 6:00 PM. Please contact our staff to confirm holiday schedules.',
                'keywords' => 'hours, open, operating hours, what time, when are you open, working hours, hours of operation',
            ],
            [
                'question' => 'How do I check my appointment schedule?',
                'answer' => 'You can check your appointment using the "Check My Appointment" page on our homepage. You will need your patient reference code and birthdate.',
                'keywords' => 'schedule, appointment, check appointment, view appointment, when is my appointment, reference code, my dialysis schedule',
            ],
            [
                'question' => 'What should I bring to my appointment?',
                'answer' => 'Please bring a valid ID, your patient reference code, and any current medications or prescriptions. Arrive at least 15 minutes early.',
                'keywords' => 'bring, prepare, appointment requirements, what to bring, documents, medications, items to bring',
            ],
            [
                'question' => 'How do I reschedule or cancel my appointment?',
                'answer' => 'Please contact our staff directly by phone or visit the center at least 24 hours before your scheduled appointment.',
                'keywords' => 'reschedule, cancel, change appointment, move appointment, postpone, rebook, cancel my session',
            ],
            [
                'question' => 'What is hemodialysis?',
                'answer' => 'Hemodialysis is a treatment that filters waste, extra fluid, and salt from your blood when your kidneys can no longer do this well enough on their own.',
                'keywords' => 'hemodialysis, what is hemodialysis, what happens in hemodialysis, kidney treatment filter blood',
            ],
        ];
    }

    protected function matchesDefaultFaq(string $normalizedMessage, array $keywords): bool
    {
        $match = $this->findBestFaqMatch($normalizedMessage);

        if ($match !== null) {
            return true;
        }

        foreach ($keywords as $keyword) {
            $normalizedKeyword = $this->normalizeText((string) $keyword);

            if ($normalizedKeyword === '') {
                continue;
            }

            if ($normalizedKeyword === $normalizedMessage) {
                return true;
            }

            if (str_contains($normalizedMessage, $normalizedKeyword) && ! $this->isWeakKeyword($normalizedKeyword)) {
                return true;
            }
        }

        return false;
    }

    protected function extractKeywords($faq): array
    {
        if (! $faq) {
            return [];
        }

        $keywords = $faq->keywords ?? '';

        return $this->extractKeywordsFromCsv((string) $keywords);
    }

    protected function extractKeywordsFromCsv(string $keywords): array
    {
        if ($keywords === '') {
            return [];
        }

        $parts = preg_split('/[,;\n]+/', $keywords) ?: [];

        $normalized = array_map(function ($value) {
            $keyword = $this->normalizeText((string) $value);

            return $keyword;
        }, $parts);

        return array_values(array_filter($normalized, fn (string $keyword) => $keyword !== ''));
    }

    protected function normalizeText(string $text): string
    {
        $normalized = strtolower((string) $text);
        $normalized = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/[^a-z0-9\s]/u', ' ', $normalized) ?? $normalized;

        return preg_replace('/\s+/', ' ', trim($normalized)) ?? '';
    }

    protected function tokenize(string $text): array
    {
        $text = $this->normalizeText($text);

        if ($text === '') {
            return [];
        }

        $stopWords = [
            'a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'can', 'could', 'do', 'does', 'for', 'from',
            'have', 'he', 'if', 'in', 'is', 'it', 'me', 'my', 'of', 'on', 'or', 'our', 'please',
            'she', 'that', 'the', 'their', 'them', 'they', 'this', 'to', 'us', 'we', 'where',
            'who', 'why', 'with', 'you', 'your', 'how',
        ];

        $terms = array_filter(explode(' ', $text), fn (string $term) => $term !== '');

        return array_values(array_filter($terms, fn (string $term) => ! in_array($term, $stopWords, true) && strlen($term) > 1));
    }

    protected function containsWholePhrase(string $haystack, string $needle): bool
    {
        $haystack = trim($haystack);
        $needle = trim($needle);

        if ($haystack === '' || $needle === '') {
            return false;
        }

        if ($haystack === $needle) {
            return true;
        }

        $regex = '/(^|\s)' . preg_quote($needle, '/') . '($|\s)/u';

        return preg_match($regex, $haystack) === 1;
    }

    protected function systemPrompt(): string
    {
        return 'You are Diabot, an assistant for a dialysis center management system. '
            . 'Answer general questions about appointments, dialysis basics, and how to use the system. '
            . 'Do not diagnose medical conditions, prescribe treatment, or invent clinic hours, prices, or availability. '
            . 'If the user needs medical or clinic-specific decisions, tell them to contact clinic staff or their healthcare provider.';
    }

    protected function buildPrompt(string $message): string
    {
        return $this->systemPrompt() . "\n\nUser: " . $message;
    }
}