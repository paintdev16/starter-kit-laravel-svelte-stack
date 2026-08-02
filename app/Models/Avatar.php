<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avatar extends Model
{
    protected $fillable = ['user_id', 'path', 'source'];

    protected $appends = ['url'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExternal(): bool
    {
        return $this->source === 'url';
    }

    public function getUrlAttribute(): string
    {
        if ($this->isExternal()) {
            return $this->path;
        }

        return asset('storage/'.$this->path);
    }
}
