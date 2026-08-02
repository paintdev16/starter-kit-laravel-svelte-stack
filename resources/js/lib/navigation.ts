import LayoutGrid from '@lucide/svelte/icons/layout-grid';
import Settings2 from '@lucide/svelte/icons/settings-2';
import Users from '@lucide/svelte/icons/users';
import { dashboard } from '@/routes';
import systems from '@/routes/systems';
import users from '@/routes/users';
import type { NavItem } from '@/types';

export const mainNavItems: NavItem[] = [
    {
        title: 'Panel principal',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Usuarios',
        href: users.index(),
        icon: Users,
    },
];

export const systemNavItems: NavItem[] = [
    {
        title: 'Sistema',
        href: systems.index(),
        icon: Settings2,
        roles: ['root'],
    },
];
