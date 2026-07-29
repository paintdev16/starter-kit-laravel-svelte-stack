<script lang="ts">
    import type { Snippet } from 'svelte';
    import AppHeader from '@/components/AppHeader.svelte';
    import AppShell from '@/components/AppShell.svelte';
    import { Toaster } from '@/components/ui/sonner';
    import type { BreadcrumbItem } from '@/types';

    let {
        breadcrumbs = [],
        children,
    }: {
        breadcrumbs?: BreadcrumbItem[];
        children?: Snippet;
    } = $props();
</script>

<AppShell variant="header">
    <AppHeader {breadcrumbs} />
    <main
        class="mx-auto flex h-svh max-h-svh min-h-0 w-full max-w-7xl flex-1 flex-col overflow-y-auto rounded-xl"
    >
        {@render children?.()}
    </main>
    <Toaster />
</AppShell>
