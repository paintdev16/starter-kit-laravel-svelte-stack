<script lang="ts">
    import { Calendar, Globe, Monitor, MapPin } from '@lucide/svelte';
    import {
        Avatar,
        AvatarFallback,
        AvatarImage,
    } from '@/components/ui/avatar';
    import { Badge } from '@/components/ui/badge';
    import { Separator } from '@/components/ui/separator';
    import * as Tooltip from '@/components/ui/tooltip';
    import { getDeviceIcon } from './device-icons';
    import type { LogItem } from './types';

    let { log }: { log: LogItem } = $props();

    let Icon = $derived(getDeviceIcon(log.device_type));

    function formatDate(dateStr: string) {
        return new Date(dateStr).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    function getInitials(name: string): string {
        return name
            .split(' ')
            .map((n) => n[0])
            .join('')
            .toUpperCase()
            .slice(0, 2);
    }

    function actionVariant(
        action: string,
    ): 'default' | 'secondary' | 'destructive' | 'outline' {
        if (action.includes('created')) {
return 'default';
}

        if (action.includes('updated') || action.includes('changed')) {
return 'secondary';
}

        if (action.includes('deleted')) {
return 'destructive';
}

        if (action.includes('login')) {
return 'outline';
}

        return 'secondary';
    }

    function actionLabel(action: string): string {
        if (action.includes('created')) {
return 'Creación';
}

        if (action.includes('updated') || action.includes('changed')) {
return 'Actualización';
}

        if (action.includes('deleted')) {
return 'Eliminación';
}

        if (action.includes('login')) {
return 'Login';
}

        if (action.includes('logout')) {
return 'Logout';
}

        return action;
    }
</script>

<div
    class="group flex items-start gap-4 px-5 py-4 transition-colors hover:bg-muted/40"
>
    <Avatar class="size-9 ring-2 ring-background shadow-sm">
        <AvatarImage src={null} alt={log.user?.name ?? ''} />
        <AvatarFallback
            class="text-[11px] font-semibold bg-muted text-muted-foreground"
        >
            {log.user ? getInitials(log.user.name) : 'SY'}
        </AvatarFallback>
    </Avatar>

    <div class="min-w-0 flex-1 space-y-1.5">
        <div class="flex items-center gap-2 flex-wrap">
            <p class="text-sm font-semibold text-foreground">
                {log.user?.name ?? 'Sistema'}
            </p>
            <Badge
                variant={actionVariant(log.action)}
                class="text-[10px] px-1.5 py-0 h-5"
            >
                {actionLabel(log.action)}
            </Badge>
        </div>

        <p class="text-sm text-muted-foreground leading-relaxed">
            {log.description ?? log.action}
        </p>

        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 pt-0.5">
            <span
                class="inline-flex items-center gap-1 text-[11px] text-muted-foreground/80"
            >
                <Calendar class="size-3 opacity-60" />
                {formatDate(log.created_at)}
            </span>

            {#if log.action === 'auth.login' && log.updated_at !== log.created_at}
                <Separator orientation="vertical" class="h-3" />
                <span
                    class="inline-flex items-center gap-1 text-[11px] text-primary font-medium"
                >
                    <Calendar class="size-3" />
                    Último login: {formatDate(log.updated_at)}
                </span>
            {/if}

            {#if log.browser}
                <Separator orientation="vertical" class="h-3" />
                <span
                    class="inline-flex items-center gap-1 text-[11px] text-muted-foreground/70"
                >
                    <Globe class="size-3 opacity-50" />
                    {log.browser}{log.browser_version
                        ? ` ${log.browser_version}`
                        : ''}
                </span>
            {/if}

            {#if log.os}
                <Separator orientation="vertical" class="h-3" />
                <span
                    class="inline-flex items-center gap-1 text-[11px] text-muted-foreground/70"
                >
                    <Monitor class="size-3 opacity-50" />
                    {log.os}
                </span>
            {/if}

            {#if log.ip_address}
                <Separator orientation="vertical" class="h-3" />
                <span
                    class="inline-flex items-center gap-1 text-[11px] text-muted-foreground/70"
                >
                    <MapPin class="size-3 opacity-50" />
                    {log.ip_address}
                </span>
            {/if}
        </div>
    </div>

    <Tooltip.Root>
        <Tooltip.Trigger>
            <div
                class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground group-hover:bg-background group-hover:shadow-sm group-hover:text-foreground transition-all cursor-default"
            >
                <Icon class="size-4" />
            </div>
        </Tooltip.Trigger>
        <Tooltip.Content side="left">
            <p class="text-xs">{log.device_type ?? 'Desconocido'}</p>
        </Tooltip.Content>
    </Tooltip.Root>
</div>
