<script lang="ts">
    import {
        AppWindow,
        Braces,
        Cpu,
        Database,
        Fingerprint,
        FlaskConical,
        Layers,
        Link,
        ListTodo,
        Radio,
    } from '@lucide/svelte';
    import type { Component } from 'svelte';

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

    let { general }: { general: GeneralInfo } = $props();

    const appItems = $derived<
        { icon: Component; label: string; value: string }[]
    >([
        {
            icon: AppWindow,
            label: 'Nombre de la aplicación',
            value: general.app_name,
        },
        { icon: Link, label: 'URL de la aplicación', value: general.app_url },
        { icon: FlaskConical, label: 'Entorno', value: general.env },
        { icon: Braces, label: 'Versión de PHP', value: general.php_version },
        {
            icon: Layers,
            label: 'Versión de Laravel',
            value: general.laravel_version,
        },
    ]);

    const driverItems = $derived<
        { icon: Component; label: string; value: string }[]
    >([
        {
            icon: Database,
            label: 'Base de datos',
            value: general.db_connection,
        },
        { icon: Cpu, label: 'Cache', value: general.cache_driver },
        { icon: ListTodo, label: 'Cola', value: general.queue_connection },
        { icon: Fingerprint, label: 'Sesión', value: general.session_driver },
        { icon: Radio, label: 'Broadcast', value: general.broadcast_driver },
    ]);
</script>

<div class="grid gap-4 lg:grid-cols-2">
    <section
        class="flex flex-col gap-4 rounded-xl border border-card-border bg-card p-5"
    >
        <div>
            <h2 class="text-base font-semibold text-foreground">Aplicación</h2>
            <p class="mt-0.5 text-sm text-muted-foreground">
                Información general de la aplicación.
            </p>
        </div>
        <dl class="grid gap-x-6 gap-y-4">
            {#each appItems as item (item.label)}
                {@const Icon = item.icon}
                <div class="flex items-start gap-3">
                    <span
                        class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-card-primary/40 text-primary"
                    >
                        <Icon class="size-4" />
                    </span>
                    <div class="min-w-0">
                        <dt class="text-xs text-muted-foreground">
                            {item.label}
                        </dt>
                        <dd
                            class="truncate text-sm font-medium text-foreground"
                        >
                            {item.value}
                        </dd>
                    </div>
                </div>
            {/each}
        </dl>
    </section>

    <section
        class="flex flex-col gap-4 rounded-xl border border-card-border bg-card p-5"
    >
        <div>
            <h2 class="text-base font-semibold text-foreground">Drivers</h2>
            <p class="mt-0.5 text-sm text-muted-foreground">
                Servicios de infraestructura configurados.
            </p>
        </div>
        <dl class="grid gap-x-6 gap-y-4">
            {#each driverItems as item (item.label)}
                {@const Icon = item.icon}
                <div class="flex items-start gap-3">
                    <span
                        class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-card-primary/40 text-primary"
                    >
                        <Icon class="size-4" />
                    </span>
                    <div class="min-w-0">
                        <dt class="text-xs text-muted-foreground">
                            {item.label}
                        </dt>
                        <dd
                            class="truncate text-sm font-medium text-foreground"
                        >
                            {item.value}
                        </dd>
                    </div>
                </div>
            {/each}
        </dl>
    </section>
</div>
