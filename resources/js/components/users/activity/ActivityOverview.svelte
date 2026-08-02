<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import { ClipboardList, Eye, RotateCw } from '@lucide/svelte';
    import { Avatar, AvatarFallback } from '@/components/ui/avatar';
    import { Button } from '@/components/ui/button';
    import Skeleton from '@/components/ui/skeleton/skeleton.svelte';
    import CurrentDeviceCard from './CurrentDeviceCard.svelte';
    import type { DeviceInfo } from './types';

    interface ActivityGroup {
        id?: number;
        user_id: number;
        user_name: string;
        count: number;
        last_action: string;
        last_date: string;
        last_login?: string | null;
        is_online?: boolean;
    }

    let {
        active = false,
    }: {
        active?: boolean;
    } = $props();

    let currentDevice = $state<DeviceInfo | null>(null);
    let deviceLoading = $state(false);
    let activityGroups = $state<ActivityGroup[]>([]);
    let activityLoading = $state(false);
    let hasLoaded = $state(false);

    function getInitials(name: string): string {
        return name
            .split(' ')
            .map((n) => n[0])
            .join('')
            .toUpperCase()
            .slice(0, 2);
    }

    function formatDate(dateStr: string) {
        return new Date(dateStr).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    async function fetchCurrentDevice() {
        deviceLoading = true;

        try {
            const res = await fetch('/activity/current-device', {
                credentials: 'include',
            });

            if (!res.ok) {
                throw new Error(`${res.status}`);
            }

            currentDevice = await res.json();
        } catch {
            currentDevice = null;
        } finally {
            deviceLoading = false;
        }
    }

    async function fetchActivityGroups() {
        activityLoading = true;

        try {
            const res = await fetch('/activity/grouped', {
                credentials: 'include',
            });

            if (!res.ok) {
                throw new Error(`${res.status}`);
            }

            activityGroups = await res.json();
        } catch {
            activityGroups = [];
        } finally {
            activityLoading = false;
        }
    }

    $effect(() => {
        if (active && !hasLoaded) {
            hasLoaded = true;
            fetchCurrentDevice();
            fetchActivityGroups();
        }
    });
</script>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-1">
        <CurrentDeviceCard device={currentDevice} loading={deviceLoading} />
    </div>

    <div class="lg:col-span-2">
        <div class="rounded-xl border bg-card">
            <div class="flex items-center justify-between border-b px-5 py-3.5">
                <h3 class="text-sm font-semibold text-foreground">
                    Actividad por usuario
                </h3>
                {#if !activityLoading}
                    <button
                        onclick={() => fetchActivityGroups()}
                        class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent"
                        aria-label="Recargar"
                    >
                        <RotateCw class="size-3.5" />
                    </button>
                {/if}
            </div>

            {#if activityLoading && activityGroups.length === 0}
                <div class="space-y-3 p-5">
                    {#each [1, 2, 3, 4] as _, i (i)}
                        <div class="flex items-center gap-3">
                            <Skeleton class="size-10 shrink-0 rounded-full" />
                            <div class="min-w-0 flex-1 space-y-1.5">
                                <Skeleton class="h-4 w-36 rounded-md" />
                                <Skeleton class="h-3 w-48 rounded-md" />
                            </div>
                        </div>
                    {/each}
                </div>
            {:else if activityGroups.length === 0}
                <div
                    class="flex flex-col items-center justify-center gap-3 py-16"
                >
                    <ClipboardList class="size-10 text-muted-foreground/30" />
                    <p class="text-sm text-muted-foreground">
                        Aún no hay actividad registrada
                    </p>
                </div>
            {:else}
                <div class="divide-y">
                    {#each activityGroups as group, i (group.id ?? group.user_id ?? i)}
                        <div
                            class="flex items-center gap-3 px-5 py-4 sm:gap-4 {group.is_online
                                ? 'border-l-4 border-l-card-success-border bg-card-success/20'
                                : 'border-l-4 border-l-card-warning-border bg-card-warning/20'}"
                        >
                            <div class="relative shrink-0">
                                <Avatar class="size-10">
                                    <AvatarFallback
                                        class="text-xs font-semibold"
                                        >{getInitials(
                                            group.user_name,
                                        )}</AvatarFallback
                                    >
                                </Avatar>
                                {#if group.is_online}
                                    <span
                                        class="absolute -right-0.5 -top-0.5 size-3 rounded-full border-2 border-background bg-success"
                                        title="Conectado"
                                    ></span>
                                {:else}
                                    <span
                                        class="absolute -right-0.5 -top-0.5 size-3 rounded-full border-2 border-background bg-muted-foreground/40"
                                        title="Desconectado"
                                    ></span>
                                {/if}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p
                                        class="text-sm font-semibold text-foreground"
                                    >
                                        {group.user_name}
                                    </p>
                                    <span
                                        class="rounded-full bg-accent px-2 py-0.5 text-[10px] font-medium text-muted-foreground"
                                        >{group.count}
                                        {group.count === 1
                                            ? 'acción'
                                            : 'acciones'}</span
                                    >
                                    {#if group.is_online}
                                        <span
                                            class="text-[10px] font-medium text-success"
                                            >En línea</span
                                        >
                                    {/if}
                                </div>
                                <p
                                    class="mt-0.5 truncate text-xs text-muted-foreground"
                                >
                                    {group.last_action}
                                </p>
                                <p class="text-xs text-muted-foreground/60">
                                    {formatDate(group.last_date)}
                                </p>
                                {#if group.last_login}
                                    <p class="text-xs text-primary">
                                        Último login: {formatDate(
                                            group.last_login,
                                        )}
                                    </p>
                                {/if}
                            </div>
                            <Link
                                href={`/users/activity?user=${group.user_id}`}
                                class="shrink-0"
                            >
                                <Button variant="outline" size="sm">
                                    <Eye class="size-3.5 sm:mr-1.5" />
                                    <span class="hidden sm:inline"
                                        >Detalles</span
                                    >
                                </Button>
                            </Link>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>
    </div>
</div>
