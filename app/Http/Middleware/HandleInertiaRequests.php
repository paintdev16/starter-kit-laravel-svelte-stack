<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $lastLogin = null;
        if ($request->user()) {
            $lastLogin = ActivityLog::where('user_id', $request->user()->id)
                ->where('action', 'auth.login')
                ->orderBy('created_at', 'desc')
                ->first();
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'lastLogin' => $lastLogin ? [
                'date' => $lastLogin->created_at,
                'browser' => $lastLogin->browser,
                'os' => $lastLogin->os,
                'ip_address' => $lastLogin->ip_address,
                'device_type' => $lastLogin->device_type,
            ] : null,
            'auth' => [
                'user' => $request->user()?->load('roles', 'permissions', 'avatars'),
                'roles' => $request->user()?->getRoleNames(),
                'permissions' => $request->user()?->getAllPermissions()->pluck('name'),
                'isLastRoot' => $request->user()?->isRoot() && User::role('root')->count() <= 1,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
