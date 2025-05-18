<?php

use App\Models\Chat;
use App\Models\Chunk;
use App\Services\HevyService;
use App\Services\TrelloService;
use App\Tools\Agents\Fitness\LiftSearchTool;
use App\Tools\Hevy\HevyGetWorkoutEventsTool;
use App\Tools\Hevy\HevyGetWorkoutsByDateTool;
use App\Tools\Hevy\HevyGetWorkoutsByExerciseTool;
use App\Tools\Hevy\HevyGetWorkoutsTool;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\UserMessage;

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
    $chat = Chat::first();
    $message = $chat->messages()->updateOrCreate([
        'source_id' => 'test!',
        'role' => 'user',
    ], [
        'content' => 'whats my best bench ever',
    ]);
    $messages = Chat::first()->prismMessages();
    // dd($messages);
    $prism = Prism::text()
            ->using(Provider::Gemini, 'gemini-2.0-flash')
            ->withSystemPrompt(view('prompts.agents.fitness.coordinator'))
            ->withTools([
                // new HevyGetWorkoutsTool('c-00000000000000000000000000000000000000xy92pdqmolvubsssk7i2af8v1j'),
                new HevyGetWorkoutsByDateTool('test'),
                new HevyGetWorkoutsByExerciseTool('test'),
            ])
            ->withMaxSteps(5);
    // dd($messages);
    $answer = $prism->withMessages($messages->toArray())
        ->asText();
    dd($answer->text);
})->purpose('Get workout events from Hevy');