<?php

use App\Models\Chunk;
use App\Services\HevyService;
use App\Services\TrelloService;
use App\Tools\Hevy\HevyGetWorkoutEventsTool;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

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
    // $hevyService = new HevyService();
    // $response = $hevyService->getWorkouts();
    // $response = $hevyService->getWorkoutEvents();
    $hevy = new HevyGetWorkoutEventsTool();
    $response = $hevy->__invoke('2025-05-03T00:00:00Z');
    dd($response);
})->purpose('Get workouts from Hevy');