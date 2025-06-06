<?php

namespace App\Tools\Hevy;

use App\Services\HevyService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Tool;

class HevyGetRoutinesTool extends Tool
{
    protected HevyService $hevy;
    protected int $pageCount = 10;
    protected string $cacheKey;

    public function __construct($cacheKey, $token)
    {
        $this->cacheKey = $cacheKey;
        $this->hevy = new HevyService($token);
        $this->as('HevyGetRoutinesTool')
            ->for('searching for your personalized workout routines, these are typically repeated workouts.')
            ->using($this);
    }

    public function __invoke(): string
    {
        try {
            // Cache the results for 1 hour
            $routines = collect(Cache::remember('routines:' . $this->cacheKey, $seconds = 3600, function () {
                // This callback only runs if the key is not in the cache.
                return $this->hevy->getAllRoutines();
            }));

            // Modify the results for processing
            $modifiedResults = $routines->map(function ($result) {
                return [
                    'id' => $result['id'],
                    'title' => $result['title'],
                    'folder_id' => $result['folder_id'],
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
                                    'custom_metric' => $set['custom_metric'],
                                ];
                            })->all(),
                        ];
                    }),
                ];
            })->all();

            return view('prompts.hevy.hevy-get-routines-tool-results', [
                'results' => $modifiedResults
            ])->render();
        } catch (\Exception $e) {
            Log::error('HevyGetRoutinesTool error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return $e->getMessage();
        }

    }
}
