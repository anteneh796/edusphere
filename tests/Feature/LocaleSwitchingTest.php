<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_switching_to_amharic_renders_translated_content(): void
    {
        $this->get(route('public.locale', 'am'))
            ->assertRedirect();

        $response = $this->get(route('public.home'));

        $response->assertOk();
        $response->assertSee('መነሻ', false);

        $html = $response->getContent();
        $this->assertStringContainsString(
            'class="active"',
            $html,
            'The Amharic option should be marked active after switching.'
        );
    }
}
