<?php

namespace Tests\Feature;

use App\Models\ChatbotFaq;
use App\Models\Setting;
use App\Services\ChatbotService;
use Database\Seeders\ChatbotFaqSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class ChatbotServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.chatbot.provider' => 'gemini',
            'services.chatbot.gemini_key' => 'test-gemini-key',
            'services.chatbot.openai_key' => 'test-openai-key',
        ]);

        // The FAQ catalog now lives only in the DB — seed it so tests that
        // rely on a preset answer (but don't create their own FAQ row) see
        // the same catalog production will have.
        $this->seed(ChatbotFaqSeeder::class);
    }

    public function test_exact_preset_match_returns_expected_answer_without_ai_call(): void
    {
        Setting::set('operating_hours', 'Monday to Saturday, 6:00 AM to 6:00 PM');
        Setting::set('clinic_contact_number', '(044) 123-4567');

        Http::fake();

        $reply = app(ChatbotService::class)->ask('What are your operating hours?');

        $this->assertSame(
            'Our center is open Monday to Saturday, 6:00 AM to 6:00 PM. Please contact (044) 123-4567 to confirm holiday schedules.',
            $reply
        );
        Http::assertSentCount(0);
    }

    public function test_natural_phrasing_of_known_faq_returns_preset_without_api_call(): void
    {
        Setting::set('operating_hours', 'Monday to Saturday, 6:00 AM to 6:00 PM');

        Http::fake();

        $reply = app(ChatbotService::class)->ask('When do you open?');

        $this->assertStringContainsString('Monday to Saturday', $reply);
        Http::assertSentCount(0);
    }

    public function test_unknown_question_uses_the_configured_ai_provider(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'The clinic can help with that.']]]],
                ],
            ], 200),
        ]);

        $reply = app(ChatbotService::class)->ask('Can you explain how dialysis works?');

        $this->assertSame('The clinic can help with that.', $reply);
        Http::assertSent(function ($request) {
            $this->assertStringContainsString('gemini-2.5-flash', (string) $request->url());
            $this->assertArrayHasKey('systemInstruction', $request->data());
            $this->assertSame('user', $request->data()['contents'][count($request->data()['contents']) - 1]['role']);
            $this->assertSame('user', $request->data()['contents'][count($request->data()['contents']) - 1]['role']);

            return true;
        });
    }

    public function test_ai_unavailable_returns_service_message_not_faq_answer(): void
    {
        foreach ([401, 429, 500] as $status) {
            Http::fake([
                '*' => Http::response(['error' => ['message' => 'bad request']], $status),
            ]);

            $reply = app(ChatbotService::class)->ask('Explain what kidney dialysis is.');

            $this->assertStringContainsString('temporarily unavailable', strtolower($reply));
            $this->assertNotSame(
                'Our center is open Monday to Saturday, 6:00 AM to 6:00 PM. Please contact our staff to confirm holiday schedules.',
                $reply
            );
        }
    }

    public function test_provider_not_configured_returns_clear_message_when_no_faq_matches(): void
    {
        config(['services.chatbot.provider' => 'none']);
        Http::fake();

        $reply = app(ChatbotService::class)->ask('Can you explain quantum computing?');

        $this->assertStringContainsString('not configured', strtolower($reply));
        Http::assertSentCount(0);
    }

    public function test_short_or_ambiguous_keywords_do_not_trigger_false_positive_faqs(): void
    {
        config(['services.chatbot.provider' => 'none']);

        $reply = app(ChatbotService::class)->ask('How do I open the app?');

        $this->assertStringContainsString('not configured', strtolower($reply));
        $this->assertStringNotContainsString('Monday to Saturday', $reply);
    }

    public function test_follow_up_uses_recent_context_without_client_trust(): void
    {
        config([
            'services.chatbot.provider' => 'openai',
            'services.chatbot.openai_key' => 'test-openai-key',
        ]);

        Session::put('chatbot_history', [
            ['role' => 'user', 'content' => 'What is hemodialysis?'],
            ['role' => 'assistant', 'content' => 'Hemodialysis filters waste from blood.'],
        ]);

        Http::fake([
            '*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'In simpler terms, it filters waste from blood when kidneys are not working well.']],
                ],
            ], 200),
        ]);

        $reply = app(ChatbotService::class)->ask('Can you explain that in simpler words?');

        $this->assertSame('In simpler terms, it filters waste from blood when kidneys are not working well.', $reply);
        Http::assertSent(function ($request) {
            $payload = $request->data();
            $this->assertSame('system', $payload['messages'][0]['role']);
            $this->assertSame('user', $payload['messages'][count($payload['messages']) - 1]['role']);
            $this->assertGreaterThanOrEqual(3, count($payload['messages']));

            return true;
        });
    }

    public function test_database_faqs_are_considered_before_ai(): void
    {
        ChatbotFaq::create([
            'question' => 'When do you open?',
            'answer' => 'Our clinic opens at 6:00 AM.',
            'keywords' => 'open, hours, operating hours',
            'is_active' => true,
        ]);

        Http::fake();

        $reply = app(ChatbotService::class)->ask('When do you open?');

        $this->assertSame('Our clinic opens at 6:00 AM.', $reply);
        Http::assertSentCount(0);
    }

    public function test_chatbot_accepts_valid_csrf_token(): void
    {
        Setting::set('operating_hours', 'Monday to Saturday, 6:00 AM to 6:00 PM');
        Setting::set('clinic_contact_number', '(044) 123-4567');

        $token = 'test-csrf-token';

        $this->withSession(['_token' => $token])
            ->post('/chatbot/message', [
                '_token' => $token,
                'message' => 'What are your operating hours?',
            ])
            ->assertOk()
            ->assertJsonPath('reply', 'Our center is open Monday to Saturday, 6:00 AM to 6:00 PM. Please contact (044) 123-4567 to confirm holiday schedules.');
    }

    public function test_lookup_search_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/lookup/search', [
                'patient_code' => 'INVALID',
                'birthdate' => '1990-01-01',
            ])->assertStatus(302);
        }

        $this->post('/lookup/search', [
            'patient_code' => 'INVALID',
            'birthdate' => '1990-01-01',
        ])->assertStatus(429);
    }

    public function test_specific_faq_outscores_a_generic_one_sharing_a_common_word(): void
    {
        Http::fake();

        $reply = app(ChatbotService::class)->ask('How should I prepare for my first dialysis session?');

        $this->assertStringContainsString('arrive a little early', strtolower($reply));
    }

    public function test_inactive_database_faq_is_ignored(): void
    {
        ChatbotFaq::create([
            'question' => 'Do you offer home dialysis?',
            'answer' => 'Yes we do.',
            'keywords' => 'home dialysis, dialysis at home',
            'is_active' => false,
        ]);

        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'General answer from AI.']]]],
                ],
            ], 200),
        ]);

        $reply = app(ChatbotService::class)->ask('Do you offer home dialysis?');

        $this->assertSame('General answer from AI.', $reply);
    }

    public function test_newly_seeded_faq_is_immediately_searchable(): void
    {
        ChatbotFaq::create([
            'question' => 'Can I bring a companion to my session?',
            'answer' => 'Yes, one companion may accompany you.',
            'keywords' => 'bring a companion, companion during dialysis, visitor during treatment',
            'is_active' => true,
        ]);

        Http::fake();

        $reply = app(ChatbotService::class)->ask('Can I bring a companion to my session?');

        $this->assertSame('Yes, one companion may accompany you.', $reply);
        Http::assertSentCount(0);
    }

    public function test_setting_placeholders_are_substituted_in_preset_answers(): void
    {
        Setting::set('clinic_contact_number', '0912-345-6789');
        Setting::set('operating_hours', 'Mon–Sat, 7AM–5PM');

        Http::fake();

        $reply = app(ChatbotService::class)->ask('What are your operating hours?');

        $this->assertStringContainsString('7AM–5PM', $reply);
        $this->assertStringContainsString('0912-345-6789', $reply);
        $this->assertStringNotContainsString('{{', $reply);
    }

    public function test_preset_match_does_not_fire_on_a_single_generic_word(): void
    {
        config(['services.chatbot.provider' => 'none']);

        $reply = app(ChatbotService::class)->ask('I need some help with something unrelated.');

        $this->assertStringContainsString('not configured', strtolower($reply));
    }
}