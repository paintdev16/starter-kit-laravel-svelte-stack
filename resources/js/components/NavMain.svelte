<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import {
        SidebarGroup,
        SidebarGroupLabel,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { toUrl } from '@/lib/utils';
    import type { NavItem } from '@/types';

    let {
        items = [],
    }: {
        items: NavItem[];
    } = $props();

    const url = currentUrlState();
</script>

<SidebarGroup class="px-2 py-0">
    <SidebarGroupLabel>Platform</SidebarGroupLabel>
    <SidebarMenu>
        {#each items as navItem (toUrl(navItem.href))}
            {@const NavIcon = navItem.icon}
            <SidebarMenuItem>
                <SidebarMenuButton
                    isActive={url.isCurrentUrl(navItem.href, url.currentUrl)}
                >
                    {#snippet child({ props })}
                        <Link {...props} href={toUrl(navItem.href)}>
                            {#if NavIcon}
                                <NavIcon class="size-4 shrink-0" />
                            {/if}
                            <span>{navItem.title}</span>
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        {/each}
    </SidebarMenu>
</SidebarGroup>
