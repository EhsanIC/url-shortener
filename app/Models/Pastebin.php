<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pastebin extends Model
{
    protected $fillable = ['text_id', 'size_byte', 'expire_at'];

    protected function casts(): array
    {
        return [
            'expire_at' => 'datetime',
        ];
    }

    /**
     * Route keys and lookups use the text id instead of the pk.
     */
    public function getRouteKeyName(): string
    {
        return 'text_id';
    }
}
