<script module lang="ts">
    import { index } from '@/routes/systems';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Sistema',
                href: index(),
            },
        ],
    };
</script>

<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import {
        Archive,
        CalendarClock,
        Check,
        Database,
        Globe,
        Inbox,
        Info,
        MoreHorizontal,
        ScrollText,
        Zap,
    } from '@lucide/svelte';
    import type { Component } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Backups from '@/components/systems/Backups.svelte';
    import Cache from '@/components/systems/Cache.svelte';
    import General from '@/components/systems/General.svelte';
    import Logs from '@/components/systems/Logs.svelte';
    import OAuth from '@/components/systems/OAuth.svelte';
    import Queue from '@/components/systems/Queue.svelte';
    import Realtime from '@/components/systems/Realtime.svelte';
    import Scheduler from '@/components/systems/Scheduler.svelte';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import {
        Tabs,
        TabsContent,
        TabsList,
        TabsTrigger,
    } from '@/components/ui/tabs';

    interface RealtimeState {
        enabled: boolean;
        manager: string;
        service: string;
    }

    interface GeneralInfo {
        app_name: string;
        env: string;
        php_version: string;
        laravel_version: string;
        db_connection: string;
        cache_driver: string;
        queue_connection: string;
        session_driver: string;
        broadcast_driver: string;
        app_url: string;
    }

    let {
        realtime,
        general,
    }: { realtime: RealtimeState; general: GeneralInfo } = $props();

    type TabId =
        | 'general'
        | 'oauth'
        | 'realtime'
        | 'queue'
        | 'scheduler'
        | 'cache'
        | 'logs'
        | 'backups';

    interface TabDef {
        id: TabId;
        label: string;
        icon: Component;
    }

    const tabMeta: Record<TabId, { title: string; description: string }> = {
        general: {
            title: 'Sistema',
            description: 'Administración general de la aplicación.',
        },
        oauth: {
            title: 'Proveedores OAuth',
            description: 'Gestiona los proveedores de inicio de sesión social.',
        },
        realtime: {
            title: 'Realtime',
            description:
                'Gestiona el servicio de notificaciones en tiempo real.',
        },
        queue: {
            title: 'Cola de trabajos',
            description: 'Trabajos en cola y fallidos del sistema.',
        },
        scheduler: {
            title: 'Tareas programadas',
            description: 'Tareas agendadas de la aplicación.',
        },
        cache: {
            title: 'Cache',
            description: 'Estado y limpieza del cache.',
        },
        logs: {
            title: 'Logs',
            description: 'Registros de la aplicación.',
        },
        backups: {
            title: 'Copias de seguridad',
            description: 'Respaldos de la aplicación.',
        },
    };

    const tabs: TabDef[] = [
        { id: 'general', label: 'General', icon: Info },
        { id: 'oauth', label: 'OAuth', icon: Globe },
        { id: 'realtime', label: 'Realtime', icon: Zap },
        { id: 'queue', label: 'Cola', icon: Inbox },
        { id: 'scheduler', label: 'Scheduler', icon: CalendarClock },
        { id: 'cache', label: 'Cache', icon: Database },
        { id: 'logs', label: 'Logs', icon: ScrollText },
        { id: 'backups', label: 'Backups', icon: Archive },
    ];

    const MOBILE_PRIMARY_COUNT = 2;
    let isMobile = $state(false);

    $effect(() => {
        if (typeof window === 'undefined') {
            return;
        }

        const mq = window.matchMedia('(max-width: 767px)');
        const update = () => {
            isMobile = mq.matches;
        };
        update();
        mq.addEventListener('change', update);

        return () => mq.removeEventListener('change', update);
    });

    function isTabAllowed(id: string): id is TabId {
        return tabs.some((t) => t.id === id);
    }

    function readTabFromUrl(): TabId {
        try {
            const raw = page.url.includes('?')
                ? page.url.slice(page.url.indexOf('?') + 1)
                : '';
            const param = new URLSearchParams(raw).get('tab');

            if (param && isTabAllowed(param)) {
                return param;
            }
        } catch {
            // ignore
        }

        return 'general';
    }

    let tab = $state<TabId>(readTabFromUrl());

    const headerTitle = $derived(tabMeta[tab]?.title ?? 'Sistema');
    const headerDescription = $derived(tabMeta[tab]?.description ?? '');

    const barTabs = $derived.by((): TabDef[] => {
        if (!isMobile || tabs.length <= MOBILE_PRIMARY_COUNT) {
            return tabs;
        }

        const primary = tabs.slice(0, MOBILE_PRIMARY_COUNT);

        if (primary.some((t) => t.id === tab)) {
            return primary;
        }

        const active = tabs.find((t) => t.id === tab);

        if (!active) {
            return primary;
        }

        return [...primary.slice(0, MOBILE_PRIMARY_COUNT - 1), active];
    });

    const overflowTabs = $derived.by((): TabDef[] => {
        if (!isMobile || tabs.length <= MOBILE_PRIMARY_COUNT) {
            return [];
        }

        const barIds = new Set(barTabs.map((t) => t.id));

        return tabs.filter((t) => !barIds.has(t.id));
    });

    const overflowHasActive = $derived(overflowTabs.some((t) => t.id === tab));

    function syncTabToUrl(next: TabId) {
        if (typeof window === 'undefined') {
            return;
        }

        const url = new URL(window.location.href);

        if (next === 'general') {
            url.searchParams.delete('tab');
        } else {
            url.searchParams.set('tab', next);
        }

        window.history.replaceState(window.history.state, '', url);
    }

    function setTab(next: string) {
        if (!isTabAllowed(next)) {
            return;
        }

        tab = next;
        syncTabToUrl(tab);
    }
