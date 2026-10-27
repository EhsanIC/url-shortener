<?php

namespace Tests\Feature;

use App\Models\ShortUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShortUrlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The full flow a user experiences:
     * 1. Submit https://google.com/
     * 2. Get back a short code
     * 3. Visit the short link and land on https://google.com/
     */
    public function test_user_can_create_a_short_url_and_then_get_it(): void
    {
        // 1. User submits a url
        $response = $this->postJson('/short-urls', [
            'original_url' => 'https://google.com/',
        ]);

        // 2. Server answers 201 with the short code and persists it
        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'original_url', 'short_url', 'created_at', 'updated_at']);

        $shortUrl = $response->json('short_url');

        $this->assertDatabaseHas('short_urls', [
            'original_url' => 'https://google.com/',
            'short_url' => $shortUrl,
        ]);

        // 3. User visits the short link and is redirected to the original
        $this->get('/'.$shortUrl)
            ->assertRedirect('https://google.com/')
            ->assertStatus(302);

        // 4. The click is tracked
        $this->assertDatabaseHas('analytics', [
            'short_url' => $shortUrl,
            'click_count' => 1,
        ]);

        // 5. A second visit bumps the counter to 2
        $this->get('/'.$shortUrl)
            ->assertStatus(302);

        $this->assertDatabaseHas('analytics', [
            'short_url' => $shortUrl,
            'click_count' => 2,
        ]);
    }

    public function test_it_rejects_invalid_urls(): void
    {
        $this->postJson('/short-urls', [
            'original_url' => 'not a url',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['original_url']);
    }

    public function test_generated_short_codes_are_unique(): void
    {
        $first = $this->postJson('/short-urls', ['original_url' => 'https://google.com/'])->json('short_url');
        $second = $this->postJson('/short-urls', ['original_url' => 'https://laravel.com/'])->json('short_url');

        $this->assertNotSame($first, $second);
        $this->assertSame(2, ShortUrl::count());
    }
}
