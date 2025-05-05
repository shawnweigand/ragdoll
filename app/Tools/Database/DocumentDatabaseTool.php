<?php

namespace App\Tools\Database;

use App\Models\Document;
use Prism\Prism\Tool;

class DocumentDatabaseTool extends Tool
{

    public function __construct()
    {
        $this->as('Database')
            ->for('To retrieve the ID about the documents in the database. This will return all the documents to feed a single document ID into another tool to search with.')
            ->using($this);
    }

    public function __invoke()
    {
        $docs = Document::all();

        return view('prompts.database.document-database-tool', [
            'docs' => $docs
        ])->render();
    }
}