<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analytics extends Model
{
    protected $fillable = ['short_url', 'click_count', 'last_accessed'];

    protected function casts(): array
    {
        return [
            'last_accessed' => 'datetime',
        ];
    }

    /**
     * Track a click on a short url: create the counter row on first hit,
     * then atomically bump the counter and refresh the timestamp.
     */
    public static function recordHit(string $shortUrl): static
    {
        $record = static::firstOrCreate(
            ['short_url' => $shortUrl],
            ['click_count' => 0],
        );

        $record->increment('click_count');

        $record->update(['last_accessed' => now()]);

        return $record;
    }
}
