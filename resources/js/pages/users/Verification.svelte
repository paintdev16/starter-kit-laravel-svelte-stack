<script lang="ts">
    import { Shield, ShieldCheck, ShieldX } from '@lucide/svelte';
    import { Button } from '@/components/ui/button';
    import Skeleton from '@/components/ui/skeleton/skeleton.svelte';

    interface UserItem {
        id: number;
        name: string;
        email: string;
        verified: boolean;
        email_verified_at: string | null;
        roles: string[];
        created_at: string;
    }

    let { active = false, canManageVerification = false }: { active?: boolean; canManageVerification?: boolean } = $props();

    let users = $state<UserItem[]>([]);
    let loading = $state(false);
    let toggling = $state<number | null>(null);
    let hasLoaded = $state(false);

    async function loadUsers() {
        loading = true;

        try {
            const res = await fetch('/admin/verification');
            users = await res.json();
        } catch {
            users = [];
        } finally {
            loading = false;
        }
    }

    async function toggle(user: UserItem) {
        toggling = user.id;

        try {
            const csrf =
                document
                    .querySelector('meta[name=csrf-token]')
                    ?.getAttribute('content') ?? '';
            const res = await fetch(`/admin/verification/${user.id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
            });

            if (res.ok) {
                const data = await res.json();
                users = users.map((u) =>
                    u.id === user.id ? { ...u, verified: data.verified, email_verified_at: data.email_verified_at } : u,
                );
            }
        } catch {
            // ignore
        } finally {
            toggling = null;
        }
    }

    function formatDate(dateStr: string | null): string {
        if (!dateStr) {
return '—';
}

        return new Date(dateStr).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    $effect(() => {
        if (active && !hasLoaded && !loading) {
            hasLoaded = true;
            loadUsers();
        }
    });
</script>

{#if !canManageVerification}
    <div
        class="flex flex-col items-center justify-center gap-4 rounded-xl border bg-card py-20"
    >
        <Shield class="size-12 text-muted-foreground/30" />
        <div class="text-center">
            <p class="text-base font-medium text-muted-foreground">
                Acceso restringido
            </p>
            <p class="mt-1 text-sm text-muted-foreground/60">
                Solo los usuarios con rol <strong>root</strong>
                o <strong>super-admin</strong> pueden
                gestionar la verificación de correos.
            </p>
        </div>
    </div>
{:else if loading}
    <div class="space-y-3">
        {#each [1, 2, 3, 4] as _, i (i)}
            <Skeleton class="h-14 w-full rounded-xl" />
        {/each}
    </div>
{:else if users.length === 0}
    <div class="flex flex-col items-center justify-center gap-4 py-20">
        <div class="flex size-16 items-center justify-center rounded-2xl bg-secondary">
            <ShieldCheck class="size-8 text-secondary-foreground-soft" />
        </div>
        <div class="text-center">
            <p class="text-base font-medium text-foreground">
                No hay usuarios registrados
            </p>
        </div>
    </div>
{:else}
    <div class="overflow-hidden rounded-xl border">
        <table class="w-full">
            <thead>
                <tr class="border-b bg-accent/30 text-left text-xs font-medium uppercase tracking-wide text-muted-foreground">
                    <th class="px-4 py-3">Usuario</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Roles</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Verificado desde</th>
                    <th class="px-4 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                {#each users as user (user.id)}
                    <tr class="transition-colors hover:bg-accent/20">
                        <td class="px-4 py-3 text-sm font-medium text-foreground">
                            {user.name}
                        </td>
                        <td class="px-4 py-3 text-sm text-muted-foreground">
                            {user.email}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                {#each user.roles as role (role)}
                                    <span class="inline-flex items-center rounded-md bg-secondary px-1.5 py-0.5 text-[10px] font-medium text-secondary-foreground-soft">
                                        {role}
                                    </span>
                                {/each}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            {#if user.verified}
                                <span class="inline-flex items-center gap-1 rounded-md border border-success/30 bg-card-success px-1.5 py-0.5 text-[10px] font-medium text-success-foreground-soft">
                                    <ShieldCheck class="size-3" />
                                    Verificado
                                </span>
                            {:else}
                                <span class="inline-flex items-center gap-1 rounded-md border border-warning/30 bg-card-warning px-1.5 py-0.5 text-[10px] font-medium text-warning-foreground-soft">
                                    <ShieldX class="size-3" />
                                    Pendiente
                                </span>
                            {/if}
                        </td>
                        <td class="px-4 py-3 text-xs text-muted-foreground">
                            {formatDate(user.email_verified_at)}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Button
                                size="sm"
                                variant={user.verified ? 'outline' : 'default'}
                                onclick={() => toggle(user)}
                                disabled={toggling === user.id || user.roles.includes('super-admin')}
                            >
                                {toggling === user.id
                                    ? 'Cambiando...'
                                    : user.roles.includes('super-admin')
                                      ? 'Protegido'
                                      : user.verified
                                        ? 'Desmarcar'
                                        : 'Verificar'}
                            </Button>
                        </td>
                    </tr>
                {/each}
            </tbody>
        </table>
    </div>
{/if}
