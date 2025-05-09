<?php

namespace App\Console\Commands;

use App\Tools\Embeddings\SimilaritySearchTool;
use App\Tools\Serper\SerperSearchTool;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Themes\Default\Concerns\DrawsBoxes;
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Throwable;

use function Laravel\Prompts\textarea;

class TravelCommand extends Command
{
    use Colors;
    use DrawsBoxes;

    protected $signature = 'travel';

    protected $description = 'Chat with the travel agent.';

    protected Collection $messages;

    protected $prism;

    public function __construct()
    {
        parent::__construct();
        $this->messages = collect();
        $this->prism = $this->prismFactory();
    }

    protected function prismFactory()
    {
        return Prism::text()
            ->using(Provider::Gemini, 'gemini-2.0-flash')
            ->withSystemPrompt(view('prompts.agents.travel.coordinator'))
            ->withTools([
                new SimilaritySearchTool([
                    'tags->category' => 'Travel',
                ]),
                new SerperSearchTool(),
            ])
            ->withMaxSteps(5);
    }

    protected function chat()
    {
        $message = textarea('Message');
        $this->messages->push(new UserMessage($message));

        try {
            $answer = $this->prism->withMessages($this->messages->toArray())->asStream();
        } catch (PrismException $e) {
            dd('Text generation failed:', ['error' => $e->getMessage()]);
        } catch (Throwable $e) {
            dd('Generic error:', ['error' => $e->getMessage()]);
        }

        $fullResponse = '';
        foreach ($answer as $chunk) {
            $fullResponse .= $chunk->text;
            // Check for tool calls
            if ($chunk->toolCalls) {
                foreach ($chunk->toolCalls as $call) {
                    $body = '';
                    foreach ($call->arguments() as $key => $value) {
                        $key = $key;
                        $body .= "$key: $value\n";
                    }
                    $this->box($call->name, wordwrap($body, 60), color: 'blue');
                }
            }
        }

        $this->messages->push(new AssistantMessage($fullResponse));

        $this->box('Response', wordwrap($fullResponse, 60), color: 'magenta');
    }

    public function handle(): void
    {
        while (true) {
            $this->chat();
        }
    }
}