</script>

<AppHead title="Sistema" />

<div
    class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 pb-10 md:p-6 lg:p-8"
>
    <div>
        <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
            {headerTitle}
        </h1>
        <p class="mt-1 text-sm text-muted-foreground">{headerDescription}</p>
    </div>

    <Tabs bind:value={tab}>
        <TabsList
            variant="line"
            class="h-auto w-full max-w-full flex-wrap justify-start gap-1"
        >
            {#each barTabs as t (t.id)}
                {@const Icon = t.icon}
                <TabsTrigger value={t.id} class="gap-2">
                    <Icon class="size-4" />
                    <span class="truncate">{t.label}</span>
                </TabsTrigger>
            {/each}

            {#if overflowTabs.length > 0}
                <DropdownMenu>
                    <DropdownMenuTrigger>
                        {#snippet child({ props })}
                            <button
                                type="button"
                                class="relative inline-flex h-[calc(100%-1px)] items-center justify-center gap-1.5 rounded-md px-1.5 py-0.5 text-sm font-medium transition-all hover:text-foreground {overflowHasActive
                                    ? 'text-foreground'
                                    : 'text-foreground/60'}"
                                aria-label="Más pestañas"
                                {...props}
                            >
                                <MoreHorizontal class="size-4" />
                                Más
                            </button>
                        {/snippet}
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="min-w-44">
                        {#each overflowTabs as t (t.id)}
                            {@const Icon = t.icon}
                            <DropdownMenuItem
                                class="gap-2"
                                onclick={() => setTab(t.id)}
                            >
                                <Icon class="size-4" />
                                <span class="flex-1">{t.label}</span>
                                {#if tab === t.id}
                                    <Check class="size-4 text-primary" />
                                {/if}
                            </DropdownMenuItem>
                        {/each}
                    </DropdownMenuContent>
                </DropdownMenu>
            {/if}
        </TabsList>

        <TabsContent value="general">
            <General {general} />
        </TabsContent>
        <TabsContent value="oauth">
            <OAuth active={tab === 'oauth'} />
        </TabsContent>
        <TabsContent value="realtime">
            <Realtime {realtime} active={tab === 'realtime'} />
        </TabsContent>
        <TabsContent value="queue">
            <Queue />
        </TabsContent>
        <TabsContent value="scheduler">
            <Scheduler />
        </TabsContent>
        <TabsContent value="cache">
            <Cache />
        </TabsContent>
        <TabsContent value="logs">
            <Logs />
        </TabsContent>
        <TabsContent value="backups">
            <Backups />
        </TabsContent>
    </Tabs>
</div>
