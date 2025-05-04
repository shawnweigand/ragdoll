<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = ['name', 'source_id', 'type', 'parent_id', 'category', 'is_outdated'];

    public function chunks(): HasMany
    {
        return $this->hasMany(Chunk::class);
    }
}
