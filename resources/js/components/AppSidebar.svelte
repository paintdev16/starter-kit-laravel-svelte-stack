<script lang="ts">
    import { usePage } from '@inertiajs/svelte';
    import AudioWaveform from '@lucide/svelte/icons/audio-waveform';
    import ChevronsUpDown from '@lucide/svelte/icons/chevrons-up-down';
    import Command from '@lucide/svelte/icons/command';
    import GalleryVerticalEnd from '@lucide/svelte/icons/gallery-vertical-end';
    import Plus from '@lucide/svelte/icons/plus';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import NavUser from '@/components/NavUser.svelte';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuGroup,
        DropdownMenuItem,
        DropdownMenuLabel,
        DropdownMenuSeparator,
        DropdownMenuShortcut,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
        SidebarRail,
    } from '@/components/ui/sidebar';
    import { useSidebar } from '@/components/ui/sidebar';
    import { mainNavItems } from '@/lib/navigation';

    const page = usePage();

    const userRoles = $derived(
        Array.isArray(page.props.auth.roles) ? page.props.auth.roles : [],
    );

    const visibleNavItems = $derived(
        mainNavItems.filter(
            (item) =>
                !item.roles ||
                item.roles.some((role) => userRoles.includes(role)),
        ),
    );

    let { children }: { children?: Snippet } = $props();

    const sidebar = useSidebar();
    const teams = [
        {
            name: 'Acme Inc',
            plan: 'Empresa',
            icon: GalleryVerticalEnd,
            shortcut: '⌘1',
        },
        {
            name: 'Acme Corp.',
            plan: 'Startup',
            icon: AudioWaveform,
            shortcut: '⌘2',
        },
        { name: 'Evil Corp.', plan: 'Gratis', icon: Command, shortcut: '⌘3' },
    ];
</script>

<Sidebar collapsible="icon" variant="floating">
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <DropdownMenu>
                    <DropdownMenuTrigger>
                        {#snippet child({ props })}
                            <SidebarMenuButton
                                {...props}
                                size="lg"
                                class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                            >
                                <AppLogo />
                                <div
                                    class="grid flex-1 text-left text-sm leading-tight"
                                >
                                    <span class="truncate font-semibold"
                                        >Acme Inc</span
                                    >
                                    <span class="truncate text-xs">Empresa</span
                                    >
                                </div>
                                <ChevronsUpDown class="ml-auto" />
                            </SidebarMenuButton>
                        {/snippet}
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        class="w-(--bits-dropdown-menu-anchor-width) min-w-56 rounded-lg"
                        side={sidebar.state === 'collapsed' && !sidebar.isMobile
                            ? 'left'
                            : 'bottom'}
                        align="start"
                        sideOffset={4}
                    >
                        <DropdownMenuLabel
                            class="text-muted-foreground text-xs"
                        >
                            Equipos
                        </DropdownMenuLabel>
                        <DropdownMenuGroup>
                            {#each teams as team (team.name)}
                                {@const Icon = team.icon}
                                <DropdownMenuItem
                                    class="gap-2 p-2 {team.name === 'Acme Inc'
                                        ? 'bg-sidebar-accent text-sidebar-accent-foreground'
                                        : ''}"
                                >
                                    <div
                                        class="flex size-6 items-center justify-center rounded-md border border-sidebar-border bg-sidebar-accent text-sidebar-accent-foreground"
                                    >
                                        <Icon class="size-4 shrink-0" />
                                    </div>
                                    {team.name}
                                    <DropdownMenuShortcut
                                        >{team.shortcut}</DropdownMenuShortcut
                                    >
                                </DropdownMenuItem>
                            {/each}
                        </DropdownMenuGroup>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem class="gap-2 p-2">
                            <div
                                class="flex size-6 items-center justify-center rounded-md border border-sidebar-border bg-sidebar text-sidebar-foreground"
                            >
                                <Plus class="size-4" />
                            </div>
                            <span class="font-medium text-muted-foreground"
                                >Añadir equipo</span
                            >
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
        <NavMain groupLabel="Plataforma" items={visibleNavItems} />
    </SidebarContent>

    <SidebarFooter>
        <NavUser />
    </SidebarFooter>
    <SidebarRail />
</Sidebar>
{@render children?.()}
