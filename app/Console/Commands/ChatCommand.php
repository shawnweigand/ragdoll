<?php

namespace App\Console\Commands;

use App\Tools\SearchTool;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Themes\Default\Concerns\DrawsBoxes;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Throwable;

use function Laravel\Prompts\textarea;

class ChatCommand extends Command
{
    use Colors;
    use DrawsBoxes;

    protected $signature = 'chat';

    protected $description = 'Chat using Prism';

    protected Collection $messages;

    public function __construct()
    {
        parent::__construct();
        $this->messages = collect();
    }

    protected function prismFactory()
    {
        return Prism::text()
            ->using(Provider::Gemini, 'gemini-2.0-flash')
            ->withSystemPrompt(view('prompts.nova'))
            ->withTools([
                new SearchTool(),
            ])
            ->withMaxSteps(5);
    }

    protected function chat($prism): void  {
        $message = textarea('Message');
        $this->messages->push(new UserMessage($message));

        try {
            $answer = $prism->withMessages($this->messages->toArray())->asStream();//asText();
        } catch (PrismException $e) {
            dd('Text generation failed:', ['error' => $e->getMessage()]);
        } catch (Throwable $e) {
            dd('Generic error:', ['error' => $e->getMessage()]);
        }

        $fullResponse = '';
        foreach ($answer as $chunk) {
            // Append each chunk to build the complete response
            $fullResponse .= $chunk->text;

            // Check for tool calls
            if ($chunk->toolCalls) {
                foreach ($chunk->toolCalls as $call) {
                    $body = '';
                    foreach ($call->arguments() as $key => $value) {
                        $key = ucwords($key);
                        $body .= "$key: $value\n";
                    }
                    $this->box(ucwords($call->name), wordwrap($body, 60), color: 'blue');
                }
            }

            // // Check for tool results
            // if ($chunk->toolResults) {
            //     foreach ($chunk->toolResults as $result) {
            //         $this->box('Tool', wordwrap($result->result, 60), color: 'blue');
            //     }
            // }
        }

        $this->messages->push(new AssistantMessage($fullResponse));

        $this->box('Response', wordwrap($fullResponse, 60), color: 'magenta');
    }

    public function handle(): void
    {
        $prism = $this->prismFactory();

        while (true) {
            $this->chat($prism);
        }
    }
}
