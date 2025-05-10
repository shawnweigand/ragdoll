<?php

namespace App\Tools\Agents\Travel;

use App\Tools\Serper\SerperSearchTool;
use Illuminate\Support\Collection;
use Prism\Prism\Prism;
use Prism\Prism\Tool;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Throwable;

class FlightResearcherTool extends Tool
{
    protected Collection $messages;

    protected $researcher;

    protected $responder;

    public function __construct()
    {
        $this->as('FlightResearcher')
            ->for('researching travel flight options between locations.')
            ->withStringParameter('message', 'A specific flight-related research request (e.g., “Flights from NYC to Lisbon in October”) that the transportation agent should investigate and summarize. Best to search one trasportation at a time.')
            ->using($this);

        $this->researcher = Prism::text()
            ->using(Provider::Gemini, 'gemini-2.0-flash')
            ->withSystemPrompt(view('prompts.agents.travel.researchers.flight'))
            ->withTools([
                new SerperSearchTool(),
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
            return view('prompts.outputs.travel.researchers.flight', [
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
            name: 'travel_research',
            description: 'A structured summary of travel research findings.',
            items: new ObjectSchema(
                name: 'finding',
                description: 'A single finding related to the travel request.',
                properties: [
                    new StringSchema(
                        name: 'title',
                        description: 'The title of the finding.'
                    ),
                    new StringSchema(
                        name: 'summary',
                        description: 'A brief summary of the finding.'
                    ),
                    new StringSchema(
                        name: 'details',
                        description: 'Detailed information about the finding.'
                    ),
                    new StringSchema(
                        name: 'link',
                        description: 'A link to more information about the finding.'
                    ),
                    new StringSchema(
                        name: 'price',
                        description: 'The price associated with the finding.'
                    ),
                    new StringSchema(
                        name: 'duration',
                        description: 'The duration of the travel.'
                    ),
                    new StringSchema(
                        name: 'provider',
                        description: 'The provider of the travel service.'
                    ),
                    new ObjectSchema(
                        name: 'departure',
                        description: 'The departure information.',
                        properties: [
                            new StringSchema(
                                name: 'location',
                                description: 'The departure location.'
                            ),
                            new StringSchema(
                                name: 'date',
                                description: 'The departure date.'
                            ),
                            new StringSchema(
                                name: 'time',
                                description: 'The departure time.'
                            ),
                        ],
                        requiredFields: ['location', 'date', 'time'],
                    ),
                    new ObjectSchema(
                        name: 'arrival',
                        description: 'The arrival information.',
                        properties: [
                            new StringSchema(
                                name: 'location',
                                description: 'The arrival location.'
                            ),
                            new StringSchema(
                                name: 'date',
                                description: 'The arrival date.'
                            ),
                            new StringSchema(
                                name: 'time',
                                description: 'The arrival time.'
                            ),
                        ],
                        requiredFields: ['location', 'date', 'time'],
                    ),
                ],
                requiredFields: ['title', 'summary', 'details', 'link', 'price', 'duration', 'provider' ,'departure', 'arrival'],
            )
        );
    }
}