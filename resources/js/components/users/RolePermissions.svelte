<script lang="ts">
    import { usePage } from '@inertiajs/svelte';
    import { Check, Pencil, Plus, Shield, Trash2, X } from '@lucide/svelte';
    import { Button } from '@/components/ui/button';
    import { Checkbox } from '@/components/ui/checkbox';
    import {
        Dialog,
        DialogContent,
        DialogDescription,
        DialogFooter,
        DialogHeader,
        DialogTitle,
        DialogTrigger,
    } from '@/components/ui/dialog';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { ScrollArea } from '@/components/ui/scroll-area';
    import Skeleton from '@/components/ui/skeleton/skeleton.svelte';

    interface RoleItem {
        id: number;
        name: string;
        guard_name: string;
        permissions: string[];
        created_at: string;
    }

    interface PermissionItem {
        id: number;
        name: string;
        guard_name: string;
    }

    let { active = false }: { active?: boolean } = $props();

    let roles = $state<RoleItem[]>([]);
    let allPermissions = $state<PermissionItem[]>([]);
    let rolesLoading = $state(false);

    let roleDialogOpen = $state(false);
    let editingRole = $state<RoleItem | null>(null);
    let roleName = $state('');
    let roleNameError = $state('');
    let selectedPermissions = $state<string[]>([]);
    let savingRole = $state(false);

    let deleteConfirmRole = $state<RoleItem | null>(null);
    let deletingRole = $state(false);

    let permsDialogOpen = $state(false);
    let permsLoading = $state(false);

    let newPermissionName = $state('');
    let creatingPermission = $state(false);
    let permError = $state('');

    let editingPermission = $state<PermissionItem | null>(null);
    let editPermName = $state('');
    let savingPermission = $state(false);

    let deleteConfirmPerm = $state<PermissionItem | null>(null);
    let deletingPerm = $state(false);

    let isEditingRoot = $derived(editingRole?.name === 'root');

    const page = usePage();

    const userRoles = $derived<string[]>(
        Array.isArray(page.props.auth.roles) ? page.props.auth.roles : [],
    );
    const userPermissions = $derived<string[]>(
        Array.isArray(page.props.auth.permissions)
            ? page.props.auth.permissions
            : [],
    );

    const canCreateRole = $derived(userPermissions.includes('create-roles'));
    const canEditRole = $derived(userPermissions.includes('edit-roles'));
    const canDeleteRole = $derived(userPermissions.includes('delete-roles'));
    const canManagePermissions = $derived(
        userRoles.some((role) => ['root', 'super-admin'].includes(role)),
    );

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

    function roleIcon(name: string) {
        const styles: Record<string, string> = {
            'super-admin': 'bg-card-primary/80 text-primary',
            admin: 'bg-card-info/80 text-info',
            user: 'bg-card-success/80 text-success',
        };

        return styles[name] || 'bg-card-primary/80 text-primary';
    }

    async function loadRoles() {
        rolesLoading = true;

        try {
            const [rolesRes, permsRes] = await Promise.all([
                fetch('/admin/roles'),
                fetch('/admin/permissions'),
            ]);
            roles = await rolesRes.json();
            allPermissions = await permsRes.json();
        } catch {
            roles = [];
            allPermissions = [];
        } finally {
            rolesLoading = false;
        }
    }

    function openEditRole(role: RoleItem) {
        editingRole = role;
        roleName = role.name;
        roleNameError = '';
        selectedPermissions = [...role.permissions];
        roleDialogOpen = true;
        loadPermissionsOnly();
    }

    async function loadPermissionsOnly() {
        permsLoading = true;

        try {
            const permsRes = await fetch('/admin/permissions');
            allPermissions = await permsRes.json();
        } catch {
            allPermissions = [];
        } finally {
            permsLoading = false;
        }
    }

    function togglePermission(permName: string) {
        if (selectedPermissions.includes(permName)) {
            selectedPermissions = selectedPermissions.filter(
                (p) => p !== permName,
            );
        } else {
            selectedPermissions = [...selectedPermissions, permName];
        }
    }

    async function saveRole() {
        roleNameError = '';

        if (!roleName.trim()) {
            roleNameError = 'El nombre del rol es obligatorio';

            return;
        }

        savingRole = true;

        try {
            const url = editingRole
                ? `/admin/roles/${editingRole.id}`
                : '/admin/roles';
            const method = editingRole ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name=csrf-token]')
                            ?.getAttribute('content') ?? '',
                },
                body: JSON.stringify({
                    name: roleName.trim(),
                    permissions: selectedPermissions,
                }),
            });

            if (!res.ok) {
                return;
            }

            roleDialogOpen = false;
            await loadRoles();
        } catch {
            roleNameError = 'Error de conexión';
        } finally {
            savingRole = false;
        }
    }

    async function createPermission() {
        permError = '';

        if (!newPermissionName.trim()) {
            return;
        }

        creatingPermission = true;

        try {
            const res = await fetch('/admin/permissions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name=csrf-token]')
                            ?.getAttribute('content') ?? '',
                },
                body: JSON.stringify({ name: newPermissionName.trim() }),
            });

            if (!res.ok) {
                const err = await res.json();
                permError = err.errors?.name?.[0] || 'Error al crear permiso';

                return;
            }

            newPermissionName = '';
            permError = '';
            await loadRoles();
        } catch {
            permError = 'Error de conexión';
        } finally {
            creatingPermission = false;
        }
    }

    async function savePermission() {
        if (!editingPermission) {
            return;
        }

        permError = '';

        if (!editPermName.trim()) {
            return;
        }

        savingPermission = true;

        try {
            const res = await fetch(
                `/admin/permissions/${editingPermission.id}`,
                {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN':
                            document
                                .querySelector('meta[name=csrf-token]')
                                ?.getAttribute('content') ?? '',
                    },
                    body: JSON.stringify({ name: editPermName.trim() }),
                },
            );

            if (!res.ok) {
                const err = await res.json();
                permError =
                    err.errors?.name?.[0] || 'Error al actualizar permiso';

                return;
            }

            editingPermission = null;
            editPermName = '';
            permError = '';
            await loadRoles();
        } catch {
            permError = 'Error de conexión';
        } finally {
            savingPermission = false;
        }
    }

    async function deletePermission(perm: PermissionItem) {
        deletingPerm = true;

        try {
            const res = await fetch(`/admin/permissions/${perm.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name=csrf-token]')
                            ?.getAttribute('content') ?? '',
                },
            });

            if (res.ok) {
                deleteConfirmPerm = null;
                await loadRoles();
            }
        } finally {
            deletingPerm = false;
        }
    }

    async function deleteRole(role: RoleItem) {
        deletingRole = true;

        try {
            const res = await fetch(`/admin/roles/${role.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name=csrf-token]')
                            ?.getAttribute('content') ?? '',
                },
            });

            if (res.ok) {
                deleteConfirmRole = null;
                await loadRoles();
            }
        } finally {
            deletingRole = false;
        }
    }

    $effect(() => {
        if (active && roles.length === 0 && !rolesLoading) {
            loadRoles();
        }
    });
