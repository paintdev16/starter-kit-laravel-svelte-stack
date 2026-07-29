<script lang="ts">
    import Breadcrumbs from '@/components/Breadcrumbs.svelte';
    import { SidebarTrigger } from '@/components/ui/sidebar';
    import type { BreadcrumbItem } from '@/types';
    import AppearanceTabs from './AppearanceTabs.svelte';
    import GlobalSearchController from './GlobalSearchController.svelte';

    let {
        breadcrumbs = [],
    }: {
        breadcrumbs?: BreadcrumbItem[];
    } = $props();
</script>

<header
    class="flex h-12 shrink-0 items-center gap-2 border-b border-sidebar-border/70 bg-background p-2"
>
    <div class="flex items-center gap-2">
        <SidebarTrigger class="-ml-1" />
        {#if breadcrumbs && breadcrumbs.length > 0}
            <Breadcrumbs {breadcrumbs} />
        {/if}
        <AppearanceTabs />
        <GlobalSearchController/>
    </div>
</header>
