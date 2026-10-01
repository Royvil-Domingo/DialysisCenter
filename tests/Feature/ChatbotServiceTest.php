<?php

namespace Tests\Feature;

use App\Services\ChatbotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_a_default_answer_for_common_questions_when_faqs_are_missing(): void
    {
        $reply = app(ChatbotService::class)->ask('What are your operating hours?');

        $this->assertIsString($reply);
        $this->assertNotSame("I'm not sure about that — please contact our staff directly.", $reply);
        $this->assertStringContainsString('Monday', $reply);
    }
}
