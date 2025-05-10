<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use BenBjurstrom\PgvectorScout\Models\Concerns\HasEmbeddings;
use Laravel\Scout\Searchable;

class Chunk extends Model
{
    use HasEmbeddings, Searchable;

    protected $fillable = ['document_id', 'content', 'index', 'meta', 'tags'];

    protected $casts = [
        'meta' => 'array',
        'tags' => 'array',
    ];

    public function searchableAs(): string
    {
        return 'gemini';
    }

    public function toSearchableArray(): array
    {
        return [
            'title' => $this->document->name,
            'meta' => $this->meta,
            'tags' => $this->tags,
            'content' => $this->content,
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
