<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import { Users } from '@lucide/svelte';
    import AudioWaveform from '@lucide/svelte/icons/audio-waveform';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Bot from '@lucide/svelte/icons/bot';
    import ChevronRight from '@lucide/svelte/icons/chevron-right';
    import ChevronsUpDown from '@lucide/svelte/icons/chevrons-up-down';
    import Command from '@lucide/svelte/icons/command';
    import GalleryVerticalEnd from '@lucide/svelte/icons/gallery-vertical-end';
    import LayoutDashboard from '@lucide/svelte/icons/layout-dashboard';
    import Plus from '@lucide/svelte/icons/plus';
    import Settings2 from '@lucide/svelte/icons/settings-2';
    import SquareTerminal from '@lucide/svelte/icons/square-terminal';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import NavUser from '@/components/NavUser.svelte';
    import {
        Collapsible,
        CollapsibleContent,
        CollapsibleTrigger,
    } from '@/components/ui/collapsible';
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
        SidebarGroup,
        SidebarGroupContent,
        SidebarGroupLabel,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
        SidebarMenuSub,
        SidebarMenuSubButton,
        SidebarMenuSubItem,
        SidebarRail,
    } from '@/components/ui/sidebar';
    import { useSidebar } from '@/components/ui/sidebar';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';
    import users from '@/routes/users';

    let { children }: { children?: Snippet } = $props();

    const url = currentUrlState();
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
    const navigation = [
        {
            title: 'Espacio de trabajo',
            icon: SquareTerminal,
            items: ['Historial', 'Destacados', 'Configuración'],
            open: true,
        },
        {
            title: 'Modelos',
            icon: Bot,
            items: ['Genesis', 'Explorador', 'Quantum'],
            open: false,
        },
        {
            title: 'Documentación',
            icon: BookOpen,
            items: ['Introducción', 'Comenzar', 'Tutoriales'],
            open: false,
        },
        {
            title: 'Configuración',
            icon: Settings2,
            items: ['General', 'Equipo', 'Facturación'],
            open: false,
        },
    ];
</script>

<Sidebar collapsible="icon" variant="inset">
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
                                                ? 'bg-accent'
                                                : ''}"
                                        >
                                            <div
                                                class="flex size-6 items-center justify-center rounded-md border"
                                            >
                                                <Icon
                                                    class="size-4 shrink-0"
                                                />
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
                                class="flex size-6 items-center justify-center rounded-md border bg-background"
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
        <SidebarGroup>
            <SidebarGroupLabel>Plataforma</SidebarGroupLabel>
            <SidebarGroupContent>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            isActive={url.isCurrentUrl(
                                dashboard(),
                                url.currentUrl,
                            )}
                            tooltipContent="Panel principal"
                        >
                            {#snippet child({ props })}
                                <Link {...props} href={toUrl(dashboard())}>
                                    <LayoutDashboard />
                                    <span>Panel principal</span>
                                </Link>
                            {/snippet}
                        </SidebarMenuButton>
                        <SidebarMenuButton
                            isActive={url.isCurrentUrl(
                                users.index(),
                                url.currentUrl,
                            )}
                            tooltipContent="Usuarios"
                        >
                            {#snippet child({ props })}
                                <Link {...props} href={toUrl(users.index())}>
                                    <Users />
                                    <span>Usuarios</span>
                                </Link>
                            {/snippet}
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    {#each navigation as navItem (navItem.title)}
                        {@const NavItemIcon = navItem.icon}
                        <Collapsible open={navItem.open} class="group/collapsible">
                            <SidebarMenuItem>
                                <CollapsibleTrigger>
                                    {#snippet child({ props })}
                                        <SidebarMenuButton
                                            {...props}
                                            tooltipContent={navItem.title}
                                        >
                                            {#if NavItemIcon}
                                                <NavItemIcon />
                                            {/if}
                                            <span>{navItem.title}</span>
                                            <ChevronRight
                                                class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                            />
                                        </SidebarMenuButton>
                                    {/snippet}
                                </CollapsibleTrigger>
                                <CollapsibleContent>
                                    <SidebarMenuSub>
                                        {#each navItem.items as subItem (subItem)}
                                            <SidebarMenuSubItem>
                                                <SidebarMenuSubButton>
                                                    {subItem}
                                                </SidebarMenuSubButton>
                                            </SidebarMenuSubItem>
                                        {/each}
                                    </SidebarMenuSub>
                                </CollapsibleContent>
                            </SidebarMenuItem>
                        </Collapsible>
                    {/each}
                </SidebarMenu>
            </SidebarGroupContent>
        </SidebarGroup>
    </SidebarContent>

    <SidebarFooter>
        <NavUser />
    </SidebarFooter>
    <SidebarRail />
</Sidebar>
{@render children?.()}
