<?php

namespace App\Tools\Serper;

use Illuminate\Support\Facades\Http;
use Prism\Prism\Tool;

class SerperSearchTool extends Tool
{
    public function __construct()
    {
        $this->as('Search')
            ->for('useful when you need to search for current events')
            ->withStringParameter('query', 'Detailed search query. Best to search one topic at a time.')
            ->using($this);
    }

    public function __invoke(string $query): string
    {
        $response = Http::get('https://serpapi.com/search', [
            'engine' => 'google',
            'q' => $query,
            'google_domain' => 'google.com',
            'gl' => 'us',
            'hl' => 'en',
            'api_key' => config('services.serpapi.key')
        ]);

        $results = collect($response->json('organic_results'));

        $results->map(function ($result) {
            return [
                'title' => $result['title'],
                'link' => $result['link'],
                'snippet' => $result['snippet'],
            ];
        })->take(4);

        return view('prompts.serper.serper-search-tool-results', [
            'results' => $results
        ])->render();
    }
}