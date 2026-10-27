<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePasteRequest;
use App\Models\Pastebin;
use App\Services\ShortCodeGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PastebinController extends Controller
{
    /**
     * Store the pasted text as a file named by its text_id,
     * and save the metadata in the pastebins table.
     */
    public function store(StorePasteRequest $request): JsonResponse
    {
        $text = $request->validated('text');

        $textId = ShortCodeGenerator::generate(table: 'pastebins', column: 'text_id');

        Storage::disk('local')->put("pastes/{$textId}.txt", $text);

        $paste = Pastebin::create([
            'text_id' => $textId,
            'size_byte' => strlen($text),
            'expire_at' => $request->validated('expire_at'),
        ]);

        return response()->json($paste, 201);
    }

    /**
     * Read: show the content of a stored paste.
     */
    public function show(Pastebin $paste)
    {
        abort_unless(
            Storage::disk('local')->exists("pastes/{$paste->text_id}.txt"),
            404,
        );

        return response(
            Storage::disk('local')->get("pastes/{$paste->text_id}.txt"),
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        );
    }

    /**
     * Delete a paste: remove both the file and its metadata row.
     */
    public function destroy(Pastebin $paste): JsonResponse
    {
        Storage::disk('local')->delete("pastes/{$paste->text_id}.txt");

        $paste->delete();

        return response()->json(status: 204);
    }
}
