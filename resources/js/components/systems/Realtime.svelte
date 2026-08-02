<script lang="ts">
    import { Power, PowerOff, Radio, Server, Zap } from '@lucide/svelte';
    import { onDestroy } from 'svelte';
    import { toast } from 'svelte-sonner';
    import { Switch } from '@/components/ui/switch';

    interface RealtimeState {
        enabled: boolean;
        manager: string;
        service: string;
    }

    let { realtime, active }: { realtime: RealtimeState; active: boolean } =
        $props();

    // svelte-ignore state_referenced_locally
    let enabled = $state(realtime.enabled);
    let running = $state(false);
    let statusLoading = $state(false);
    let toggling = $state(false);
    let settling = $state(false);
    let desired = $state(false);

    let pollHandle: ReturnType<typeof setInterval> | null = null;
    let pollDeadline = 0;

    function csrfToken(): string {
        return (
            document
                .querySelector('meta[name=csrf-token]')
                ?.getAttribute('content') ?? ''
        );
    }

    async function loadStatus() {
        statusLoading = true;

        try {
            const res = await fetch('/systems/realtime/status', {
                headers: { Accept: 'application/json' },
            });
            const data = await res.json();
            enabled = data.enabled;
            running = data.running;
        } catch {
            running = false;
        } finally {
            statusLoading = false;
        }
    }

    function stopPolling() {
        if (pollHandle !== null) {
            clearInterval(pollHandle);
            pollHandle = null;
        }

        settling = false;
    }

    function startPolling(expectedRunning: boolean) {
        stopPolling();

        settling = true;
        pollDeadline = Date.now() + 10_000;

        pollHandle = setInterval(async () => {
            if (Date.now() > pollDeadline) {
                stopPolling();

                return;
            }

            const previous = running;
            await loadStatus();

            if (running === expectedRunning) {
                stopPolling();
            } else if (running !== previous) {
                loadStatus();
            }
        }, 1500);
    }

    async function toggleRealtime(next: boolean) {
        if (toggling) {
            return;
        }

        toggling = true;
        desired = next;

        try {
            const res = await fetch('/systems/realtime/toggle', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({ enabled: next }),
            });
            const data = await res.json();

            if (!res.ok) {
                toast.error(
                    data.error ?? 'No se pudo cambiar el estado del servicio.',
                );

                return;
            }

            enabled = data.enabled;
            running = data.running;

            if (data.error) {
                toast.error(data.error);
            } else {
                toast.success(
                    data.enabled
                        ? 'Realtime activado.'
                        : 'Realtime desactivado.',
                );
            }

            if (next && !data.running) {
                startPolling(true);
            } else if (!next && data.running) {
                startPolling(false);
            }
        } catch {
            toast.error('Error de conexión al cambiar el estado del servicio.');
        } finally {
            toggling = false;
        }
    }

    const managerLabel: Record<string, string> = {
        local: 'Local (detached)',
        systemd: 'systemd',
        supervisor: 'Supervisor',
    };

    $effect(() => {
        if (active) {
            loadStatus();
        } else {
            stopPolling();
        }
    });

    onDestroy(stopPolling);
</script>

<div
    class="flex flex-col gap-4 rounded-xl border border-card-border bg-card p-5"
>
    <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
            <span
                class="inline-flex size-10 shrink-0 items-center justify-center rounded-lg bg-card-primary/40 text-primary"
            >
                <Zap class="size-5" />
            </span>
            <div>
                <h2 class="text-base font-semibold text-foreground">
                    Notificaciones en tiempo real
                </h2>
                <p class="mt-0.5 text-sm text-muted-foreground">
                    Envía avisos a los administradores cuando se crea un usuario
                    nuevo.
                </p>
            </div>
        </div>
        <Switch
            checked={toggling ? desired : enabled}
            disabled={toggling}
            onCheckedChange={(next) => toggleRealtime(next)}
            size="default"
            variant="primary"
            checkedIcon={Power}
            uncheckedIcon={PowerOff}
        />
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <span
            class="inline-flex items-center gap-1.5 rounded-md border px-2 py-1 text-xs font-medium {running
                ? 'border-card-success-border bg-card-success/40 text-success-foreground-soft'
                : 'border-card-warning-border bg-card-warning/40 text-warning-foreground-soft'}"
        >
            <span
                class="size-1.5 rounded-full {running
                    ? 'bg-success'
                    : 'bg-warning'}"
            ></span>
            {toggling
                ? desired
                    ? 'Iniciando…'
                    : 'Deteniendo…'
                : settling
                  ? desired
                      ? 'Iniciando…'
                      : 'Deteniendo…'
                  : statusLoading
                    ? 'Comprobando…'
                    : running
                      ? 'En ejecución'
                      : 'Detenido'}
        </span>

        <span
            class="inline-flex items-center gap-1.5 rounded-md border border-card-info-border bg-card-info/40 px-2 py-1 text-xs font-medium text-info-foreground-soft"
        >
            <Server class="size-3" />
            {realtime.service}
        </span>

        <span
            class="inline-flex items-center gap-1.5 rounded-md border bg-secondary px-2 py-1 text-xs font-medium text-secondary-foreground-soft"
        >
            <Radio class="size-3" />
            {managerLabel[realtime.manager] ?? realtime.manager}
        </span>
    </div>

    {#if !enabled}
        <p class="text-xs text-muted-foreground/80">
            Con el servicio desactivado no se envían ni se reciben
            notificaciones en tiempo real.
        </p>
    {/if}
</div>
