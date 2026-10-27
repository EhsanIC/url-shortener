<?php

namespace App\Services;

use App\Models\ShortUrl;
use Illuminate\Support\Str;

class ShortCodeGenerator
{
    /**
     * Generate a short code that doesn't already exist in the DB.
     */
    public static function generate(int $length = 8): string
    {
        do {
            $code = Str::random($length);
        } while (ShortUrl::where('short_url', $code)->exists());

        return $code;
    }
}
