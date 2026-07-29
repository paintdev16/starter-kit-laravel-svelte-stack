<script lang="ts">
    import {
        SidebarGroup,
        SidebarGroupContent,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { toUrl } from '@/lib/utils';
    import type { NavItem } from '@/types';

    let {
        items = [],
        class: className = '',
    }: {
        items: NavItem[];
        class?: string;
    } = $props();
</script>

<SidebarGroup class={`group-data-[collapsible=icon]:p-0 ${className}`}>
    <SidebarGroupContent>
        <SidebarMenu>
            {#each items as item (toUrl(item.href))}
                {@const Icon = item.icon}
                <SidebarMenuItem>
                    <SidebarMenuButton
                        class="text-sidebar-foreground/70 hover:text-sidebar-foreground"
                    >
                        {#snippet child({ props })}
                            <a
                                {...props}
                                href={toUrl(item.href)}
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {#if Icon}
                                    <Icon class="size-4 shrink-0" />
                                {/if}
                                <span>{item.title}</span>
                            </a>
                        {/snippet}
                    </SidebarMenuButton>
                </SidebarMenuItem>
            {/each}
        </SidebarMenu>
    </SidebarGroupContent>
</SidebarGroup>
