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
    import { Link, router } from '@inertiajs/svelte';
    import {
        Calendar,
        ClipboardList,
        Eye,
        Globe,
        KeyRound,
        Laptop,
        Mail,
        Monitor,
        Pencil,
        Plus,
        RotateCw,
        Shield,
        ShieldCheck,
        Smartphone,
        Tablet,
        Trash2,
        Tv,
        UsersIcon,
    } from '@lucide/svelte';
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
    import RolesPermisos from '../../components/users/RolePermissions.svelte';
    import Settings from './Settings.svelte';
    import Token from './Token.svelte';
    import Verification from './Verification.svelte';

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
        canManageSocialite = false,
    }: {
        users?: PaginatedData<UserItem>;
        rootCount?: number;
        canViewActivity?: boolean;
        canManageTokens?: boolean;
        canCreateUser?: boolean;
        canManageVerification?: boolean;
        canManageSocialite?: boolean;
    } = $props();

    let tab = $state('users');
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

    let currentDevice = $state<any>(null);
    let deviceLoading = $state(false);
    let activityGroups = $state<any[]>([]);
    let activityLoading = $state(false);

    async function fetchCurrentDevice() {
        deviceLoading = true;

        try {
            const res = await fetch('/activity/current-device', {
                credentials: 'include',
            });

            if (!res.ok) {
                throw new Error(`${res.status} ${res.statusText}`);
            }

            currentDevice = await res.json();
        } catch (e) {
            console.error('fetchCurrentDevice:', e);
            currentDevice = null;
        } finally {
            deviceLoading = false;
        }
    }

    async function fetchActivityGroups() {
        activityLoading = true;

        try {
            const res = await fetch('/activity/grouped', {
                credentials: 'include',
            });

            if (!res.ok) {
                throw new Error(`${res.status} ${res.statusText}`);
            }

            activityGroups = await res.json();
        } catch (e) {
            console.error('fetchActivityGroups:', e);
            activityGroups = [];
        } finally {
            activityLoading = false;
        }
    }

    function formatDate(dateStr: string) {
        return new Date(dateStr).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    $effect(() => {
        if (tab === 'activity') {
            if (currentDevice === null && !deviceLoading) {
                fetchCurrentDevice();
            }

            if (activityGroups.length === 0 && !activityLoading) {
                fetchActivityGroups();
            }
        }
    });
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
                {tab === 'users'
                    ? 'Usuarios'
                    : tab === 'roles'
                      ? 'Roles y permisos'
                      : tab === 'token'
                        ? 'Tokens de API'
                        : tab === 'verification'
                          ? 'Verificación'
                          : tab === 'settings'
                            ? 'Configuración'
                            : 'Actividad'}
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                {#if tab === 'users'}
                    {users.total}
                    {users.total === 1
                        ? 'usuario registrado'
                        : 'usuarios registrados'} en el sistema
                {:else if tab === 'roles'}
                    Administra los roles y permisos del sistema
                {:else if tab === 'token'}
                    Gestiona tokens para integraciones con terceros
                {:else if tab === 'verification'}
                    Gestiona la verificación de correos electrónicos
                {:else if tab === 'settings'}
                    Configura las opciones generales del sistema
                {:else}
                    Historial de actividad de los usuarios
                {/if}
            </p>
        </div>
        {#if tab === 'users' && canCreateUser}
            <div class="flex items-center gap-2">
                <Button size="sm" onclick={openCreateUser}>
                    <Plus class="mr-1.5 size-4" />
                    Nuevo
                </Button>
            </div>
        {/if}
    </div>

    <Tabs bind:value={tab}>
        <TabsList variant="line">
            <TabsTrigger value="users" class="gap-2">
                <UsersIcon class="size-4" />
                Usuarios
            </TabsTrigger>
            <TabsTrigger value="roles" class="gap-2">
                <Shield class="size-4" />
                Roles
            </TabsTrigger>
            <TabsTrigger value="activity" class="gap-2">
                <ClipboardList class="size-4" />
                Actividad
            </TabsTrigger>
            <TabsTrigger value="token" class="gap-2">
                <KeyRound class="size-4" />
                Token
            </TabsTrigger>
            <TabsTrigger value="verification" class="gap-2">
                <ShieldCheck class="size-4" />
                Verificación
            </TabsTrigger>
            {#if canManageSocialite}
                <TabsTrigger value="settings" class="gap-2">
                    <Globe class="size-4" />
                    Configuración
                </TabsTrigger>
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
                            class="group relative overflow-hidden rounded-xl border bg-accent/30 p-4 transition-all hover:bg-accent/50 hover:shadow-sm"
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
                                            class="inline-flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                            onclick={() => openEditUser(user)}
                                            aria-label="Editar"
                                        >
                                            <Pencil class="size-3.5" />
                                        </button>
                                        <button
                                            class="inline-flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-card-destructive hover:text-destructive"
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
                                            class="inline-flex items-center rounded-md border border-success/30 bg-card-success px-1.5 py-0.5 text-[10px] font-medium text-success-foreground-soft"
                                            >Verificado</span
                                        >
                                    {:else}
                                        <span
                                            class="inline-flex items-center rounded-md border border-warning/30 bg-card-warning px-1.5 py-0.5 text-[10px] font-medium text-warning-foreground-soft"
                                            >Pendiente</span
                                        >
                                    {/if}
                                    {#if user.has_two_factor}
                                        <span
                                            class="inline-flex items-center gap-0.5 rounded-md border border-info/30 bg-card-info px-1.5 py-0.5 text-[10px] font-medium text-info-foreground-soft"
                                        >
                                            <ShieldCheck class="size-3" />
                                            2FA
                                        </span>
                                    {/if}
                                    {#if user.has_passkeys}
                                        <span
                                            class="inline-flex items-center gap-0.5 rounded-md border border-info/30 bg-card-info px-1.5 py-0.5 text-[10px] font-medium text-info-foreground-soft"
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
                                router.get(
                                    index.url({
                                        query: {
                                            page: String(p),
                                        },
                                    }),
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

        <TabsContent value="roles">
            <RolesPermisos active={tab === 'roles'} />
        </TabsContent>

        <TabsContent value="token">
            <Token active={tab === 'token'} {canManageTokens} />
        </TabsContent>

        <TabsContent value="verification">
            <Verification active={tab === 'verification'} canManageVerification={canManageVerification} />
        </TabsContent>

        {#if canManageSocialite}
            <TabsContent value="settings">
                <Settings active={tab === 'settings'} />
            </TabsContent>
        {/if}

        <TabsContent value="activity">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-1">
                    <div class="rounded-xl border bg-card p-5">
                        <h3 class="mb-4 text-sm font-semibold text-foreground">
                            Tu dispositivo actual
                        </h3>
                        {#if deviceLoading}
                            <div class="space-y-3">
                                {#each [1, 2, 3, 4] as _, i (i)}
                                    <Skeleton class="h-4 w-full rounded-md" />
                                {/each}
                            </div>
                        {:else if currentDevice}
                            {#each [{ label: 'Navegador', value: currentDevice.browser && currentDevice.browser_version ? `${currentDevice.browser} ${currentDevice.browser_version}` : currentDevice.browser }, { label: 'Sistema operativo', value: currentDevice.os && currentDevice.os_version ? `${currentDevice.os} ${currentDevice.os_version}` : currentDevice.os }, { label: 'Tipo de dispositivo', value: currentDevice.device_type }, { label: 'Marca / Modelo', value: [currentDevice.device_brand, currentDevice.device_model]
                                            .filter(Boolean)
                                            .join(' ') || '—' }, { label: 'Dirección IP', value: currentDevice.ip_address }] as item (item.label)}
                                {#if item.value}
                                    <div
                                        class="flex items-center justify-between py-1.5"
                                    >
                                        <span
                                            class="text-xs text-muted-foreground"
                                            >{item.label}</span
                                        >
                                        <span
                                            class="text-xs font-medium text-foreground"
                                            >{item.value}</span
                                        >
                                    </div>
                                {/if}
                            {/each}
                            <div
                                class="mt-3 flex items-center gap-2 text-xs text-muted-foreground"
                            >
                                {#if currentDevice.device_type
                                    ?.toLowerCase()
                                    .includes('smartphone') || currentDevice.device_type
                                        ?.toLowerCase()
                                        .includes('mobile') || currentDevice.device_type
                                        ?.toLowerCase()
                                        .includes('phone')}
                                    <Smartphone class="size-3.5" />
                                {:else if currentDevice.device_type
                                    ?.toLowerCase()
                                    .includes('tablet') || currentDevice.device_type
                                        ?.toLowerCase()
                                        .includes('phablet')}
                                    <Tablet class="size-3.5" />
                                {:else if currentDevice.device_type
                                    ?.toLowerCase()
                                    .includes('tv')}
                                    <Tv class="size-3.5" />
                                {:else if currentDevice.device_type
                                    ?.toLowerCase()
                                    .includes('laptop') || currentDevice.device_type
                                        ?.toLowerCase()
                                        .includes('notebook')}
                                    <Laptop class="size-3.5" />
                                {:else}
                                    <Monitor class="size-3.5" />
                                {/if}
                                <span>Detección basada en User-Agent</span>
                            </div>
                        {:else}
                            <p class="text-sm text-muted-foreground">
                                No se pudo detectar la información del
                                dispositivo.
                            </p>
                        {/if}
                    </div>
                </div>

                <div class="lg:col-span-2">
                    {#if canViewActivity}
                        <div class="rounded-xl border bg-card">
                            <div
                                class="flex items-center justify-between border-b px-5 py-3.5"
                            >
                                <h3
                                    class="text-sm font-semibold text-foreground"
                                >
                                    Actividad por usuario
                                </h3>
                                {#if !activityLoading}
                                    <button
                                        onclick={() => fetchActivityGroups()}
                                        class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent"
                                        aria-label="Recargar"
                                    >
                                        <RotateCw class="size-3.5" />
                                    </button>
                                {/if}
                            </div>
                            {#if activityLoading && activityGroups.length === 0}
                                <div class="space-y-3 p-5">
                                    {#each [1, 2, 3, 4] as _, i (i)}
                                        <div class="flex items-center gap-3">
                                            <Skeleton
                                                class="size-10 shrink-0 rounded-full"
                                            />
                                            <div
                                                class="min-w-0 flex-1 space-y-1.5"
                                            >
                                                <Skeleton
                                                    class="h-4 w-36 rounded-md"
                                                />
                                                <Skeleton
                                                    class="h-3 w-48 rounded-md"
                                                />
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            {:else if activityGroups.length === 0}
                                <div
                                    class="flex flex-col items-center justify-center gap-3 py-16"
                                >
                                    <ClipboardList
                                        class="size-10 text-muted-foreground/30"
                                    />
                                    <p class="text-sm text-muted-foreground">
                                        Aún no hay actividad registrada
                                    </p>
                                </div>
                            {:else}
                                <div class="divide-y">
                                    {#each activityGroups as group, i (group.id ?? group.user_id ?? i)}
                                        <div
                                            class="flex items-center gap-4 px-5 py-4"
                                        >
                                            <div class="relative shrink-0">
                                                <Avatar class="size-10">
                                                    <AvatarFallback
                                                        class="text-xs font-semibold"
                                                        >{getInitials(
                                                            group.user_name,
                                                        )}</AvatarFallback
                                                    >
                                                </Avatar>
                                                {#if group.is_online}
                                                    <span
                                                        class="absolute -right-0.5 -top-0.5 size-3 rounded-full border-2 border-background bg-success"
                                                        title="Conectado"
                                                    ></span>
                                                {:else}
                                                    <span
                                                        class="absolute -right-0.5 -top-0.5 size-3 rounded-full border-2 border-background bg-muted-foreground/40"
                                                        title="Desconectado"
                                                    ></span>
                                                {/if}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <p
                                                        class="text-sm font-semibold text-foreground"
                                                    >
                                                        {group.user_name}
                                                    </p>
                                                    <span
                                                        class="rounded-full bg-accent px-2 py-0.5 text-[10px] font-medium text-muted-foreground"
                                                        >{group.count}
                                                        {group.count === 1
                                                            ? 'acción'
                                                            : 'acciones'}</span
                                                    >
                                                    {#if group.is_online}
                                                        <span
                                                            class="text-[10px] font-medium text-success"
                                                            >En línea</span
                                                        >
                                                    {/if}
                                                </div>
                                                <p
                                                    class="mt-0.5 truncate text-xs text-muted-foreground"
                                                >
                                                    {group.last_action}
                                                </p>
                                                <p
                                                    class="text-xs text-muted-foreground/60"
                                                >
                                                    {formatDate(
                                                        group.last_date,
                                                    )}
                                                </p>
                                                {#if group.last_login}
                                                    <p
                                                        class="text-xs text-primary"
                                                    >
                                                        Último login: {formatDate(
                                                            group.last_login,
                                                        )}
                                                    </p>
                                                {/if}
                                            </div>
                                            <Link
                                                href={`/users/activity?user=${group.user_id}`}
                                                class="shrink-0"
                                            >
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                >
                                                    <Eye
                                                        class="mr-1.5 size-3.5"
                                                    />
                                                    Detalles
                                                </Button>
                                            </Link>
                                        </div>
                                    {/each}
                                </div>
                            {/if}
                        </div>
                    {:else}
                        <div
                            class="flex flex-col items-center justify-center gap-4 rounded-xl border bg-card py-20"
                        >
                            <Shield class="size-12 text-muted-foreground/30" />
                            <div class="text-center">
                                <p
                                    class="text-base font-medium text-muted-foreground"
                                >
                                    Acceso restringido
                                </p>
                                <p
                                    class="mt-1 text-sm text-muted-foreground/60"
                                >
                                    Solo los usuarios con rol <strong
                                        >root</strong
                                    >
                                    o <strong>super-admin</strong> pueden ver el historial
                                    de actividad.
                                </p>
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
        </TabsContent>
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
                <div class="flex items-center gap-3 rounded-lg border bg-accent/30 p-3">
                    <Checkbox id="user-verified" bind:checked={userFormVerified} />
                    <Label for="user-verified" class="cursor-pointer text-sm font-medium">
                        Email verificado
                    </Label>
                </div>
            {/if}

            <div class="space-y-2">
                <Label>Rol</Label>
                {#if rolesLoading}
                    <div class="space-y-2">
                        {#each [1, 2, 3] as _, i (i)}
                            <Skeleton class="h-14 w-full rounded-xl" />
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
                            No puedes cambiar el rol del Ãºnico usuario root.
                        </p>
                    {/if}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
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
            <Button onclick={saveUser} disabled={savingUser}>
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
