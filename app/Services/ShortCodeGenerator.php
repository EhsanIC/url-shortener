<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShortCodeGenerator
{
    /**
     * Generate a short code that doesn't already exist in the given table.
     */
    public static function generate(
        int $length = 8,
        string $table = 'short_urls',
        string $column = 'short_url',
    ): string {
        do {
            $code = Str::random($length);
        } while (DB::table($table)->where($column, $code)->exists());

        return $code;
    }
}
