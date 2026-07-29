<script lang="ts">
    import type { Snippet } from 'svelte';
    import { SidebarInset } from '@/components/ui/sidebar';
    import { cn } from '@/lib/utils';

    let {
        class: className = '',
        children,
    }: {
        class?: string;
        children?: Snippet;
    } = $props();

    const contentClass =
        'mx-auto flex h-svh max-h-svh min-h-0 w-full max-w-7xl flex-1 flex-col rounded-xl md:peer-data-[variant=inset]:h-[calc(100svh-1rem)] md:peer-data-[variant=inset]:max-h-[calc(100svh-1rem)]';
</script>

<SidebarInset class={cn(contentClass, className)}>
    {@render children?.()}
</SidebarInset>
