<?php

namespace Tests\Feature;

use App\Models\Analytics;
use App\Models\ShortUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function first_hit_creates_the_row_with_a_count_of_one(): void
    {
        Carbon::setTestNow('2026-10-27 12:00:00');

        $record = Analytics::recordHit('abcdefgh');

        $this->assertSame(1, $record->click_count);
        $this->assertSame(1, Analytics::count());
        $this->assertSame('2026-10-27 12:00:00', $record->last_accessed->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function repeated_hits_increment_the_count_but_keep_one_row(): void
    {
        Analytics::recordHit('abcdefgh');
        Analytics::recordHit('abcdefgh');
        $record = Analytics::recordHit('abcdefgh');

        $this->assertSame(3, $record->click_count);
        $this->assertSame(1, Analytics::where('short_url', 'abcdefgh')->count());
        $this->assertSame(1, Analytics::count());
    }

    #[Test]
    public function last_accessed_is_refreshed_on_every_hit(): void
    {
        Carbon::setTestNow('2026-10-27 09:00:00');
        Analytics::recordHit('abcdefgh');

        Carbon::setTestNow('2026-10-27 15:30:00');
        $record = Analytics::recordHit('abcdefgh');

        $this->assertSame('2026-10-27 15:30:00', $record->last_accessed->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function hits_are_tracked_separately_per_short_url(): void
    {
        ShortUrl::create(['original_url' => 'https://google.com/', 'short_url' => 'aaaaaaaa']);
        ShortUrl::create(['original_url' => 'https://laravel.com/', 'short_url' => 'bbbbbbbb']);

        Analytics::recordHit('aaaaaaaa');
        Analytics::recordHit('aaaaaaaa');
        Analytics::recordHit('bbbbbbbb');

        $this->assertDatabaseHas('analytics', ['short_url' => 'aaaaaaaa', 'click_count' => 2]);
        $this->assertDatabaseHas('analytics', ['short_url' => 'bbbbbbbb', 'click_count' => 1]);
    }

    #[Test]
    public function a_click_on_a_short_link_updates_analytics_end_to_end(): void
    {
        $shortUrl = $this->postJson('/short-urls', ['original_url' => 'https://google.com/'])->json('short_url');

        $this->get('/'.$shortUrl)->assertStatus(302);
        $this->get('/'.$shortUrl)->assertStatus(302);

        $this->assertSame(2, Analytics::where('short_url', $shortUrl)->value('click_count'));
    }
}
