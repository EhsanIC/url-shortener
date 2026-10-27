<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortUrl extends Model
{
    protected $fillable = ['original_url', 'short_url'];

    /**
     * Route keys and lookups use the short code instead of the id.
     */
    public function getRouteKeyName(): string
    {
        return 'short_url';
    }
}
