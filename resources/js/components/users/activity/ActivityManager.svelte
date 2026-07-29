<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Skeleton from '@/components/ui/skeleton/skeleton.svelte';
    import { onMount } from 'svelte';
    import { ArrowLeft, Globe } from '@lucide/svelte';
    import CurrentDeviceCard from './CurrentDeviceCard.svelte';
    import ActivityLogItem from './ActivityLogItem.svelte';
    import ActivityPagination from './ActivityPagination.svelte';

    interface LogItem {
        id: number;
        user_id: number;
        action: string;
        description: string | null;
        user_agent: string | null;
        ip_address: string | null;
        device_type: string | null;
        device_brand: string | null;
        device_model: string | null;
        os: string | null;
        os_version: string | null;
        browser: string | null;
        browser_version: string | null;
        created_at: string;
        updated_at: string;
        user: { id: number; name: string } | null;
    }

    interface PaginatedData<T> {
        data: T[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: { url: string | null; label: string; active: boolean }[];
    }

    interface DeviceInfo {
        browser: string | null;
        browser_version: string | null;
        os: string | null;
        os_version: string | null;
        device_type: string | null;
        device_brand: string | null;
        device_model: string | null;
        ip_address: string | null;
    }

    let {
        active = false,
        filterUserId = null,
    }: {
        active?: boolean;
        filterUserId?: number | null;
    } = $props();

    let logs = $state<PaginatedData<LogItem> | null>(null);
    let logsLoading = $state(false);

    let currentDevice = $state<DeviceInfo | null>(null);
    let deviceLoading = $state(true);

    async function fetchLogs(page = 1) {
        logsLoading = true;
        try {
            const userParam = filterUserId ? `&user=${filterUserId}` : '';
            const res = await fetch(`/activity/logs?page=${page}${userParam}`, {
                credentials: 'include',
            });
            if (!res.ok) throw new Error(`${res.status}`);
            logs = await res.json();
        } catch (e) {
            console.error('fetchLogs:', e);
            logs = null;
        } finally {
            logsLoading = false;
        }
    }

    async function fetchCurrentDevice() {
        deviceLoading = true;
        try {
            const res = await fetch('/activity/current-device', {
                credentials: 'include',
            });
            if (!res.ok) throw new Error(`${res.status}`);
            currentDevice = await res.json();
        } catch {
            currentDevice = null;
        } finally {
            deviceLoading = false;
        }
    }

    onMount(() => {
        fetchCurrentDevice();
    });

    $effect(() => {
        if (active && !logs && !logsLoading) {
            fetchLogs();
        }
    });
</script>

<div
    class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 pb-10 md:p-6 lg:p-8"
>
    <div class="flex items-center gap-3">
        <Link
            href="/users"
            class="inline-flex size-8 items-center justify-center rounded-md border border-input text-foreground transition-colors hover:bg-accent"
            aria-label="Volver"
        >
            <ArrowLeft class="size-4" />
        </Link>
        <div>
            <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                {filterUserId != null
                    ? `Actividad del usuario`
                    : 'Actividad del sistema'}
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                {logs?.total ?? 0}
                {logs?.total === 1 ? 'registro' : 'registros'} de actividad
            </p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <CurrentDeviceCard device={currentDevice} loading={deviceLoading} />
        </div>

        <div class="lg:col-span-2">
            <div class="rounded-xl border bg-card">
                {#if logsLoading && !logs}
                    <div class="space-y-3 p-5">
                        {#each [1, 2, 3, 4] as _}
                            <div class="flex items-center gap-3">
                                <Skeleton
                                    class="size-10 shrink-0 rounded-full"
                                />
                                <div class="min-w-0 flex-1 space-y-1.5">
                                    <Skeleton class="h-4 w-36 rounded-md" />
                                    <Skeleton class="h-3 w-48 rounded-md" />
                                </div>
                            </div>
                        {/each}
                    </div>
                {:else if !logs || logs.data.length === 0}
                    <div
                        class="flex flex-col items-center justify-center gap-4 py-20"
                    >
                        <Globe class="size-14 text-muted-foreground/30" />
                        <p class="text-base font-medium text-muted-foreground">
                            No hay actividad registrada
                        </p>
                    </div>
                {:else}
                    <div class="divide-y">
                        {#each logs.data as log (log.id)}
                            <ActivityLogItem {log} />
                        {/each}
                    </div>

                    {#if logs.last_page > 1}
                        <ActivityPagination links={logs.links} />
                    {/if}
                {/if}
            </div>
        </div>
    </div>
</div>
