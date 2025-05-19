<?php

namespace App\Tools\Hevy;

use App\Services\HevyService;
use Prism\Prism\Tool;

class HevyGetWorkoutsTool extends Tool
{
    protected HevyService $hevy;

    public function __construct()
    {
        $this->hevy = new HevyService();
        $this->as('Hevy')
            ->for('useful when you need to search for lifting workouts on the Hevy app.')
            ->using($this);
    }

    public function __invoke(): string
    {
        $response = $this->hevy->getWorkouts();

        $results = collect($response['workouts']);

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