</script>

<div
    class="flex items-center justify-between rounded-xl bg-secondary/60 p-4 backdrop-blur-xl"
>
    <p class="text-sm text-muted-foreground">
        <span class="font-semibold text-foreground">{roles.length}</span>
        {roles.length === 1 ? 'rol configurado' : 'roles configurados'}
        <span class="mx-2 text-muted-foreground/30">|</span>
        <span class="font-semibold text-foreground"
            >{allPermissions.length}</span
        >
        {allPermissions.length === 1 ? 'permiso' : 'permisos'}
    </p>
    <div class="flex gap-2">
        {#if canManagePermissions}
            <Button
                size="sm"
                variant="outline"
                onclick={() => (permsDialogOpen = true)}
            >
                <Shield class="mr-1.5 size-4" />
                Permisos
            </Button>
        {/if}
        <Dialog
            open={roleDialogOpen}
            onOpenChange={(o) => {
                if (o && !editingRole) {
                    roleName = '';
                    roleNameError = '';
                    selectedPermissions = [];
                    loadPermissionsOnly();
                }

                if (!o) {
                    editingRole = null;
                }

                roleDialogOpen = o;
            }}
        >
            {#if canCreateRole}
                <DialogTrigger>
                    <Button size="sm" variant="success">
                        <Plus class="mr-1.5 size-4" />
                        Nuevo rol
                    </Button>
                </DialogTrigger>
            {/if}
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle
                        >{editingRole
                            ? isEditingRoot
                                ? 'Rol root'
                                : 'Editar rol'
                            : 'Crear rol'}</DialogTitle
                    >
                    <DialogDescription>
                        {isEditingRoot
                            ? 'Los permisos del rol root son fijos y no pueden modificarse.'
                            : editingRole
                              ? 'Modifica el nombre y los permisos del rol.'
                              : 'Define un nombre y asigna los permisos para el nuevo rol.'}
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4 py-2">
                    <div class="space-y-2">
                        <Label for="role-name">Nombre del rol</Label>
                        <Input
                            id="role-name"
                            placeholder="ej. editor"
                            bind:value={roleName}
                            class={roleNameError ? 'border-destructive' : ''}
                        />
                        {#if roleNameError}
                            <p class="text-xs text-destructive">
                                {roleNameError}
                            </p>
                        {/if}
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label>Permisos</Label>
                            <span class="text-xs text-muted-foreground"
                                >{allPermissions.length} permisos</span
                            >
                            {#if isEditingRoot}
                                <span
                                    class="inline-flex items-center rounded-md border border-warning/30 bg-card-warning px-1.5 py-0.5 text-[10px] font-medium text-warning-foreground-soft"
                                    >Bloqueado</span
                                >
                            {/if}
                        </div>
                        <ScrollArea
                            class="h-64 rounded-xl border bg-accent/30 p-2"
                        >
                            {#if permsLoading}
                                <div class="space-y-1.5 p-1">
                                    {#each [1, 2, 3, 4] as _, i (i)}
                                        <div
                                            class="flex items-center gap-3 px-3 py-2.5"
                                        >
                                            <Skeleton
                                                class="size-4 shrink-0 rounded-[4px]"
                                            />
                                            <div
                                                class="min-w-0 flex-1 space-y-1.5"
                                            >
                                                <Skeleton class="h-4 w-28" />
                                                <Skeleton class="h-3 w-16" />
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            {:else}
                                <div class="space-y-0.5">
                                    {#each allPermissions as perm (perm.name)}
                                        <label
                                            class="group flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 transition-all hover:bg-accent/60"
                                        >
                                            <Checkbox
                                                checked={selectedPermissions.includes(
                                                    perm.name,
                                                )}
                                                onCheckedChange={() =>
                                                    togglePermission(perm.name)}
                                                class="shrink-0"
                                                disabled={isEditingRoot}
                                            />
                                            <div class="min-w-0 flex-1">
                                                <p
                                                    class="text-sm font-medium truncate"
                                                >
                                                    {formatRoleName(perm.name)}
                                                </p>
                                                <p
                                                    class="text-xs text-muted-foreground/60 truncate"
                                                >
                                                    {perm.name}
                                                </p>
                                            </div>
                                        </label>
                                    {:else}
                                        <p
                                            class="py-8 text-center text-sm text-muted-foreground"
                                        >
                                            No hay permisos disponibles
                                        </p>
                                    {/each}
                                </div>
                            {/if}
                        </ScrollArea>
                    </div>
                </div>
                <DialogFooter>
                    <Button
                        variant="outline"
                        onclick={() => (roleDialogOpen = false)}
                        disabled={savingRole}>Cancelar</Button
                    >
                    <Button
                        variant={editingRole ? 'info' : 'success'}
                        onclick={saveRole}
                        disabled={savingRole || isEditingRoot}
                    >
                        {savingRole
                            ? 'Guardando...'
                            : isEditingRoot
                              ? 'Guardar'
                              : editingRole
                                ? 'Guardar cambios'
                                : 'Crear rol'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</div>

<Dialog
    open={permsDialogOpen}
    onOpenChange={(o) => {
        permsDialogOpen = o;
    }}
>
    <DialogContent class="sm:max-w-lg">
        <DialogHeader>
            <DialogTitle>Gestionar permisos</DialogTitle>
            <DialogDescription>
                Crea, edita o elimina permisos del sistema
            </DialogDescription>
        </DialogHeader>
        <div class="space-y-4 py-2">
            {#if editingPermission}
                <div
                    class="flex items-center gap-2 rounded-lg border bg-warning/10 p-2"
                >
                    <Input
                        placeholder="Editar nombre del permiso"
                        bind:value={editPermName}
                        class="h-8 text-sm flex-1"
                        onkeydown={(e) => {
                            if (e.key === 'Enter') {
                                savePermission();
                            }

                            if (e.key === 'Escape') {
                                editingPermission = null;
                                editPermName = '';
                                permError = '';
                            }
                        }}
                    />
                    <button
                        class="inline-flex size-7 items-center justify-center rounded-md text-info transition-colors hover:bg-card-info"
                        onclick={savePermission}
                        disabled={savingPermission}
                        aria-label="Guardar"
                    >
                        <Check class="size-4" />
                    </button>
                    <button
                        class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent"
                        onclick={() => {
                            editingPermission = null;
                            editPermName = '';
                            permError = '';
                        }}
                        aria-label="Cancelar"
                    >
                        <X class="size-4" />
                    </button>
                </div>
            {:else}
                <div
                    class="flex items-center gap-2 rounded-lg border bg-accent/30 p-2"
                >
                    <Input
                        placeholder="ej. view-reports"
                        bind:value={newPermissionName}
                        class="h-8 text-sm flex-1"
                        onkeydown={(e) => {
                            if (e.key === 'Enter') {
                                createPermission();
                            }
                        }}
                    />
                    <button
                        class="inline-flex size-7 shrink-0 items-center justify-center rounded-md text-success transition-colors hover:bg-card-success"
                        onclick={createPermission}
                        disabled={creatingPermission}
                        aria-label="Crear permiso"
                    >
                        {#if creatingPermission}
                            <svg
                                class="size-3.5 animate-spin"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                ><circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                /><path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                                /></svg
                            >
                        {:else}
                            <Plus class="size-4" />
                        {/if}
                    </button>
                </div>
            {/if}

            {#if permError}
                <p class="text-xs text-destructive">{permError}</p>
            {/if}

            <ScrollArea class="h-72 rounded-xl border bg-accent/30 p-2">
                {#if permsLoading}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-1">
                        {#each [1, 2, 3, 4] as _, i (i)}
                            <div
                                class="flex items-center gap-2 rounded-lg px-3 py-2.5"
                            >
                                <Skeleton class="size-7 rounded-md shrink-0" />
                                <div class="min-w-0 flex-1 space-y-1">
                                    <Skeleton class="h-3.5 w-24" />
                                    <Skeleton class="h-2.5 w-14" />
                                </div>
                            </div>
                        {/each}
                    </div>
                {:else}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                        {#each allPermissions as perm (perm.name)}
                            <div
                                class="group flex items-center gap-2.5 rounded-lg px-3 py-2.5 transition-all hover:bg-accent/60"
                            >
                                <div
                                    class="flex size-7 shrink-0 items-center justify-center rounded-md bg-accent"
                                >
                                    <Shield
                                        class="size-3.5 text-accent-foreground"
                                    />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="text-sm font-medium truncate text-foreground"
                                    >
                                        {formatRoleName(perm.name)}
                                    </p>
                                    <p
                                        class="text-xs text-muted-foreground/60 truncate"
                                    >
                                        {perm.name}
                                    </p>
                                </div>
                                <div
                                    class="flex gap-0.5 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        class="inline-flex size-6 items-center justify-center rounded-md text-warning transition-colors hover:bg-card-warning hover:text-warning"
                                        onclick={() => {
                                            editingPermission = perm;
                                            editPermName = perm.name;
                                            permError = '';
                                        }}
                                        aria-label="Editar permiso"
                                    >
                                        <Pencil class="size-3" />
                                    </button>
                                    <button
                                        class="inline-flex size-6 items-center justify-center rounded-md text-destructive transition-colors hover:bg-card-destructive hover:text-destructive"
                                        onclick={() =>
                                            (deleteConfirmPerm = perm)}
                                        aria-label="Eliminar permiso"
                                    >
                                        <Trash2 class="size-3" />
                                    </button>
                                </div>
                            </div>
                        {:else}
                            <div
                                class="col-span-2 py-8 text-center text-sm text-muted-foreground"
                            >
                                No hay permisos. Crea uno arriba.
                            </div>
                        {/each}
                    </div>
                {/if}
            </ScrollArea>
        </div>
        <DialogFooter>
            <Button
                variant="outline"
                onclick={() => {
                    permsDialogOpen = false;
                    editingPermission = null;
                    editPermName = '';
                    permError = '';
                }}>Cerrar</Button
            >
        </DialogFooter>
    </DialogContent>
</Dialog>

<div class="mt-4">
    <Dialog
        open={deleteConfirmRole !== null}
        onOpenChange={(o) => {
            if (!o) {
                deleteConfirmRole = null;
            }
        }}
    >
        {#if rolesLoading}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {#each [1, 2, 3] as _, i (i)}
                    <div class="rounded-xl border p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <Skeleton class="size-9 rounded-lg" />
                                <Skeleton class="h-4 w-24" />
                            </div>
                        </div>
                        <div class="mt-4 space-y-2.5">
                            <Skeleton class="h-3 w-20" />
                            <div class="flex flex-wrap gap-1.5">
                                <Skeleton class="h-5 w-16 rounded-md" />
                                <Skeleton class="h-5 w-20 rounded-md" />
                                <Skeleton class="h-5 w-14 rounded-md" />
                            </div>
                        </div>
                    </div>
                {/each}
            </div>
        {:else if roles.length === 0}
            <div class="flex flex-col items-center justify-center gap-4 py-20">
                <div
                    class="flex size-16 items-center justify-center rounded-2xl bg-secondary"
                >
                    <Shield class="size-8 text-secondary-foreground-soft" />
                </div>
                <div class="text-center">
                    <p class="text-base font-medium text-foreground">
                        No hay roles configurados
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Crea tu primer rol para empezar a gestionar permisos
                    </p>
                </div>
            </div>
        {:else}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {#each roles as role (role.id)}
                    <div
                        class="group relative overflow-hidden rounded-xl border p-5 transition-all hover:shadow-sm {roleCard(
                            role.name,
                        )}"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-9 items-center justify-center rounded-lg {roleIcon(
                                        role.name,
                                    )}"
                                >
                                    <Shield class="size-4.5" />
                                </div>
                                <h3
                                    class="text-sm font-semibold text-foreground"
                                >
                                    {formatRoleName(role.name)}
                                </h3>
                            </div>
                            {#if role.name !== 'root' && (canEditRole || canDeleteRole)}
                                <div
                                    class="flex gap-1 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    {#if canEditRole}
                                        <button
                                            class="inline-flex size-8 items-center justify-center rounded-lg text-warning transition-colors hover:bg-card-warning hover:text-warning"
                                            onclick={() => openEditRole(role)}
                                            aria-label="Editar"
                                        >
                                            <Pencil class="size-3.5" />
                                        </button>
                                    {/if}
                                    {#if canDeleteRole}
                                        <button
                                            class="inline-flex size-8 items-center justify-center rounded-lg text-destructive transition-colors hover:bg-card-destructive hover:text-destructive"
                                            onclick={() =>
                                                (deleteConfirmRole = role)}
                                            aria-label="Eliminar"
                                        >
                                            <Trash2 class="size-3.5" />
                                        </button>
                                    {/if}
                                </div>
                            {/if}
                        </div>
                        <div class="mt-3.5 space-y-2.5">
                            <p class="text-xs text-muted-foreground">
                                {role.permissions.length}
                                {role.permissions.length === 1
                                    ? 'permiso'
                                    : 'permisos'}
                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                {#each role.permissions as perm (perm)}
                                    <span
                                        class="inline-flex items-center rounded-md bg-secondary px-2 py-0.5 text-[11px] font-medium text-secondary-foreground-soft"
                                        >{formatRoleName(perm)}</span
                                    >
                                {/each}
                            </div>
                        </div>
                    </div>
                {/each}
            </div>
        {/if}

        <DialogContent class="sm:max-w-sm">
            <DialogHeader>
                <DialogTitle>Eliminar rol</DialogTitle>
                <DialogDescription>
                    ¿Estás seguro de eliminar el rol <strong
                        >{formatRoleName(deleteConfirmRole?.name ?? '')}</strong
                    >? Esta acción no se puede deshacer.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button
                    variant="outline"
                    onclick={() => (deleteConfirmRole = null)}
                    disabled={deletingRole}>Cancelar</Button
                >
                <Button
                    variant="destructive"
                    onclick={() =>
                        deleteConfirmRole && deleteRole(deleteConfirmRole)}
                    disabled={deletingRole}
                >
                    {deletingRole ? 'Eliminando...' : 'Eliminar'}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <Dialog
        open={deleteConfirmPerm !== null}
        onOpenChange={(o) => {
            if (!o) {
                deleteConfirmPerm = null;
            }
        }}
    >
        <DialogContent class="sm:max-w-sm">
            <DialogHeader>
                <DialogTitle>Eliminar permiso</DialogTitle>
                <DialogDescription>
                    ¿Estás seguro de eliminar el permiso <strong
                        >{formatRoleName(deleteConfirmPerm?.name ?? '')}</strong
                    >? Todos los roles perderán este permiso.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button
                    variant="outline"
                    onclick={() => (deleteConfirmPerm = null)}
                    disabled={deletingPerm}>Cancelar</Button
                >
                <Button
                    variant="destructive"
                    onclick={() =>
                        deleteConfirmPerm &&
                        deletePermission(deleteConfirmPerm)}
                    disabled={deletingPerm}
                >
                    {deletingPerm ? 'Eliminando...' : 'Eliminar'}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</div>
