<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Chat extends Model
{
    protected $fillable = ['source_id', 'type' ];//'user_id'];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function prismMessages(): Collection
    {
        return $this->messages
            ->sortBy('created_at')
            ->map->prismMessage()
            ->collect();
    }
}
