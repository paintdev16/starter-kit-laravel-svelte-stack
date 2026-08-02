<script module lang="ts">
    import { index } from '@/routes/users';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Usuarios',
                href: index(),
            },
        ],
    };
</script>

<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import {
        Calendar,
        Check,
        ClipboardList,
        KeyRound,
        Mail,
        MoreHorizontal,
        Pencil,
        Plus,
        Shield,
        ShieldCheck,
        Trash2,
        UsersIcon,
    } from '@lucide/svelte';
    import type { Component } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import {
        Avatar,
        AvatarFallback,
        AvatarImage,
    } from '@/components/ui/avatar';
    import { Button } from '@/components/ui/button';
    import { Checkbox } from '@/components/ui/checkbox';
    import {
        Dialog,
        DialogContent,
        DialogDescription,
        DialogFooter,
        DialogHeader,
        DialogTitle,
    } from '@/components/ui/dialog';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import {
        Pagination,
        PaginationContent,
        PaginationItem,
        PaginationLink,
        PaginationPrevious,
        PaginationNext,
        PaginationEllipsis,
    } from '@/components/ui/pagination';
    import Skeleton from '@/components/ui/skeleton/skeleton.svelte';
    import {
        Tabs,
        TabsContent,
        TabsList,
        TabsTrigger,
    } from '@/components/ui/tabs';
    import ActivityOverview from '@/components/users/activity/ActivityOverview.svelte';
    import RolesPermisos from '@/components/users/RolePermissions.svelte';
    import Token from './Token.svelte';
    import Verification from './Verification.svelte';

    type TabId = 'users' | 'roles' | 'activity' | 'token' | 'verification';

    interface TabDef {
        id: TabId;
        label: string;
        icon: Component;
        description: string;
    }

    interface UserItem {
        id: number;
        name: string;
        email: string;
        avatar: string | null;
        email_verified_at: string | null;
        has_two_factor: boolean;
        has_passkeys: boolean;
        roles: string[];
        created_at: string;
        updated_at: string;
    }

    interface RoleItem {
        id: number;
        name: string;
        guard_name: string;
        permissions: string[];
        created_at: string;
    }

    interface PaginatedData<T> {
        data: T[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: { url: string | null; label: string; active: boolean }[];
    }

    let {
        users = {
            data: [],
            current_page: 1,
            last_page: 1,
            per_page: 12,
            total: 0,
            from: null,
            to: null,
            links: [],
        },
        rootCount = 0,
        canViewActivity = false,
        canManageTokens = false,
        canCreateUser = false,
        canManageVerification = false,
        canViewRoles = false,
    }: {
        users?: PaginatedData<UserItem>;
        rootCount?: number;
        canViewActivity?: boolean;
        canManageTokens?: boolean;
        canCreateUser?: boolean;
        canManageVerification?: boolean;
        canViewRoles?: boolean;
    } = $props();

    const MOBILE_PRIMARY_COUNT = 2;
    let isMobile = $state(false);

    $effect(() => {
        if (typeof window === 'undefined') {
            return;
        }

        const mq = window.matchMedia('(max-width: 767px)');
        const update = () => {
            isMobile = mq.matches;
        };
        update();
        mq.addEventListener('change', update);

        return () => mq.removeEventListener('change', update);
    });

    const tabMeta: Record<TabId, { title: string; description: string }> = {
        users: {
            title: 'Usuarios',
            description: '',
        },
        roles: {
            title: 'Roles y permisos',
            description: 'Administra los roles y permisos del sistema',
        },
        activity: {
            title: 'Actividad',
            description: 'Historial de actividad de los usuarios',
        },
        token: {
            title: 'Tokens de API',
            description: 'Gestiona tokens para integraciones con terceros',
        },
        verification: {
            title: 'Verificación',
            description: 'Gestiona la verificación de correos electrónicos',
        },
    };

    const visibleTabs = $derived.by((): TabDef[] => {
        const defs: (TabDef & { show: boolean })[] = [
            {
                id: 'users',
                label: 'Usuarios',
                icon: UsersIcon,
                description: tabMeta.users.description,
                show: true,
            },
            {
                id: 'roles',
                label: 'Roles',
                icon: Shield,
                description: tabMeta.roles.description,
                show: canViewRoles,
            },
            {
                id: 'activity',
                label: 'Actividad',
                icon: ClipboardList,
                description: tabMeta.activity.description,
                show: canViewActivity,
            },
            {
                id: 'token',
                label: 'Token',
                icon: KeyRound,
                description: tabMeta.token.description,
                show: canManageTokens,
            },
            {
                id: 'verification',
                label: 'Verificación',
                icon: ShieldCheck,
                description: tabMeta.verification.description,
                show: canManageVerification,
            },
        ];

        return defs.filter((t) => t.show).map(({ show: _, ...rest }) => rest);
    });

    const allowedTabIds = $derived(visibleTabs.map((t) => t.id));

    function isTabAllowed(id: string): id is TabId {
        switch (id) {
            case 'users':
                return true;
            case 'roles':
                return canViewRoles;
            case 'activity':
                return canViewActivity;
            case 'token':
                return canManageTokens;
            case 'verification':
                return canManageVerification;
            default:
                return false;
        }
    }

    function readTabFromUrl(): TabId {
        try {
            const raw = page.url.includes('?')
                ? page.url.slice(page.url.indexOf('?') + 1)
                : '';
            const param = new URLSearchParams(raw).get('tab');

            if (param && isTabAllowed(param)) {
                return param;
            }
        } catch {
            // ignore
        }

        return 'users';
    }

    let tab = $state<TabId>(readTabFromUrl());

    const headerTitle = $derived(tabMeta[tab]?.title ?? 'Usuarios');
    const headerDescription = $derived.by(() => {
        if (tab === 'users') {
            const unit =
                users.total === 1
                    ? 'usuario registrado'
                    : 'usuarios registrados';

            return `${users.total} ${unit} en el sistema`;
        }

        return tabMeta[tab]?.description ?? '';
    });

    const barTabs = $derived.by((): TabDef[] => {
        const ordered = visibleTabs;

        if (!isMobile || ordered.length <= MOBILE_PRIMARY_COUNT) {
            return ordered;
        }

        const primary = ordered.slice(0, MOBILE_PRIMARY_COUNT);

        if (primary.some((t) => t.id === tab)) {
            return primary;
        }

        const active = ordered.find((t) => t.id === tab);

        if (!active) {
            return primary;
        }

        return [...primary.slice(0, MOBILE_PRIMARY_COUNT - 1), active];
    });

    const overflowTabs = $derived.by((): TabDef[] => {
        if (!isMobile || visibleTabs.length <= MOBILE_PRIMARY_COUNT) {
            return [];
        }

        const barIds = new Set(barTabs.map((t) => t.id));

        return visibleTabs.filter((t) => !barIds.has(t.id));
    });

    const overflowHasActive = $derived(overflowTabs.some((t) => t.id === tab));

    function syncTabToUrl(next: TabId) {
        if (typeof window === 'undefined') {
            return;
        }

        const url = new URL(window.location.href);

        if (next === 'users') {
            url.searchParams.delete('tab');
        } else {
            url.searchParams.set('tab', next);
        }

        window.history.replaceState(window.history.state, '', url);
    }

    function setTab(next: string) {
        if (!isTabAllowed(next)) {
            return;
        }

        tab = next;
        syncTabToUrl(tab);
    }

    $effect(() => {
        if (!allowedTabIds.includes(tab)) {
            tab = 'users';
        }

        syncTabToUrl(tab);
    });

    function getInitials(name: string): string {
        return name
            .split(' ')
            .map((n) => n[0])
            .join('')
            .toUpperCase()
            .slice(0, 2);
    }

    let userDialogOpen = $state(false);
    let editingUser = $state<UserItem | null>(null);
    let userFormName = $state('');
    let userFormEmail = $state('');
    let userFormPassword = $state('');
    let selectedRole = $state('');
    let userFormErrors = $state<Record<string, string>>({});
    let userFormVerified = $state(false);
    let savingUser = $state(false);
    let availableRoles = $state<RoleItem[]>([]);
    let rolesLoading = $state(false);

    let deleteConfirmUser = $state<UserItem | null>(null);
    let deletingUser = $state(false);

    function resetUserForm() {
        userFormName = '';
        userFormEmail = '';
        userFormPassword = '';
        selectedRole = '';
        userFormErrors = {};
    }

    function openCreateUser() {
        editingUser = null;
        resetUserForm();
        loadRoles();
        userDialogOpen = true;
    }

    function openEditUser(user: UserItem) {
        editingUser = user;
        userFormName = user.name;
        userFormEmail = user.email;
        userFormPassword = '';
        selectedRole = user.roles[0] ?? '';
        userFormVerified = user.email_verified_at !== null;
        userFormErrors = {};
        loadRoles();
        userDialogOpen = true;
    }

    let isLastRoot = $derived(
        editingUser !== null &&
            editingUser.roles.includes('root') &&
            rootCount <= 1,
    );
    let filteredRoles = $derived(
        rootCount > 0
            ? availableRoles.filter((r) => r.name !== 'root')
            : availableRoles,
    );

    async function loadRoles() {
        rolesLoading = true;

        try {
            const res = await fetch('/admin/roles');
            availableRoles = await res.json();
        } catch {
            availableRoles = [];
        } finally {
            rolesLoading = false;
        }
    }

    function formatRoleName(name: string): string {
        return name
            .split('-')
            .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
            .join(' ');
    }

    function userCardClasses(user: UserItem): string {
        if (user.roles.some((r) => r === 'root' || r === 'super-admin')) {
            return 'border-card-primary-border bg-card-primary/40 hover:border-card-primary-border/70 hover:bg-card-primary/60';
        }

        if (user.roles.some((r) => r === 'admin')) {
            return 'border-card-info-border bg-card-info/40 hover:border-card-info-border/70 hover:bg-card-info/60';
        }

        return 'border-card-success-border bg-card-success/40 hover:border-card-success-border/70 hover:bg-card-success/60';
    }

    function roleCard(name: string) {
        const styles: Record<string, string> = {
            'super-admin':
                'border-card-primary-border bg-card-primary/40 hover:border-card-primary-border/70 hover:bg-card-primary/60',
            admin: 'border-card-info-border bg-card-info/40 hover:border-card-info-border/70 hover:bg-card-info/60',
            user: 'border-card-success-border bg-card-success/40 hover:border-card-success-border/70 hover:bg-card-success/60',
        };

        return (
            styles[name] ||
            'border-card-primary-border bg-card-primary/40 hover:border-card-primary-border/70 hover:bg-card-primary/60'
        );
    }

    function roleSelectedCard(name: string) {
        const styles: Record<string, string> = {
            'super-admin':
                'border-card-primary-border bg-card-primary/70 ring-2 ring-primary/40',
            admin: 'border-card-info-border bg-card-info/70 ring-2 ring-info/40',
            user: 'border-card-success-border bg-card-success/70 ring-2 ring-success/40',
        };

        return (
            styles[name] ||
            'border-card-primary-border bg-card-primary/70 ring-2 ring-primary/40'
        );
    }

    function roleIcon(name: string) {
        const styles: Record<string, string> = {
            'super-admin': 'bg-card-primary/80 text-primary',
            admin: 'bg-card-info/80 text-info',
            user: 'bg-card-success/80 text-success',
        };

        return styles[name] || 'bg-card-primary/80 text-primary';
    }

    function saveUser() {
        userFormErrors = {};
        let fieldErrors: Record<string, string> = {};

        if (!userFormName.trim()) {
            fieldErrors.name = 'El nombre es obligatorio';
        }

        if (!userFormEmail.trim()) {
            fieldErrors.email = 'El email es obligatorio';
        }

        if (!editingUser && !userFormPassword.trim()) {
            fieldErrors.password = 'La contraseña es obligatoria';
        }

        if (Object.keys(fieldErrors).length > 0) {
            userFormErrors = fieldErrors;

            return;
        }

        savingUser = true;

        const body: Record<string, any> = {
            name: userFormName.trim(),
            email: userFormEmail.trim(),
            role: selectedRole || undefined,
            password: userFormPassword.trim() || undefined,
        };

        if (editingUser) {
            body.verified = userFormVerified;

            router.put(`/users/${editingUser.id}`, body, {
                onSuccess: () => {
                    userDialogOpen = false;
                    savingUser = false;
                },
                onError: (errors) => {
                    userFormErrors = errors;
                    savingUser = false;
                },
            });
        } else {
            router.post('/users', body, {
                onSuccess: () => {
                    userDialogOpen = false;
                    savingUser = false;
                },
                onError: (errors) => {
                    userFormErrors = errors;
                    savingUser = false;
                },
            });
        }
    }

    function deleteUser() {
        if (!deleteConfirmUser) {
            return;
        }

        deletingUser = true;
        router.delete(`/users/${deleteConfirmUser.id}`, {
            onSuccess: () => {
                deleteConfirmUser = null;
                deletingUser = false;
            },
            onError: () => {
                deletingUser = false;
            },
        });
    }
</script>

<AppHead title="Usuarios" />

<div
    class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 pb-10 md:p-6 lg:p-8"
>
    <div
        class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
    >
        <div>
            <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                {headerTitle}
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                {headerDescription}
            </p>
        </div>
        {#if tab === 'users' && canCreateUser}
            <div class="flex items-center gap-2">
                <Button size="sm" variant="success" onclick={openCreateUser}>
                    <Plus class="mr-1.5 size-4" />
                    Nuevo
                </Button>
            </div>
        {/if}
    </div>

    <Tabs bind:value={tab}>
        <TabsList
            variant="line"
            class="h-auto w-full max-w-full flex-wrap justify-start gap-1"
        >
            {#each barTabs as t (t.id)}
                {@const Icon = t.icon}
                <TabsTrigger value={t.id} class="gap-2">
                    <Icon class="size-4" />
                    <span class="truncate">{t.label}</span>
                </TabsTrigger>
            {/each}

            {#if overflowTabs.length > 0}
                <DropdownMenu>
                    <DropdownMenuTrigger>
                        {#snippet child({ props })}
                            <button
                                type="button"
                                class="relative inline-flex h-[calc(100%-1px)] items-center justify-center gap-1.5 rounded-md px-1.5 py-0.5 text-sm font-medium transition-all hover:text-foreground {overflowHasActive
                                    ? 'text-foreground'
                                    : 'text-foreground/60'}"
                                aria-label="Más pestañas"
                                {...props}
                            >
                                <MoreHorizontal class="size-4" />
                                Más
                            </button>
                        {/snippet}
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="min-w-44">
                        {#each overflowTabs as t (t.id)}
                            {@const Icon = t.icon}
                            <DropdownMenuItem
                                class="gap-2"
                                onclick={() => setTab(t.id)}
                            >
                                <Icon class="size-4" />
                                <span class="flex-1">{t.label}</span>
                                {#if tab === t.id}
                                    <Check class="size-4 text-primary" />
                                {/if}
                            </DropdownMenuItem>
                        {/each}
                    </DropdownMenuContent>
                </DropdownMenu>
            {/if}
        </TabsList>

        <TabsContent value="users">
            {#if users.data.length === 0}
                <div
                    class="flex flex-col items-center justify-center gap-4 py-20"
                >
                    <div
                        class="flex size-16 items-center justify-center rounded-2xl bg-secondary"
                    >
                        <UsersIcon
                            class="size-8 text-secondary-foreground-soft"
                        />
                    </div>
                    <div class="text-center">
                        <p class="text-base font-medium text-foreground">
                            No se encontraron usuarios
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            No hay usuarios registrados en el sistema
                        </p>
                    </div>
                </div>
            {:else}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {#each users.data as user (user.id)}
                        <div
                            class="group relative overflow-hidden rounded-xl border p-4 transition-all hover:shadow-sm {userCardClasses(
                                user,
                            )}"
                        >
                            <div class="flex items-center gap-4">
                                <Avatar class="size-10">
                                    <AvatarImage
                                        src={user.avatar ?? undefined}
                                        alt={user.name}
                                    />
                                    <AvatarFallback
                                        class="text-sm font-semibold"
                                        >{getInitials(
                                            user.name,
                                        )}</AvatarFallback
                                    >
                                </Avatar>
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="truncate text-sm font-semibold text-foreground"
                                    >
                                        {user.name}
                                    </p>
                                    <p
                                        class="flex items-center gap-1 truncate text-xs text-muted-foreground"
                                    >
                                        <Mail class="size-3 shrink-0" />
                                        {user.email}
                                    </p>
                                </div>
                                {#if canCreateUser && !user.roles.includes('root')}
                                    <div
                                        class="flex gap-1 opacity-0 transition-opacity group-hover:opacity-100"
                                    >
                                        <button
                                            class="inline-flex size-8 items-center justify-center rounded-lg text-warning transition-colors hover:bg-card-warning hover:text-warning"
                                            onclick={() => openEditUser(user)}
                                            aria-label="Editar"
                                        >
                                            <Pencil class="size-3.5" />
                                        </button>
                                        <button
                                            class="inline-flex size-8 items-center justify-center rounded-lg text-destructive transition-colors hover:bg-card-destructive hover:text-destructive"
                                            onclick={() =>
                                                (deleteConfirmUser = user)}
                                            aria-label="Eliminar"
                                        >
                                            <Trash2 class="size-3.5" />
                                        </button>
                                    </div>
                                {/if}
                            </div>
                            <div class="mt-3 space-y-2.5">
                                <div class="flex flex-wrap gap-1.5">
                                    {#if user.email_verified_at}
                                        <span
                                            class="inline-flex items-center rounded-md border border-card-success-border bg-card-success/40 px-1.5 py-0.5 text-[10px] font-medium text-success-foreground-soft"
                                            >Verificado</span
                                        >
                                    {:else}
                                        <span
                                            class="inline-flex items-center rounded-md border border-card-warning-border bg-card-warning/40 px-1.5 py-0.5 text-[10px] font-medium text-warning-foreground-soft"
                                            >Pendiente</span
                                        >
                                    {/if}
                                    {#if user.has_two_factor}
                                        <span
                                            class="inline-flex items-center gap-0.5 rounded-md border border-card-info-border bg-card-info/40 px-1.5 py-0.5 text-[10px] font-medium text-info-foreground-soft"
                                        >
                                            <ShieldCheck class="size-3" />
                                            2FA
                                        </span>
                                    {/if}
                                    {#if user.has_passkeys}
                                        <span
                                            class="inline-flex items-center gap-0.5 rounded-md border border-card-info-border bg-card-info/40 px-1.5 py-0.5 text-[10px] font-medium text-info-foreground-soft"
                                        >
                                            <KeyRound class="size-3" />
                                            Passkey
                                        </span>
                                    {/if}
                                    {#each user.roles as role (role)}
                                        <span
                                            class="inline-flex items-center rounded-md bg-secondary px-1.5 py-0.5 text-[10px] font-medium text-secondary-foreground-soft"
                                            >{role}</span
                                        >
                                    {/each}
                                </div>
                                <p
                                    class="flex items-center gap-1.5 text-xs text-muted-foreground/70"
                                >
                                    <Calendar class="size-3" />
                                    Miembro desde {new Date(
                                        user.created_at,
                                    ).toLocaleDateString('es-ES', {
                                        year: 'numeric',
                                        month: 'short',
                                    })}
                                </p>
                            </div>
                        </div>
                    {/each}
                </div>

                {#if users.last_page > 1}
                    <div class="pt-2">
                        <Pagination
                            count={users.total}
                            perPage={users.per_page}
                            page={users.current_page}
                            siblingCount={1}
                            onPageChange={(p) => {
                                const query: Record<string, string> = {
                                    page: String(p),
                                };

                                if (tab !== 'users') {
                                    query.tab = tab;
                                }

                                router.get(
                                    index.url({ query }),
                                    {},
                                    { preserveState: true, replace: true },
                                );
                            }}
                        >
                            <PaginationContent>
                                <PaginationItem>
                                    <PaginationPrevious
                                        disabled={users.current_page <= 1}
                                    />
                                </PaginationItem>
                                {#each users.links as link (link.label)}
                                    {#if !/previous|next/i.test(link.label)}
                                        <PaginationItem>
                                            {#if link.label === '...'}
                                                <PaginationEllipsis />
                                            {:else if link.url}
                                                {@const pageNum = Number(
                                                    link.label,
                                                )}
                                                <PaginationLink
                                                    page={{
                                                        value: pageNum,
                                                        type: 'page',
                                                    }}
                                                    isActive={pageNum ===
                                                        users.current_page}
                                                >
                                                    {pageNum}
                                                </PaginationLink>
                                            {/if}
                                        </PaginationItem>
                                    {/if}
                                {/each}
                                <PaginationItem>
                                    <PaginationNext
                                        disabled={users.current_page >=
                                            users.last_page}
                                    />
                                </PaginationItem>
                            </PaginationContent>
                        </Pagination>
                    </div>
                {/if}
            {/if}
        </TabsContent>

        {#if canViewRoles}
            <TabsContent value="roles">
                <RolesPermisos active={tab === 'roles'} />
            </TabsContent>
        {/if}

        {#if canViewActivity}
            <TabsContent value="activity">
                <ActivityOverview active={tab === 'activity'} />
            </TabsContent>
        {/if}

        {#if canManageTokens}
            <TabsContent value="token">
                <Token active={tab === 'token'} />
            </TabsContent>
        {/if}

        {#if canManageVerification}
            <TabsContent value="verification">
                <Verification active={tab === 'verification'} />
            </TabsContent>
        {/if}
    </Tabs>
</div>

<Dialog
    open={userDialogOpen}
    onOpenChange={(o) => {
        if (!o) {
            editingUser = null;
            userFormErrors = {};
        }

        userDialogOpen = o;
    }}
>
    <DialogContent class="sm:max-w-lg">
        <DialogHeader>
            <DialogTitle
                >{editingUser ? 'Editar usuario' : 'Crear usuario'}</DialogTitle
            >
            <DialogDescription>
                {editingUser
                    ? 'Modifica los datos del usuario.'
                    : 'Rellena los campos para crear un nuevo usuario.'}
            </DialogDescription>
        </DialogHeader>
        <div class="space-y-4 py-2">
            <div class="space-y-2">
                <Label for="user-name">Nombre</Label>
                <Input
                    id="user-name"
                    placeholder="Nombre completo"
                    bind:value={userFormName}
                    class={userFormErrors.name ? 'border-destructive' : ''}
                />
                {#if userFormErrors.name}
                    <p class="text-xs text-destructive">
                        {userFormErrors.name}
                    </p>
                {/if}
            </div>
            <div class="space-y-2">
                <Label for="user-email">Email</Label>
                <Input
                    id="user-email"
                    type="email"
                    placeholder="correo@ejemplo.com"
                    bind:value={userFormEmail}
                    class={userFormErrors.email ? 'border-destructive' : ''}
                />
                {#if userFormErrors.email}
                    <p class="text-xs text-destructive">
                        {userFormErrors.email}
                    </p>
                {/if}
            </div>
            <div class="space-y-2">
                <Label for="user-password"
                    >{editingUser
                        ? 'Nueva contraseña (dejar vacío para mantener)'
                        : 'Contraseña'}</Label
                >
                <Input
                    id="user-password"
                    type="password"
                    placeholder={editingUser
                        ? 'Sin cambios'
                        : 'Mínimo 8 caracteres'}
                    bind:value={userFormPassword}
                    class={userFormErrors.password ? 'border-destructive' : ''}
                />
                {#if userFormErrors.password}
                    <p class="text-xs text-destructive">
                        {userFormErrors.password}
                    </p>
                {/if}
            </div>
            {#if editingUser && canCreateUser}
                <div
                    class="flex items-center gap-3 rounded-lg border bg-accent/30 p-3"
                >
                    <Checkbox
                        id="user-verified"
                        bind:checked={userFormVerified}
                    />
                    <Label
                        for="user-verified"
                        class="cursor-pointer text-sm font-medium"
                    >
                        Email verificado
                    </Label>
                </div>
            {/if}

            <div class="space-y-2">
                <Label>Rol</Label>
                {#if rolesLoading}
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        {#each [1, 2, 3, 4] as _, i (i)}
                            <div
                                class="flex items-center gap-3 rounded-xl border p-3"
                            >
                                <Skeleton class="size-9 shrink-0 rounded-lg" />
                                <div class="min-w-0 flex-1 space-y-1.5">
                                    <Skeleton class="h-4 w-24" />
                                    <Skeleton class="h-3 w-16" />
                                </div>
                            </div>
                        {/each}
                    </div>
                {:else if filteredRoles.length === 0}
                    {#if rootCount > 0}
                        <p
                            class="py-4 text-center text-sm text-muted-foreground"
                        >
                            Ya existe un usuario root. El rol root solo puede
                            asignarse desde la base de datos.
                        </p>
                    {:else}
                        <p
                            class="py-4 text-center text-sm text-muted-foreground"
                        >
                            No hay roles disponibles. Crea uno en la pestaña
                            Roles.
                        </p>
                    {/if}
                {:else}
                    {#if isLastRoot}
                        <p
                            class="rounded-lg border border-warning/30 bg-card-warning px-3 py-2 text-xs text-warning-foreground-soft"
                        >
                            No puedes cambiar el rol del único usuario root.
                        </p>
                    {/if}
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        {#each filteredRoles as role (role.name)}
                            <button
                                type="button"
                                onclick={() => (selectedRole = role.name)}
                                disabled={isLastRoot}
                                class="flex w-full items-center gap-3 rounded-xl border p-3 text-left transition-all disabled:cursor-not-allowed disabled:opacity-60 {selectedRole ===
                                role.name
                                    ? roleSelectedCard(role.name)
                                    : roleCard(role.name)}"
                            >
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg {roleIcon(
                                        role.name,
                                    )}"
                                >
                                    <Shield class="size-4.5" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="text-sm font-semibold text-foreground"
                                    >
                                        {formatRoleName(role.name)}
                                    </p>
                                    <p class="text-xs text-muted-foreground/70">
                                        {role.permissions.length}
                                        {role.permissions.length === 1
                                            ? 'permiso'
                                            : 'permisos'}
                                    </p>
                                </div>
                                {#if selectedRole === role.name}
                                    <div
                                        class="flex size-5 shrink-0 items-center justify-center rounded-full bg-primary"
                                    >
                                        <svg
                                            class="size-3 text-primary-foreground"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="3"
                                            ><polyline
                                                points="20 6 9 17 4 12"
                                            /></svg
                                        >
                                    </div>
                                {/if}
                            </button>
                        {/each}
                    </div>
                {/if}
            </div>
            {#if userFormErrors.general}
                <p class="text-xs text-destructive">{userFormErrors.general}</p>
            {/if}
        </div>
        <DialogFooter>
            <Button
                variant="outline"
                onclick={() => (userDialogOpen = false)}
                disabled={savingUser}>Cancelar</Button
            >
            <Button
                variant={editingUser ? 'info' : 'success'}
                onclick={saveUser}
                disabled={savingUser}
            >
                {savingUser
                    ? 'Guardando...'
                    : editingUser
                      ? 'Guardar cambios'
                      : 'Crear usuario'}
            </Button>
        </DialogFooter>
    </DialogContent>
</Dialog>

<Dialog
    open={deleteConfirmUser !== null}
    onOpenChange={(o) => {
        if (!o) {
            deleteConfirmUser = null;
        }
    }}
>
    <DialogContent class="sm:max-w-sm">
        <DialogHeader>
            <DialogTitle>Eliminar usuario</DialogTitle>
            <DialogDescription>
                ¿Estás seguro de eliminar a <strong
                    >{deleteConfirmUser?.name ?? ''}</strong
                >? Esta acción no se puede deshacer.
            </DialogDescription>
        </DialogHeader>
        <DialogFooter>
            <Button
                variant="outline"
                onclick={() => (deleteConfirmUser = null)}
                disabled={deletingUser}>Cancelar</Button
            >
            <Button
                variant="destructive"
                onclick={deleteUser}
                disabled={deletingUser}
            >
                {deletingUser ? 'Eliminando...' : 'Eliminar'}
            </Button>
        </DialogFooter>
    </DialogContent>
</Dialog>
