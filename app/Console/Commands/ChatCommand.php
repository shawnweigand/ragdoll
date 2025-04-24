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
            ->withTools([
                new SearchTool(),
            ])
            ->withSystemPrompt(view('prompts.nova'))
            ->withMaxSteps(5)
            ->using(Provider::Gemini, 'gemini-2.0-flash');
    }

    protected function chat($prism): void  {
        $message = textarea('Message');
        $this->messages->push(new UserMessage($message));

        try {
            $answer = $prism->withMessages($this->messages->toArray())->asText();
        } catch (PrismException $e) {
            dd('Text generation failed:', ['error' => $e->getMessage()]);
        } catch (Throwable $e) {
            dd('Generic error:', ['error' => $e->getMessage()]);
        }

        $this->messages->push(new AssistantMessage($answer->text));

        $this->box('Response', wordwrap($answer->text, 60), color: 'magenta');
    }

    public function handle(): void
    {
        $prism = $this->prismFactory();

        while (true) {
            $this->chat($prism);
        }
    }
}
