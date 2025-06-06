<?php

use App\Models\Chat;
use App\Models\Token;
use App\Services\HevyService;
use App\Tools\Agents\Fitness\LiftSearchTool;
use App\Tools\Hevy\HevyGetRoutinesTool;
use App\Tools\Hevy\HevyGetWorkoutEventsTool;
use App\Tools\Hevy\HevyGetWorkoutsByDateTool;
use App\Tools\Hevy\HevyGetWorkoutsByExerciseTool;
use App\Tools\Hevy\HevyGetWorkoutsTool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Symfony\Component\HttpFoundation\StreamedResponse;

Route::post('/hevy', function (Request $request) {
    try {
        // Extract the JSON data from the request
        $data = $request->json()->all();
        Log::info('Received Hevy request', [
            'data' => $data
        ]);
        $chatId = $data['conversation_id'];
        $messageId = $data['message_id'];
        $userId = $data['user_id'];
        $queryData = $data['query'];
        $content = end($queryData)['content'];

        // Make sure user has token
        $token = Token::where('user_id', $userId)
            ->where('type', 'poe')
            ->where('service', 'hevy')
            ->first()
            ?->token;

        if (!$token) {
            return new StreamedResponse(function () use ($userId) {
                // Disable buffering
                if (ob_get_level()) ob_end_clean();
                ini_set('zlib.output_compression', 0);
                ini_set('output_buffering', 'off');
                ini_set('implicit_flush', 1);
                while (ob_get_level() > 0) {
                    ob_end_flush();
                }
                flush();

                // Send the initial event metadata
                echo "event: meta\n";
                echo "data: " . json_encode([
                    'content_type' => 'text/markdown',
                    'suggested_replies' => false,
                ]) . "\n\n";

                echo "event: text\n";
                echo "data: " . json_encode(['text' => "You need to link your Hevy account first. Your user ID is:\n" . $userId]) . "\n\n";
                flush();

                // Add this to tell Poe the response is finished
                echo "event: done\n";
                echo "data: {}\n\n";
                flush(); // Ensure it's sent in chunks
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        # Next -> add token to DB for user.

        // Create the chat and user message
        // $chat = Chat::firstOrCreate([
        //     'source_id' => $chatId,
        //     'type' => 'poe',
        //     // 'user_id' => $data['user_id'] ?? null,
        // ]);
        // $message = $chat->messages()->updateOrCreate([
        //     'source_id' => $messageId,
        //     'role' => 'user',
        // ], [
        //     'content' => $content,
        // ]);

        // Run the Hevy AI Agent
        // $messages = $chat->prismMessages()->toArray();
        $messages = collect($queryData)->map(function ($message) {
            switch ($message['role']) {
                case 'user':
                    return new UserMessage($message['content']);
                case 'bot':
                    return new AssistantMessage($message['content']);
                default:
                    return new UserMessage($message['content']);
            }
        })->toArray();

        $prism = Prism::text()
            ->using(Provider::Gemini, 'gemini-2.0-flash')
            ->withSystemPrompt(view('prompts.agents.fitness.coordinator'))
            ->withTools([
                new HevyGetWorkoutsByDateTool($chatId, $token),
                new HevyGetWorkoutsByExerciseTool($chatId, $token),
                new HevyGetRoutinesTool($chatId, $token),
            ])
            ->withMaxSteps(5);

        // return $messages;
        $answer = $prism->withMessages($messages)
            // ->asText();
            ->asStream();
        Log::info('Answer:', [
            'answer' => $answer,
        ]);

        // return response()->json([
        //     'answer' => $answer
        // ]);

        return new StreamedResponse(function () use ($answer, $messageId) {
            // Disable buffering
            ob_end_clean();
            ini_set('zlib.output_compression', 0);
            ini_set('output_buffering', 'off');
            ini_set('implicit_flush', 1);
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            flush();

            // Send the initial event metadata
            echo "event: meta\n";
            echo "data: " . json_encode([
                'content_type' => 'text/markdown',
                'suggested_replies' => false,
            ]) . "\n\n";

            $fullResponse = '';
            foreach ($answer as $chunk) {
                Log::info('Received chunk from Hevy AI', [
                    'chunk' => $chunk,
                ]);
                $text = $chunk->text;

                if ($text === '') continue;
                $fullResponse .= $text;

                // Simulate token chunking (e.g., 5-character chunks)
                $miniChunks = str_split($text, 5);
                foreach ($miniChunks as $miniChunk) {
                    echo "event: text\n";
                    echo "data: " . json_encode(['text' => $miniChunk]) . "\n\n";
                    flush();
                    usleep(40 * 1000); // Optional: smoother typing feel (40ms = ~25 tokens/sec)
                }
            }

            // $chat->messages()->updateOrCreate([
            //     'source_id' => $messageId,
            //     'role' => 'assistant',
            // ], [
            //     'content' => $fullResponse ?: 'An error occured finding your response. Please try again.',
            // ]);

            if ($fullResponse === '') {
                echo "event: text\n";
                echo "data: " . json_encode(['text' => "Sorry, I couldn't generate a response. Please try again later."]) . "\n\n";
                flush();
            }

            echo "event: suggested_reply\n";
            echo "data: " . json_encode(['text' => 'Can you tell me more?']) . "\n\n";

            echo "event: suggested_reply\n";
            echo "data: " . json_encode(['text' => 'How much did I lift the last time I did Bench Press?']) . "\n\n";

            echo "event: suggested_reply\n";
            echo "data: " . json_encode(['text' => 'What have I worked out this week?']) . "\n\n";

            echo "event: done\n";
            echo "data: {}\n\n";
            flush(); // Ensure it's sent in chunks
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no'
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
        $errorMessage = "An error occurred on line " . $e->getLine() . ": " . $e->getMessage();

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
})->name('hevy');

###############
### Testing ###
###############

Route::post('/user', function (Request $request) {
    try
    {
        $data = $request->json()->all();
        $user = $data['user_id'];
        $token = Token::where('user_id', $user)
            ->where('type', 'poe')
            ->where('service', 'hevy')
            ->first();
        return [
            'user' => $user,
            'token' =>$token
        ];
    }
    catch (Exception $e)
    {
        return response()->json([
            'error' => 'Invalid JSON',
            'message' => $e->getMessage(),
        ], 400);
    }
});

Route::post('/test', function (Request $request) {
    try
    {
        // Extract the JSON data from the request
        $data = $request->json()->all();
        $chatId = $data['conversation_id'];
        $messageId = $data['message_id'];
        $userId = $data['user_id'];
        $queryData = $data['query'];
        $content = end($queryData)['content'];

        // Create the chat and user message
        $chat = Chat::firstOrCreate([
            'source_id' => $chatId,
            'type' => 'poe',
            // 'user_id' => $data['user_id'] ?? null,
        ]);
        $message = $chat->messages()->updateOrCreate([
            'source_id' => $messageId,
            'role' => 'user',
        ], [
            'content' => $content,
        ]);

        // Run the Hevy AI Agent
        $messages = $chat->prismMessages()->toArray();
        $prism = Prism::text()
            ->using(Provider::Gemini, 'gemini-2.0-flash')
            ->withSystemPrompt(view('prompts.agents.fitness.coordinator'))
            ->withTools([
                // new HevyGetWorkoutsByDateTool($chatId),
                // new HevyGetWorkoutsByExerciseTool($chatId),
            ])
            ->withMaxSteps(5);
        $answer = $prism->withMessages($messages)
            ->asText();
        return response()->json([
            'answer' => $answer->text,
        ]);
    }
    catch (PrismException $e)
    {
        return response()->json([
            'error' => 'PrismException',
            'message' => $e->getMessage(),
        ]);
    }
    catch (\Throwable $e)
    {
        return response()->json([
            'error' => 'Throwable',
            'message' => $e->getMessage(),
        ]);
    }
    {

    }
});
