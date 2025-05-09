<?php

namespace App\Tools\Agents\Travel;

use App\Tools\Serper\SerperSearchTool;
use Illuminate\Support\Collection;
use Prism\Prism\Prism;
use Prism\Prism\Tool;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Throwable;

class ResearchAgentTool extends Tool
{
    protected Collection $messages;

    protected $prism;

    protected $schema;

    public function __construct()
    {
        $this->as('Research')
            ->for('calling a travel coordinator agent to perform live, structured travel research—including destinations, activities, entry requirements, transportation options, and flights with prices and booking links.')
            ->withStringParameter('message', 'A specific travel-related request or topic (e.g., “Things to do in Tokyo in November” or “Flights from NYC to Lisbon in October”) that the researcher agent should investigate and summarize.. Best to search one topic at a time.')
            ->using($this);
        $this->messages = collect();
        $this->prism = $this->prismFactory();
    }

    protected function prismFactory()
    {
        return Prism::text()
            ->using(Provider::Gemini, 'gemini-2.0-flash')
            ->withSystemPrompt(view('prompts.agents.travel.researcher'))
            ->withTools([
                new SerperSearchTool(),
            ])
            ->withMaxSteps(5);
    }

    protected function chat(string $message)
    {
        $this->messages->push(new UserMessage($message));

        try {
            $answer = $this->prism->withMessages($this->messages->toArray())->asText();
        } catch (PrismException $e) {
            dd('Text generation failed:', ['error' => $e->getMessage()]);
        } catch (Throwable $e) {
            dd('Generic error:', ['error' => $e->getMessage()]);
        }

        $this->messages->push(new AssistantMessage($answer->text));

    }

    public function __invoke(string $message): string
    {
        $completed = false;

        while (!$completed) {
            $this->chat($message);
            if ($this->messages->count() >= 10) {
                $completed = true;
            }
        }

        return view('prompts.outputs.travel.researcher', [
            'messages' => $this->messages
        ])->render();
    }
}