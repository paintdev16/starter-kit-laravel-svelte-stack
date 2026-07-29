<script lang="ts">
    import type { Snippet } from 'svelte';
    import AppContent from '@/components/AppContent.svelte';
    import AppShell from '@/components/AppShell.svelte';
    import AppSidebar from '@/components/AppSidebar.svelte';
    import AppSidebarHeader from '@/components/AppSidebarHeader.svelte';
    import { Toaster } from '@/components/ui/sonner';
    import UserNotificationListener from '@/components/UserNotificationListener.svelte';
    import type { BreadcrumbItem } from '@/types';
    import AppearanceTabs from '@/components/AppearanceTabs.svelte';

    let {
        breadcrumbs = [],
        children,
    }: {
        breadcrumbs?: BreadcrumbItem[];
        children?: Snippet;
    } = $props();
</script>

<AppShell variant="sidebar">
    <AppSidebar />
    <AppContent class="overflow-x-hidden">
        <AppSidebarHeader {breadcrumbs} />
        <div class="flex-1 overflow-y-auto">
            {@render children?.()}
        </div>
    </AppContent>
    <Toaster />
    <UserNotificationListener />
</AppShell>
