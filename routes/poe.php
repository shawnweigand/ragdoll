<?php

use App\Models\Chat;
use App\Tools\Agents\Fitness\LiftSearchTool;
use App\Tools\Hevy\HevyGetWorkoutEventsTool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Prism;
use Symfony\Component\HttpFoundation\StreamedResponse;

Route::get('/user', function (Request $request) {
    return [
        'user' => 'johnny test'
    ];
});

Route::post('/echo', function (Request $request) {
    try {
        $data = $request->json()->all();

        $chatId = $data['conversation_id'];
        $messageId = $data['message_id'];
        $userId = $data['user_id'];


        $chat = Chat::firstOrCreate([
            'source_id' => $chatId,
            'type' => 'poe',
            // 'user_id' => $data['user_id'] ?? null,
        ]);

        $queryData = $data['query'];
        $content = end($queryData)['content'];

        $message = $chat->messages()->updateOrCreate([
            'source_id' => $messageId,
            'role' => 'user',
        ], [
            'content' => $content,
        ]);

        $messages = $chat->prismMessages();

        $prism = Prism::text()
            ->using(Provider::Gemini, 'gemini-2.0-flash')
            ->withSystemPrompt(view('prompts.agents.fitness.coordinator'))
            ->withTools([
                // new HevyGetWorkoutEventsTool(),
                new LiftSearchTool(),
            ])
            ->withMaxSteps(5);

        $answer = $prism->withMessages($messages->toArray())
            ->asStream();

        return new StreamedResponse(function () use ($chat, $answer, $messageId) {

            // Send the initial event metadata
            echo "event: meta\n";
            echo "data: " . json_encode([
                'content_type' => 'text/markdown',
                'suggested_replies' => false,
            ]) . "\n\n";

            $fullResponse = '';
            foreach ($answer as $chunk) {
                $fullResponse .= $chunk->text;

                // Send the chunked response
                echo "event: text\n";
                echo "data: " . json_encode([
                    'text' => $chunk->text,
                ]) . "\n\n";

                // Check for tool calls
                // if ($chunk->toolCalls) {
                //     foreach ($chunk->toolCalls as $call) {
                //         $body = '';
                //         foreach ($call->arguments() as $key => $value) {
                //             $key = $key;
                //             $body .= "$key: $value\n";
                //         }
                //         $this->box($call->name, wordwrap($body, 60), color: 'blue');
                //     }
                // }
            }

            $chat->messages()->updateOrCreate([
                'source_id' => $messageId,
                'role' => 'assistant',
            ], [
                'content' => $fullResponse,
            ]);

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

    } catch (PrismException $e) {
        $errorMessage = "Text generation failed:" . $e->getMessage();

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
