<?php

namespace App\Tools\Embeddings;

use App\Models\Chunk;
use Prism\Prism\Tool;

class SimilaritySearchTool extends Tool
{
    protected float $similarityThreshold = 0.3;
    protected int $maxResults = 5;
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->as('Similarity')
            ->for('Useful when you need to search for things that are only available within given documents. Determine that a document must be searched on your own. Confirmation is not required.')
            ->withStringParameter('q', 'Detailed search query. Best to search one topic at a time. Present in the form of a question. If not a question, convert to a question without prompting the user.')
            ->using($this);
    }

    public function __invoke(string $q): string
    {
        $query = Chunk::search($q);

        // Apply all filters
        foreach ($this->filters as $column => $value) {
            $query->where($column, $value);
        }

        $results = $query->get();

        $results = collect($results
            ->filter(function ($result) {
                return $result->embedding['neighbor_distance'] <= $this->similarityThreshold;
            })
            ->take($this->maxResults)
            ->map(function ($result) {
                return [
                    'index' => $result->index ?? null,
                    'content' => $result->content ?? null,
                    'title' => $result->document->name ?? null,
                    'source' => $result->document->source ?? null,
                    'meta' => $result->meta ?? null,
                    'tags' => $result->tags ?? null,
                    'neighbor_distance' => $result->embedding['neighbor_distance'] ?? null,
                ];
            }));

        return view('prompts.embeddings.similarity-search-tool', [
            'results' => $results
        ])->render();
    }
}
