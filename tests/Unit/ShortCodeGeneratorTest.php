<?php

namespace Tests\Unit;

use App\Models\ShortUrl;
use App\Services\ShortCodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShortCodeGeneratorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_generates_a_code_of_the_requested_length(): void
    {
        $code = ShortCodeGenerator::generate();

        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{8}$/', $code);
    }

    #[Test]
    public function it_skips_codes_that_already_exist(): void
    {
        // Force Str::random to first return a code that IS already taken,
        // then a unique one. The generator must skip the first attempt.
        Str::createRandomStringsUsingSequence(['AAAAAAAAAA', 'BBBBBBBBBB']);

        ShortUrl::create([
            'original_url' => 'https://example.com/',
            'short_url' => 'AAAAAAAAAA',
        ]);

        $code = ShortCodeGenerator::generate();

        $this->assertSame('BBBBBBBBBB', $code);
    }
}
