<?php

namespace App\Tools\Trello;

use App\Services\TrelloService;
use Prism\Prism\Tool;

class TrelloSearchTool extends Tool
{
    protected TrelloService $trello;

    public function __construct()
    {
        $this->trello = new TrelloService();
        $this->as('trello')
            ->for('useful when you need to search for cards on a trello board')
            ->withStringParameter('query', 'Detailed search query for trello cards. Best to search one word or phrase representing the trello card at a time. The query must appear in the trello card.')
            ->using($this);
    }

    public function __invoke(string $query): string
    {
        $response = $this->trello->search($query);

        $results = collect($response['cards']);

        $modifiedResults = $results->map(function ($result) {
            return [
                'id' => $result['shortLink'],
                'name' => $result['name'],
                'desc' => $result['desc'],
                'labels' => collect($result['labels'])->map(function ($label) {
                    return [
                        'name' => $label['name'],
                    ];
                }),
                'list' => $result['idList'],
                'start' => $result['start'],
                'due' => $result['due'],
                'url' => $result['shortUrl'],
            ];
        })->all();

        return view('prompts.trello.trello-search-tool-results', [
            'results' => $modifiedResults
        ])->render();
    }
}