<script lang="ts">
    import { Button } from '@/components/ui/button';
    import { themeState } from '@/lib/theme.svelte';
    import { cn } from '@/lib/utils';
    import type { Appearance } from '@/types/ui';

    import { Monitor, Moon, Sun } from '@lucide/svelte/icons';

    const { updateAppearance } = themeState();

    const tabs: {
        value: Appearance;
        icon: typeof Sun;
        activeClass: string;
        activeIconClass: string;
    }[] = [
        {
            value: 'light',
            icon: Sun,
            activeClass:
                '[html[data-appearance=light]_&]:bg-background [html[data-appearance=light]_&]:shadow-sm [html[data-appearance=light]_&]:scale-100',
            activeIconClass:
                '[html[data-appearance=light]_&]:text-amber-500',
        },
        {
            value: 'system',
            icon: Monitor,
            activeClass:
                '[html[data-appearance=system]_&]:bg-background [html[data-appearance=system]_&]:shadow-sm [html[data-appearance=system]_&]:scale-100',
            activeIconClass:
                '[html[data-appearance=system]_&]:text-sky-500',
        },
        {
            value: 'dark',
            icon: Moon,
            activeClass:
                '[html[data-appearance=dark]_&]:bg-background [html[data-appearance=dark]_&]:shadow-sm [html[data-appearance=dark]_&]:scale-100',
            activeIconClass:
                '[html[data-appearance=dark]_&]:text-indigo-500',
        },
    ];
</script>

<div
    class="inline-flex items-center gap-0.5 rounded-full border border-border/50 bg-muted/40 p-1 shadow-xs backdrop-blur-md"
>
    {#each tabs as { value, icon: Icon, activeClass, activeIconClass } (value)}
        <Button
            variant="ghost"
            data-appearance-option={value}
            aria-label={value}
            class={cn(
                'relative h-7 w-7 scale-90 rounded-full text-muted-foreground transition-all duration-300 ease-out hover:bg-background/60 hover:text-foreground',
                activeClass,
            )}
            onclick={() => updateAppearance(value)}
        >
            <Icon
                class={cn(
                    'relative h-3.5 w-3.5 transition-colors duration-300',
                    activeIconClass,
                )}
            />
        </Button>
    {/each}
</div>
