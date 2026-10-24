<?php

namespace App\Services;

use App\Models\ChatbotFaq;
use App\Models\Setting;
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
            $answer = $this->applySettingPlaceholders($matchedFaq['answer']);

            // Preset exchanges are stored too, so a follow-up question
            // ("can you explain that more simply?") has real context.
            $this->storeUserHistory($message);
            $this->storeAiHistory('assistant', $answer);

            return $answer;
        }

        $provider = $this->configuredProvider();

        if ($provider === 'none') {
            return 'The AI assistant is not configured for this clinic. Please contact our staff directly for help.';
        }

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
    $model = (string) config('services.chatbot.gemini_model', 'gemini-3.8-flash');
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
            ->withHeaders(['x-goog-api-key' => $key])
            ->timeout(20)
            ->connectTimeout(10)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                $payload
            );

        // ...rest of the method stays exactly the same

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

            $reply = trim($reply);

            // Only persist the exchange once we actually have a reply —
            // a failed call leaves no dangling turn in session history.
            $this->storeUserHistory($message);
            $this->storeAiHistory('assistant', $reply);

            return $reply;
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

            $reply = trim($reply);

            $this->storeUserHistory($message);
            $this->storeAiHistory('assistant', $reply);

            return $reply;
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

        // Generic words that recur across many FAQs — alone they shouldn't
        // win a match; paired with on-topic context words (below) they can.
        return in_array($keyword, [
            'open', 'hours', 'schedule', 'time', 'when', 'bring', 'appointment',
            'dialysis', 'treatment', 'session', 'patient', 'patients', 'staff',
            'clinic', 'center', 'help', 'information', 'document', 'documents',
            'need', 'needed', 'want', 'contact',
        ], true);
    }

    protected function hasWeakKeywordContext(array $messageTokens, string $keyword): bool
    {
        $contextWords = [
            'when', 'time', 'hours', 'open', 'schedule', 'appointment', 'bring',
            'prepare', 'first', 'before', 'eat', 'medication', 'fluid',
            'feel', 'unwell', 'bleeding', 'pain', 'dizzy', 'fever', 'infection',
            'pay', 'payment', 'fee', 'bill', 'charge', 'insurance', 'philhealth', 'receipt',
            'contact', 'address', 'located', 'directions', 'staff',
            'dashboard', 'record', 'records', 'report', 'notification', 'error',
            'access', 'permission', 'walk', 'late', 'miss', 'reschedule', 'cancel',
        ];

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

    protected function defaultFaqs(): array
    {
        // Intentionally empty — database/seeders/ChatbotFaqSeeder.php is the
        // single source of truth for preset FAQs. Kept as a method (not
        // deleted) so collectFaqCandidates()'s merge point still exists if
        // an offline/no-DB fallback list is ever needed again.
        return [];
    }

    protected function extractKeywordsFromCsv(string $keywords): array
    {
        if ($keywords === '') {
            return [];
        }

        $parts = preg_split('/[,;\n]+/', $keywords) ?: [];

        $normalized = array_map(fn ($value) => $this->normalizeText((string) $value), $parts);

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
            'she', 'should', 'that', 'the', 'their', 'them', 'they', 'this', 'to', 'us', 'we', 'what', 'where',
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

    /**
     * Replaces {{clinic_name}}, {{clinic_address}}, {{clinic_contact_number}},
     * {{clinic_email}}, {{operating_hours}} in an FAQ answer with live values
     * from the Settings page — the same source resources/views/welcome.blade.php
     * already reads, so the chatbot never drifts out of sync with the homepage.
     */
    protected function applySettingPlaceholders(string $answer): string
    {
        return strtr($answer, [
            '{{clinic_name}}' => Setting::get('clinic_name', 'Dialysis Center'),
            '{{clinic_address}}' => Setting::get('clinic_address', 'Poblacion, San Ildefonso, Bulacan'),
            '{{clinic_contact_number}}' => Setting::get('clinic_contact_number', '—'),
            '{{clinic_email}}' => Setting::get('clinic_email', '—'),
            '{{operating_hours}}' => Setting::get('operating_hours', 'Mon–Sat, 6:00 AM – 6:00 PM'),
        ]);
    }

    protected function systemPrompt(): string
    {
        return 'You are Diabot, an assistant for a dialysis center management system. '
            . 'Answer general questions about appointments, dialysis basics, and how to use the system. '
            . 'Do not diagnose medical conditions, prescribe treatment, or invent clinic hours, prices, or availability. '
            . 'If the user needs medical or clinic-specific decisions, tell them to contact clinic staff or their healthcare provider.';
    }
}