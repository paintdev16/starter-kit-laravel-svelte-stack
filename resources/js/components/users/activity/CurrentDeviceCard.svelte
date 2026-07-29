<script lang="ts">
    import { Badge } from '@/components/ui/badge';
    import * as Card from '@/components/ui/card';
    import { Separator } from '@/components/ui/separator';
    import Skeleton from '@/components/ui/skeleton/skeleton.svelte';
    import * as Tooltip from '@/components/ui/tooltip';
    import { getDeviceIcon } from './device-icons';
    import type { DeviceInfo } from './types';

    let { device, loading }: { device: DeviceInfo | null; loading: boolean } =
        $props();

    let Icon = $derived(getDeviceIcon(device?.device_type));

    let fields = $derived.by(() => {
        if (!device) {
            return [];
        }

        return [
            {
                label: 'Navegador',
                value:
                    device.browser && device.browser_version
                        ? `${device.browser} ${device.browser_version}`
                        : device.browser,
            },
            {
                label: 'Sistema operativo',
                value:
                    device.os && device.os_version
                        ? `${device.os} ${device.os_version}`
                        : device.os,
            },
            { label: 'Tipo de dispositivo', value: device.device_type },
            {
                label: 'Marca / Modelo',
                value:
                    [device.device_brand, device.device_model]
                        .filter(Boolean)
                        .join(' ') || '—',
            },
            { label: 'Dirección IP', value: device.ip_address },
        ].filter((f) => f.value);
    });
</script>

<Card.Root class="overflow-hidden">
    <Card.Header class="pb-3">
        <div class="flex items-center gap-2">
            <Card.Title class="text-sm font-semibold"
                >Tu dispositivo actual</Card.Title
            >
            {#if device && !loading}
                <Badge variant="outline" class="text-[10px] h-5 px-1.5">
                    Activo
                </Badge>
            {/if}
        </div>
    </Card.Header>

    <Card.Content class="space-y-0 pb-5">
        {#if loading}
            <div class="space-y-3">
                {#each Array(5) as _, i (i)}
                    <div class="flex items-center justify-between">
                        <Skeleton class="h-3 w-24 rounded-sm" />
                        <Skeleton class="h-3 w-32 rounded-sm" />
                    </div>
                {/each}
            </div>
        {:else if device}
            {#each fields as field, i (field.label)}
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-muted-foreground"
                        >{field.label}</span
                    >
                    <span
                        class="text-xs font-medium text-foreground text-right truncate max-w-[60%]"
                    >
                        {field.value}
                    </span>
                </div>
                {#if i < fields.length - 1}
                    <Separator />
                {/if}
            {/each}

            <div
                class="mt-3 flex items-center gap-2 text-xs text-muted-foreground"
            >
                <Tooltip.Root>
                    <Tooltip.Trigger>
                        <div class="flex items-center gap-2 cursor-default">
                            <Icon class="size-3.5" />
                            <span>Detección basada en User-Agent</span>
                        </div>
                    </Tooltip.Trigger>
                    <Tooltip.Content side="bottom">
                        <p class="text-xs">Identificado automáticamente</p>
                    </Tooltip.Content>
                </Tooltip.Root>
            </div>
        {:else}
            <div class="py-6 text-center">
                <p class="text-sm text-muted-foreground">
                    No se pudo detectar la información del dispositivo.
                </p>
            </div>
        {/if}
    </Card.Content>
</Card.Root>
