<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OauthProvider extends Model
{
    protected $fillable = [
        'provider',
        'client_id',
        'client_secret',
        'redirect_uri',
        'enabled',
        'show_on_login',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'client_id' => 'encrypted',
            'client_secret' => 'encrypted',
            'enabled' => 'boolean',
            'show_on_login' => 'boolean',
            'sort' => 'integer',
        ];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder<self> $query
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }
}
