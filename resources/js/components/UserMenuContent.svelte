<script lang="ts">
    import { Link, router, usePage } from '@inertiajs/svelte';
    import {
        BadgeCheck,
        Bell,
        CreditCard,
        LogOut,
        Settings,
        Sparkles,
    } from '@lucide/svelte';
    import {
        DropdownMenuGroup,
        DropdownMenuItem,
        DropdownMenuLabel,
        DropdownMenuSeparator,
    } from '@/components/ui/dropdown-menu';
    import UserInfo from '@/components/UserInfo.svelte';
    import { systemNavItems } from '@/lib/navigation';
    import { toUrl } from '@/lib/utils';
    import { logout } from '@/routes';
    import { show } from '@/routes/profile';
    import type { User } from '@/types';

    let { user }: { user: User } = $props();

    const page = usePage();

    const isRoot = $derived(
        Array.isArray(page.props.auth.roles) &&
            page.props.auth.roles.includes('root'),
    );

    function handleLogout(propsOnClick?: () => void) {
        return () => {
            propsOnClick?.();
            router.flushAll();
        };
    }
</script>

<DropdownMenuLabel class="p-0 font-normal">
    <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
        <UserInfo {user} showEmail={true} />
    </div>
</DropdownMenuLabel>
<DropdownMenuSeparator />
<DropdownMenuGroup>
    <DropdownMenuItem
        ><Sparkles class="mr-2 size-4" />Actualizar a Pro</DropdownMenuItem
    >
</DropdownMenuGroup>
<DropdownMenuSeparator />
<DropdownMenuGroup>
    <DropdownMenuItem><BadgeCheck class="mr-2 size-4" />Cuenta</DropdownMenuItem
    >
    <DropdownMenuItem
        ><CreditCard class="mr-2 size-4" />Facturación</DropdownMenuItem
    >
    <DropdownMenuItem
        ><Bell class="mr-2 size-4" />Notificaciones</DropdownMenuItem
    >
    <DropdownMenuItem>
        {#snippet child({ props })}
            <Link {...props} href={toUrl(show())} prefetch
                ><Settings class="mr-2 size-4" />Configuración</Link
            >
        {/snippet}
    </DropdownMenuItem>
</DropdownMenuGroup>
{#if isRoot}
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        {#each systemNavItems as item (toUrl(item.href))}
            {@const Icon = item.icon}
            <DropdownMenuItem>
                {#snippet child({ props })}
                    <Link {...props} href={toUrl(item.href)}>
                        {#if Icon}<Icon class="mr-2 size-4" />{/if}
                        {item.title}
                    </Link>
                {/snippet}
            </DropdownMenuItem>
        {/each}
    </DropdownMenuGroup>
{/if}
<DropdownMenuSeparator />
<DropdownMenuItem>
    {#snippet child({ props })}
        <Link
            {...props}
            href={logout()}
            as="button"
            onclick={handleLogout(
                typeof props.onclick === 'function'
                    ? (props.onclick as () => void)
                    : undefined,
            )}
            data-test="logout-button"
        >
            <LogOut class="mr-2 size-4" />Cerrar sesión
        </Link>
    {/snippet}
</DropdownMenuItem>
