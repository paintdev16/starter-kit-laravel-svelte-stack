<script lang="ts">
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
    import { ScrollArea } from '@/components/ui/scroll-area';
    import Skeleton from '@/components/ui/skeleton/skeleton.svelte';
    import { Pencil, Plus, Shield, Trash2 } from '@lucide/svelte';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Button } from '@/components/ui/button';

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

    let {
        open,
        onOpenChange,
        roles,
        allPermissions,
        rolesLoading,
        permsLoading,
        onLoadRoles,
    }: {
        open: boolean;
        onOpenChange: (v: boolean) => void;
        roles: RoleItem[];
        allPermissions: PermissionItem[];
        rolesLoading: boolean;
        permsLoading: boolean;
        onLoadRoles: () => Promise<void>;
    } = $props();

    let editingRole = $state<RoleItem | null>(null);
    let roleName = $state('');
    let roleNameError = $state('');
    let selectedPermissions = $state<string[]>([]);
    let savingRole = $state(false);

    let deleteConfirmRole = $state<RoleItem | null>(null);
    let deletingRole = $state(false);

    let isEditingRoot = $derived(editingRole?.name === 'root');

    function formatRoleName(name: string): string {
        return name
            .split('-')
            .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
            .join(' ');
    }

    function roleCard(name: string): string {
        const styles: Record<string, string> = {
            'super-admin': 'border-card-primary-border bg-card-primary/40 hover:border-card-primary-border/70 hover:bg-card-primary/60',
            admin: 'border-card-info-border bg-card-info/40 hover:border-card-info-border/70 hover:bg-card-info/60',
            user: 'border-card-success-border bg-card-success/40 hover:border-card-success-border/70 hover:bg-card-success/60',
        };
        return styles[name] || 'border-card-primary-border bg-card-primary/40 hover:border-card-primary-border/70 hover:bg-card-primary/60';
    }

    function roleIcon(name: string): string {
        const styles: Record<string, string> = {
            'super-admin': 'bg-card-primary/80 text-primary',
            admin: 'bg-card-info/80 text-info',
            user: 'bg-card-success/80 text-success',
        };
        return styles[name] || 'bg-card-primary/80 text-primary';
    }

    function openEditRole(role: RoleItem) {
        editingRole = role;
        roleName = role.name;
        roleNameError = '';
        selectedPermissions = [...role.permissions];
        onOpenChange(true);
    }

    async function saveRole() {
        roleNameError = '';
        if (!roleName.trim()) {
            roleNameError = 'El nombre del rol es obligatorio';
            return;
        }
        savingRole = true;
        try {
            const url = editingRole ? `/admin/roles/${editingRole.id}` : '/admin/roles';
            const method = editingRole ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') ?? '',
                },
                body: JSON.stringify({ name: roleName.trim(), permissions: selectedPermissions }),
            });
            if (!res.ok) {
                const err = await res.json();
                const allErrors = err.errors ? Object.values(err.errors).flat() : [];
                roleNameError = roleName.trim() ? allErrors[0] || 'Error al guardar el rol' : 'El nombre del rol es obligatorio';
                return;
            }
            onOpenChange(false);
            await onLoadRoles();
        } catch {
            roleNameError = 'Error de conexión';
        } finally {
            savingRole = false;
        }
    }

    async function deleteRole(role: RoleItem) {
        deletingRole = true;
        try {
            const res = await fetch(`/admin/roles/${role.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') ?? '',
                },
            });
            if (res.ok) {
                deleteConfirmRole = null;
                await onLoadRoles();
            }
        } finally {
            deletingRole = false;
        }
    }
</script>

<div class="mt-4">
    {#if rolesLoading}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {#each [1, 2, 3] as _}
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
            {/each})
        </div>
    {:else if roles.length === 0}
        <div class="flex flex-col items-center justify-center gap-4 py-20">
            <div class="flex size-16 items-center justify-center rounded-2xl bg-secondary">
                <Shield class="size-8 text-secondary-foreground-soft" />
            </div>
            <div class="text-center">
                <p class="text-base font-medium text-foreground">No hay roles configurados</p>
                <p class="mt-1 text-sm text-muted-foreground">Crea tu primer rol para empezar a gestionar permisos</p>
            </div>
        </div>
    {:else}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {#each roles as role (role.id)}
                <div
                    class="group relative overflow-hidden rounded-xl border p-5 transition-all hover:shadow-sm {roleCard(role.name)}"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-lg {roleIcon(role.name)}">
                                <Shield class="size-4.5" />
                            </div>
                            <h3 class="text-sm font-semibold text-foreground">{formatRoleName(role.name)}</h3>
                        </div>
                        {#if role.name !== 'root'}
                            <div class="flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                <button
                                    class="inline-flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                    onclick={() => openEditRole(role)}
                                    aria-label="Editar"
                                >
                                    <Pencil class="size-3.5" />
                                </button>
                                <button
                                    class="inline-flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-card-destructive hover:text-destructive"
                                    onclick={() => (deleteConfirmRole = role)}
                                    aria-label="Eliminar"
                                >
                                    <Trash2 class="size-3.5" />
                                </button>
                            </div>
                        {/if}
                    </div>
                    <div class="mt-3.5 space-y-2.5">
                        <p class="text-xs text-muted-foreground">{role.permissions.length} {role.permissions.length === 1 ? 'permiso' : 'permisos'}</p>
                        <div class="flex flex-wrap gap-1.5">
                            {#each role.permissions as perm}
                                <span class="inline-flex items-center rounded-md bg-secondary px-2 py-0.5 text-[11px] font-medium text-secondary-foreground-soft">{formatRoleName(perm)}</span>
                            {/each}
                        </div>
                    </div>
                </div>
            {/each}
        </div>
    {/if}

    <Dialog
        open={open}
        onOpenChange={onOpenChange}
    >
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>
                    {editingRole
                        ? isEditingRoot
                            ? 'Rol root'
                            : 'Editar rol'
                        : 'Crear rol'}
                </DialogTitle>
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
                        <p class="text-xs text-destructive">{roleNameError}</p>
                    {/if}
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label>Permisos</Label>
                        <span class="text-xs text-muted-foreground">{allPermissions.length} permisos</span>
                        {#if isEditingRoot}
                            <span class="inline-flex items-center rounded-md border border-warning/30 bg-card-warning px-1.5 py-0.5 text-[10px] font-medium text-warning-foreground-soft">Bloqueado</span>
                        {/if}
                    </div>
                    <ScrollArea class="h-64 rounded-xl border bg-accent/30 p-2">
                        {#if rolesLoading || permsLoading}
                            <div class="space-y-1.5 p-1">
                                {#each [1, 2, 3, 4] as _}
                                    <div class="flex items-center gap-3 px-3 py-2.5">
                                        <Skeleton class="size-4 shrink-0 rounded-[4px]" />
                                        <div class="min-w-0 flex-1 space-y-1.5">
                                            <Skeleton class="h-4 w-28" />
                                            <Skeleton class="h-3 w-16" />
                                        </div>
                                    </div>
                                {/each})
                            </div>
                        {:else}
                            <div class="space-y-0.5">
                                {#each allPermissions as perm}
                                    <label class="group flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 transition-all hover:bg-accent/60">
                                        <Checkbox
                                            checked={selectedPermissions.includes(perm.name)}
                                            onCheckedChange={() => {
                                                if (selectedPermissions.includes(perm.name)) {
                                                    selectedPermissions = selectedPermissions.filter((p) => p !== perm.name);
                                                } else {
                                                    selectedPermissions = [...selectedPermissions, perm.name];
                                                }
                                            }}
                                            class="shrink-0"
                                            disabled={isEditingRoot}
                                        />
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium truncate">{formatRoleName(perm.name)}</p>
                                            <p class="text-xs text-muted-foreground/60 truncate">{perm.name}</p>
                                        </div>
                                    </label>
                                {:else}
                                    <p class="py-8 text-center text-sm text-muted-foreground">No hay permisos disponibles</p>
                                {/each}
                            </div>
                        {/if}
                    </ScrollArea>
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" onclick={() => onOpenChange(false)} disabled={savingRole}>Cancelar</Button>
                <Button onclick={saveRole} disabled={savingRole || isEditingRoot}>
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

    <Dialog
        open={deleteConfirmRole !== null}
        onOpenChange={(o) => {
            if (!o) deleteConfirmRole = null;
        }}
    >
        <DialogContent class="sm:max-w-sm">
            <DialogHeader>
                <DialogTitle>Eliminar rol</DialogTitle>
                <DialogDescription>
                    ¿Estás seguro de eliminar el rol <strong>{formatRoleName(deleteConfirmRole?.name ?? '')}</strong>? Esta acción no se puede deshacer.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" onclick={() => (deleteConfirmRole = null)} disabled={deletingRole}>Cancelar</Button>
                <Button
                    variant="destructive"
                    onclick={() => deleteConfirmRole && deleteRole(deleteConfirmRole)}
                    disabled={deletingRole}
                >
                    {deletingRole ? 'Eliminando...' : 'Eliminar'}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</div>