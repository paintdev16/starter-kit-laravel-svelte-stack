<?php

namespace App\Http\Controllers;

use App\Contracts\ServiceManager;
use App\Enums\ActivityAction;
use App\Models\Setting;
use App\Services\ActivityLoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Sleep;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class SystemsController extends Controller
{
    public function index(): Response
    {
        $this->authorizeSystemsAccess(request());

        $running = app(ServiceManager::class)->running();
        $this->reconcileSetting($running);

        return Inertia::render('systems/Index', [
            'general' => [
                'app_name' => config('app.name'),
                'env' => app()->environment(),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'db_connection' => config('database.default'),
                'cache_driver' => config('cache.default'),
                'queue_connection' => config('queue.default'),
                'session_driver' => config('session.driver'),
                'broadcast_driver' => config('broadcast.default'),
                'app_url' => config('app.url'),
            ],
            'realtime' => [
                'enabled' => $running,
                'manager' => config('services.manager', 'local'),
                'service' => config('services.realtime_service', 'reverb'),
            ],
        ]);
    }

    public function realtimeStatus(): JsonResponse
    {
        $this->authorizeSystemsAccess(request());

        $running = app(ServiceManager::class)->running();
        $this->reconcileSetting($running);

        return response()->json([
            'enabled' => $running,
            'running' => $running,
        ]);
    }

    public function toggleRealtime(Request $request): JsonResponse
    {
        $this->authorizeSystemsAccess($request);

        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $enabled = (bool) $data['enabled'];

        try {
            $manager = app(ServiceManager::class);

            if ($enabled) {
                $manager->start();
            } else {
                $manager->stop();
            }

            $running = $this->waitForRunning($manager, $enabled, attempts: 10, sleepMs: 500);

            $this->reconcileSetting($running);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'enabled' => $this->realtimeEnabled(),
                'running' => app(ServiceManager::class)->running(),
                'error' => $enabled
                    ? 'No se pudo iniciar el servicio '.config('services.realtime_service', 'reverb').'.'
                    : 'No se pudo detener el servicio '.config('services.realtime_service', 'reverb').'.',
            ], 422);
        }

        ActivityLoggerService::log(
            $request,
            ActivityAction::RealtimeToggled,
            'Realtime '.($running ? 'activado' : 'desactivado').' ('.config('services.manager', 'local').')',
        );

        $payload = [
            'enabled' => $running,
            'running' => $running,
        ];

        if ($running !== $enabled) {
            $payload['error'] = $enabled
                ? 'El servicio está tardando en arrancar. Revisa el estado en unos segundos.'
                : 'El servicio sigue en ejecución. Inténtalo de nuevo.';
        }

        return response()->json($payload);
    }

    protected function waitForRunning(ServiceManager $manager, bool $expected, int $attempts, int $sleepMs): bool
    {
        for ($i = 0; $i < $attempts; $i++) {
            if ($manager->running() === $expected) {
                return $expected;
            }

            Sleep::for($sleepMs)->milliseconds();
        }

        return $manager->running();
    }

    protected function realtimeEnabled(): bool
    {
        return Setting::get('realtime_enabled', 'true') === 'true';
    }

    protected function reconcileSetting(bool $running): void
    {
        if ($running !== $this->realtimeEnabled()) {
            Setting::set('realtime_enabled', $running ? 'true' : 'false');
        }
    }

    protected function authorizeSystemsAccess(Request $request): void
    {
        abort_unless($request->user()->hasRole('root'), 403, 'No tienes permiso para gestionar servicios.');
    }
}
