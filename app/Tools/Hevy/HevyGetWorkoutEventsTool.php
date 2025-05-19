<?php

namespace App\Tools\Hevy;

use App\Services\HevyService;
use Prism\Prism\Tool;

class HevyGetWorkoutEventsTool extends Tool
{
    protected HevyService $hevy;

    public function __construct()
    {
        $this->hevy = new HevyService();
        $this->as('Hevy')
            ->for('useful when you need to search for lifting workouts on the Hevy app from May 3, 2025 and after.')
            ->withStringParameter('since', 'A specific date and time in ISO 8601 format (e.g., “2025-05-03T00:00:00Z”) that the lifting agent should search for workouts since this date.')
            ->using($this);
    }

    public function __invoke(string $since): string
    {
        $response = $this->hevy->getWorkoutEvents($since);

        $results = collect($response['events'])
            ->where('type', 'updated')
            ->filter(fn ($e) => isset($e['workout']['id']))
            ->unique(fn ($e) => $e['workout']['id'])
            // ->where('workout', '!=', null)
            // ->where('workout.id', '!=', null)
            // ->unique('workout.id')
            ->pluck('workout')
            ->sortBy('start_time')
            ->values();

        $modifiedResults = $results->map(function ($result) {
            return [
                'id' => $result['id'],
                'title' => $result['title'],
                'description' => $result['description'],
                'start_time' => $result['start_time'],
                'end_time' => $result['end_time'],
                'updated_at' => $result['updated_at'],
                'created_at' => $result['created_at'],

                'exercises' => collect($result['exercises'])->map(function ($exercise) {
                    return [
                        'index' => $exercise['index'],
                        'title' => $exercise['title'],
                        'notes' => $exercise['notes'],
                        'exercise_template_id' => $exercise['exercise_template_id'],
                        'superset_id' => $exercise['superset_id'],
                        'sets' => collect($exercise['sets'])->map(function ($set) {
                            return [
                                'index' => $set['index'],
                                'type' => $set['type'],
                                'weight_kg' => $set['weight_kg'],
                                'reps' => $set['reps'],
                                'distance_meters' => $set['distance_meters'],
                                'duration_seconds' => $set['duration_seconds'],
                                'rpe' => $set['rpe'],
                                'custom_metric' => $set['custom_metric'],
                            ];
                        })->all(),
                    ];
                }),
            ];
        })->all();

        return view('prompts.hevy.hevy-get-workouts-tool-results', [
            'results' => $modifiedResults
        ])->render();
    }
}