<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

Route::get('/user', function (Request $request) {
    return [
        'user' => 'johnny test'
    ];
});

Route::post('/echo', function (Request $request) {
    try {
        $data = $request->json()->all();

        $conversationId = $data['conversation_id'] ?? 'N/A';
        $userId = $data['user_id'] ?? 'N/A';

        $queryData = $data['query'] ?? [];
        $content = end($queryData)['content'] ?? 'No content available';

        return new StreamedResponse(function () use ($content) {
            echo "event: meta\n";
            echo "data: " . json_encode([
                'content_type' => 'text/markdown',
                'suggested_replies' => false,
            ]) . "\n\n";

            echo "event: text\n";
            echo "data: " . json_encode([
                'text' => "Hi there! You said: $content"
            ]) . "\n\n";

            echo "event: suggested_reply\n";
            echo "data: " . json_encode(['text' => 'Can you tell me more?']) . "\n\n";

            echo "event: suggested_reply\n";
            echo "data: " . json_encode(['text' => 'What would you like to do next?']) . "\n\n";

            echo "event: done\n";
            echo "data: {}\n\n";
            flush(); // Ensure it's sent in chunks
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    } catch (\Throwable $e) {
        $errorMessage = "An error occurred: " . $e->getMessage();

        return response()->stream(function () use ($errorMessage) {
            echo "event: error\n";
            echo "data: " . json_encode([
                'text' => $errorMessage,
                'allow_retry' => true,
            ]) . "\n\n";
            flush();
        }, 500, [
            'Content-Type' => 'text/event-stream',
        ]);
    }
});
