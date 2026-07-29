<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Badge from '@/components/ui/badge/badge.svelte';
    import type { PageLink } from './types';

    let { links }: { links: PageLink[] } = $props();
</script>

<div class="flex items-center justify-center gap-1 border-t px-5 py-4">
    {#each links as link (link.url)}
        {#if link.url}
            <Link
                href={link.url}
                class="inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 text-sm {link.active
                    ? 'bg-primary text-primary-foreground'
                    : 'text-muted-foreground hover:bg-accent'}"
                preserve-state
            >
                {link.label}
            </Link>
        {:else}
            <Badge>
                {link.label}
            </Badge>
        {/if}
    {/each}
</div>
