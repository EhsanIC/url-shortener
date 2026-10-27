<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShortUrlRequest;
use App\Models\ShortUrl;
use App\Services\ShortCodeGenerator;
use Illuminate\Http\JsonResponse;

class ShortUrlController extends Controller
{
    /**
     * Store a newly created short url.
     */
    public function store(StoreShortUrlRequest $request): JsonResponse
    {
        $shortUrl = ShortUrl::create([
            'original_url' => $request->validated('original_url'),
            'short_url' => ShortCodeGenerator::generate(),
        ]);

        return response()->json($shortUrl, 201);
    }

    /**
     * Read: resolve the short url and redirect to the original.
     */
    public function show(ShortUrl $shortUrl)
    {
        return redirect()->away($shortUrl->original_url);
    }
}
