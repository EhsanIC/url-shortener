<?php

namespace Tests\Feature;

use App\Models\Pastebin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PastebinTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_pasted_text_is_saved_as_a_file_named_by_its_text_id(): void
    {
        Storage::fake('local');

        $response = $this->postJson('/pastebins', [
            'text' => "line one\nline two\n",
        ]);

        // 201 + metadata
        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'text_id', 'size_byte', 'expire_at', 'created_at', 'updated_at']);

        $textId = $response->json('text_id');

        // File exists, named "{text_id}.txt", holding the exact text
        Storage::disk('local')->assertExists("pastes/{$textId}.txt");
        $this->assertSame("line one\nline two\n", Storage::disk('local')->get("pastes/{$textId}.txt"));
    }

    #[Test]
    public function size_byte_is_stored_in_bytes_of_the_text(): void
    {
        Storage::fake('local');

        $text = "hello pastebin";
        $response = $this->postJson('/pastebins', ['text' => $text]);

        $this->assertSame(strlen($text), $response->json('size_byte'));

        $this->assertDatabaseHas('pastebins', [
            'text_id' => $response->json('text_id'),
            'size_byte' => 14,
        ]);
    }

    #[Test]
    public function expire_at_defaults_to_null_never_expires(): void
    {
        Storage::fake('local');

        $response = $this->postJson('/pastebins', ['text' => 'forever paste']);

        $this->assertNull($response->json('expire_at'));

        $this->assertDatabaseHas('pastebins', [
            'text_id' => $response->json('text_id'),
        ]);
        $this->assertNull(Pastebin::where('text_id', $response->json('text_id'))->value('expire_at'));
    }

    #[Test]
    public function it_rejects_empty_text(): void
    {
        Storage::fake('local');

        $this->postJson('/pastebins', ['text' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text']);

        Pastebin::query()->count();
        $this->assertSame(0, Pastebin::count());
        Storage::disk('local')->assertDirectoryEmpty('pastes');
    }

    #[Test]
    public function text_id_matches_the_short_url_code_format(): void
    {
        Storage::fake('local');

        $response = $this->postJson('/pastebins', ['text' => 'x']);

        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{8}$/', $response->json('text_id'));
    }

    #[Test]
    public function a_paste_can_be_deleted_with_its_file(): void
    {
        Storage::fake('local');

        $textId = $this->postJson('/pastebins', ['text' => 'delete me'])->json('text_id');

        Storage::disk('local')->assertExists("pastes/{$textId}.txt");

        $this->deleteJson("/pastebins/{$textId}")
            ->assertStatus(204);

        // File and metadata row are both gone
        Storage::disk('local')->assertMissing("pastes/{$textId}.txt");
        $this->assertDatabaseMissing('pastebins', ['text_id' => $textId]);
        $this->assertSame(0, Pastebin::count());
    }

    #[Test]
    public function deleting_an_unknown_paste_returns_404(): void
    {
        $this->deleteJson('/pastebins/zzzzzzzz')
            ->assertStatus(404);
    }
}
