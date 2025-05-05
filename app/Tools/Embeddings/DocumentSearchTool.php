<?php

namespace App\Tools\Embeddings;

use App\Models\Chunk;
use Prism\Prism\Tool;

class DocumentSearchTool extends Tool
{

    public function __construct()
    {
        $this->as('Document')
            ->for('Use when trying to summarize a single specifically specified documents that are personal to the user that are not in the public domain. Determine that the document must be found on your own and determine what to query based on the user context. Confirmation and clarification is not required.')
            ->withStringParameter('document_id', 'The ID of the document that is specifically mentioned. Best to search one document at a time. If not provided, use another tool to find it yourself. DO NOT ASK FOR IT.')
            ->withStringParameter('query', 'Detailed search query to be used on the document. Best to search one topic at a time. Present in the form of a question. If not a question, convert to a question without prompting the user.')
            ->using($this);
    }

    public function __invoke(int $document_id, string $query): string
    {
        $results = Chunk::search($query)
            ->where('document_id', $document_id)
            ->get();

        $results = collect($results
            ->map(function ($result) {
                return [
                    'index' => $result->index ?? null,
                    'content' => $result->content ?? null,
                    'title' => $result->document->name ?? null,
                    'source' => $result->document->source ?? null,
                    'neighbor_distance' => $result->embedding['neighbor_distance'] ?? null,
                ];
            }));

        return view('prompts.embeddings.similarity-search-tool', [
            'results' => $results
        ])->render();
    }
}