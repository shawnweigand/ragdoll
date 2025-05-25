<?php

use App\Models\Chat;
use App\Models\Chunk;
use App\Services\TrelloService;
use App\Tools\Hevy\HevyGetRoutinesTool;
use App\Tools\Hevy\HevyGetWorkoutsByDateTool;
use App\Tools\Hevy\HevyGetWorkoutsByExerciseTool;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('trello', function () {
    $trello = new TrelloService();
    // $response = $trello->getCards();
    // $response = $trello->getCard('piXpwxOr');
    $response = $trello->search('prism');
    dd(collect($response['cards']));
});

Artisan::command('embed', function () {
    $results = Chunk::search('do I ahve any plans for patagonia w trek?')->get();
    dd($results->filter(function ($result) {
            return $result->embedding['neighbor_distance'] <= 0.3; // or whatever threshold works empirically
        })->take(5)->map(function ($result) {
            return [
                'index' => $result->index ?? null,
                'content' => $result->content ?? null,
                'document' => $result->document ?? null,
                'neighbor_distance' => $result->embedding['neighbor_distance'] ?? null,
            ];
        })
    )->toArray();
})->purpose('Embed all documents');

Artisan::command('hevy', function () {
    // dd(Carbon::parse('2025-05-03T00:00:00Z')->toIso8601String());
    // $response = $hevyService->getWorkoutEvents();
    // $hevy = new HevyGetWorkoutEventsTool();
    // $response = $hevy->__invoke('2024-12-03T00:00:00Z', '2025-01-01T00:00:00Z');
    // dd($response);
})->purpose('Get workouts from Hevy');

Artisan::command('hey', function () {
    // $messages = [new UserMessage('whats my best squat of all time')];
    $chat = Chat::firstOrCreate([
        'source_id' => 'test',
        'type' => 'poe',
    ]);
    $message = $chat->messages()->updateOrCreate([
        'source_id' => 'test1',
        'role' => 'user',
    ], [
        'content' => 'heres my api key e4f121c1-bff0-441f-a065-62fc3fd5c571',
    ]);
    $messages = $chat->prismMessages();
    // dd($messages);
    $prism = Prism::text()
            ->using(Provider::Gemini, 'gemini-2.0-flash')
            ->withSystemPrompt(view('prompts.agents.fitness.coordinator'))
            ->withTools([
                new HevyGetWorkoutsByDateTool('test'),
                new HevyGetWorkoutsByExerciseTool('test'),
                new HevyGetRoutinesTool('test')
            ])
            ->withMaxSteps(5);
    // dd($messages);
    $answer = $prism->withMessages($messages->toArray())
        ->asText();
    $chat->messages()->updateOrCreate([
        'source_id' => 'test1',
        'role' => 'assistant',
    ], [
        'content' => $answer->text ?: 'An error occured finding your response. Please try again.',
    ]);
    dd($answer->text);
})->purpose('Get workout events from Hevy');