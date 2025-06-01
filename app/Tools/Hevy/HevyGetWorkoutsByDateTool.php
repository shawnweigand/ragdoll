<?php

namespace App\Tools\Hevy;

use App\Services\HevyService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Tool;

class HevyGetWorkoutsByDateTool extends Tool
{
    protected HevyService $hevy;
    protected int $pageCount = 10;
    protected string $cacheKey;

    public function __construct($cacheKey, $apiKey)
    {
        $this->cacheKey = $cacheKey;
        $this->hevy = new HevyService($apiKey);
        $this->as('HevyGetWorkoutsByDateTool')
            ->for('searching for workouts on a single date or between two date ranges.')
            ->withStringParameter('start', 'the start date for the search in ISO 8601 format (e.g. 2023-10-01T00:00:00Z)')
            ->withStringParameter('end', 'the end date for the search in ISO 8601 format (e.g. 2023-10-30T23:59:59Z)')
            ->using($this);
            // ->withArrayParameter(
            //     'workouts',
            //     'the workouts to search through',
            //     $this->workoutSchema()
            // )
    }

    public function __invoke(string $start, string $end): string
    {
        try {
            // Cache the results for 1 hour
            $workouts = collect(Cache::remember('workouts:' . $this->cacheKey, $seconds = 3600, function () {
            // This callback only runs if the key is not in the cache.
                return $this->hevy->getAllWorkouts();
            }));

            // Parse the start and end dates
            $startDate = Carbon::parse($start);
            $endDate = Carbon::parse($end);

            // Filter the results based on the start and end dates
            $filteredWorkouts= $workouts->filter(function ($workout) use ($startDate, $endDate) {
                $workoutStartTime = Carbon::parse($workout['start_time']);
                return $workoutStartTime->isBetween($startDate, $endDate, true);
            });

            // Modify the results for processing
            $modifiedResults = $filteredWorkouts->map(function ($result) {
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
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    // protected function setSchema()
    // {
    //     return new ObjectSchema(
    //         name: 'set',
    //         description: 'Details of a workout set',
    //         properties: [
    //             new NumberSchema('index', 'The index of the set'),
    //             new StringSchema('type', 'The type of the set'),
    //             new NumberSchema('weight_kg', 'Weight used in kilograms'),
    //             new NumberSchema('reps', 'Number of repetitions'),
    //             new NumberSchema('distance_meters', 'Distance in meters'),
    //             new NumberSchema('duration_seconds', 'Duration in seconds'),
    //             new NumberSchema('rpe', 'Rate of perceived exertion'),
    //             new NumberSchema('custom_metric', 'Custom metric description'),
    //         ],
    //         requiredFields: ['index', 'weight_kg', 'reps']
    //     );
    // }

    // protected function exerciseSchema()
    // {
    //     return new ObjectSchema(
    //         name: 'exercise',
    //         description: 'Details of an exercise in a workout',
    //         properties: [
    //             new NumberSchema('index', 'Order of the exercise'),
    //             new StringSchema('title', 'Title of the exercise'),
    //             new StringSchema('notes', 'Notes for the exercise'),
    //             new NumberSchema('exercise_template_id', 'Template ID of the exercise'),
    //             new NumberSchema('superset_id', 'Superset ID, if applicable'),
    //             new ArraySchema(
    //                 name: 'sets',
    //                 description: 'List of sets for the exercise',
    //                 items: $this->setSchema()
    //             )
    //         ],
    //         requiredFields: ['index', 'title', 'sets']
    //     );
    // }

    // protected function workoutSchema()
    // {
    //     return new ObjectSchema(
    //         name: 'workout',
    //         description: 'A workout session with its exercises',
    //         properties: [
    //             new NumberSchema('id', 'Workout ID'),
    //             new StringSchema('title', 'Title of the workout'),
    //             new StringSchema('description', 'Description of the workout'),
    //             new StringSchema('start_time', 'Start time of the workout'),
    //             new StringSchema('end_time', 'End time of the workout'),
    //             new StringSchema('updated_at', 'Last update timestamp'),
    //             new StringSchema('created_at', 'Creation timestamp'),
    //             new ArraySchema(
    //                 name: 'exercises',
    //                 description: 'List of exercises in the workout',
    //                 items: $this->exerciseSchema()
    //             )
    //         ],
    //         requiredFields: ['title', 'start_time', 'exercises']
    //     );
    // }
}
