<?php

namespace App\Tools\Agents\Fitness;

use App\Tools\Embeddings\SimilaritySearchTool;
use App\Tools\Hevy\HevyGetWorkoutEventsTool;
use App\Tools\Serper\SerperSearchTool;
use Illuminate\Support\Collection;
use Prism\Prism\Prism;
use Prism\Prism\Tool;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Throwable;

class LiftSearchTool extends Tool
{
    protected Collection $messages;

    protected $researcher;

    protected $responder;

    public function __construct()
    {
        $this->as('LiftSearcher')
            ->for('Searching personal user history of completed lifting workouts.')
            ->withStringParameter('message', 'A specific lifting workout-related research request that the lifting agent should search and summarize. Best to search for individual (e.g., “What workout did I complete last Tuesday?") or range of (e.g., "Summary of workouts from this month") workouts.')
            ->using($this);

        $this->researcher = Prism::text()
            ->using(Provider::Gemini, 'gemini-2.0-flash')
            ->withSystemPrompt(view('prompts.agents.fitness.searchers.lift'))
            ->withTools([
                new HevyGetWorkoutEventsTool(),
            ])
            ->withMaxSteps(5);

        $this->responder = Prism::structured()
            ->using(Provider::Gemini, 'gemini-2.0-flash')
            ->withSystemPrompt('Structure the output of the research')
            ->withSchema($this->schema());
    }

    public function __invoke(string $message): string
    {
        try {

            // Perform research
            $research = $this->researcher
                ->withMessages([
                    new UserMessage($message),
                ])
                ->asText();

            // Structure the output
            $response = $this->responder
                ->withMessages([
                    new UserMessage($research->text),
                ])
                ->asStructured();

            // Render the output
            return view('prompts.outputs.fitness.searchers.lift', [
                'response' => $response->structured
            ])->render();

        } catch (PrismException $e) {
            dd('Generation failed:', ['error' => $e->getMessage()]);
        } catch (Throwable $e) {
            dd('Generic error:', ['error' => $e->getMessage()]);
        }
    }

    private function schema()
    {
        return new ArraySchema(
            name: 'workout_logs',
            description: 'A structured list of individual workout sessions.',
            items: new ObjectSchema(
              name: 'workout_entry',
              description: 'A single workout session entry.',
              properties: [
                new StringSchema(
                  name: 'workout_name',
                  description: 'The title or name of the workout.'
                ),
                new StringSchema(
                  name: 'duration',
                  description: 'Total duration of the workout session (e.g., 47m).'
                ),
                new ObjectSchema(
                  name: 'timestamp',
                  description: 'Time information about when the workout occurred.',
                  properties: [
                    new StringSchema(
                      name: 'date',
                      description: 'The date of the workout in YYYY-MM-DD format.'
                    ),
                    new StringSchema(
                      name: 'time',
                      description: 'The time of the workout in HH:MM (24-hour) format.'
                    ),
                  ],
                  requiredFields: ['date', 'time'],
                ),
                new ArraySchema(
                  name: 'exercises',
                  description: 'List of exercises included in the workout.',
                  items: new ObjectSchema(
                    name: 'exercise',
                    description: 'A single exercise with sets.',
                    properties: [
                      new StringSchema(
                        name: 'name',
                        description: 'Name of the exercise.'
                      ),
                      new ArraySchema(
                        name: 'sets',
                        description: 'List of sets performed.',
                        items: new ObjectSchema(
                          name: 'set',
                          description: 'A single set including weight and reps.',
                          properties: [
                            new NumberSchema(
                              name: 'weight',
                              description: 'Weight used in the set (lbs).'
                            ),
                            new NumberSchema(
                              name: 'reps',
                              description: 'Repetitions performed in the set.'
                            )
                          ],
                          requiredFields: ['weight', 'reps']
                        )
                      )
                    ],
                    requiredFields: ['name', 'sets']
                  )
                ),
                new StringSchema(
                  name: 'source',
                  description: 'The source of the workout log (e.g., StrongCSV).'
                ),
              ],
              requiredFields: ['workout_name', 'duration', 'timestamp', 'exercises', 'source']
            )
        );
    }
}