<?php

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return [
        'user' => 'johnny test'
    ];
});

Route::post('/chunk', function (Request $request) {
    $document = Document::updateOrCreate(
        // Find by source_id and type
        [
            'source' => $request->input('source'),
            'type' => $request->input('type'),
        ],
        // Update or set these values
        [
            'name' => $request->input('name'),
            'parent_id' => $request->input('parent_id'),
        ]
    );

    # Find a way to update or create chunks for the docs if they change, and remove all old ones
    $chunk = $document->chunks()->updateOrCreate(
        [
            'index' => $request->input('index'),
        ],
        [
            'content' => $request->input('content'),
            'meta' => $request->input('meta'),
            'tags' => $request->input('tags'),
        ]
    );

    // remove all the chunks after this index if the chunk was updated
    if ($chunk->wasChanged('content')) {
        $document->chunks()
            ->where('index', '>', $chunk->index)
            ->delete();
    }

    return [
        'request' => $chunk,
    ];
});