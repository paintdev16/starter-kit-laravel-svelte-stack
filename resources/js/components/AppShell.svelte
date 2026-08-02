<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import { SidebarProvider } from '@/components/ui/sidebar';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    let isOpen = $state(page.props.sidebarOpen);
</script>

<SidebarProvider bind:open={isOpen}>
    {@render children?.()}
</SidebarProvider>
