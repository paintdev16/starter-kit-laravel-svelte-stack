<?php

namespace App\Models;

use App\Enums\ActivityAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property-read User|null $user */
class ActivityLog extends Model
{
    use MassPrunable;

    /** @return Builder<ActivityLog> */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(90));
    }

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'user_agent',
        'ip_address',
        'device_type',
        'device_brand',
        'device_model',
        'os',
        'os_version',
        'browser',
        'browser_version',
    ];

    protected $casts = [
        'action' => ActivityAction::class,
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
