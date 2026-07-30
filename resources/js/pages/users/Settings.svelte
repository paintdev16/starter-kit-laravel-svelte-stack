<script lang="ts">
    import { Globe, Pencil, Plus, Trash2 } from '@lucide/svelte';
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
    import Skeleton from '@/components/ui/skeleton/skeleton.svelte';
    import { Switch } from '@/components/ui/switch';

    interface ProviderItem {
        id: number;
        provider: string;
        client_id: string;
        redirect_uri: string;
        enabled: boolean;
        show_on_login: boolean;
        sort: number;
        created_at: string;
    }

    let { active = false }: { active?: boolean } = $props();

    let providers = $state<ProviderItem[]>([]);
    let loading = $state(false);
    let hasLoaded = $state(false);

    let dialogOpen = $state(false);
    let editing = $state<ProviderItem | null>(null);
    let formProvider = $state('');
    let formClientId = $state('');
    let formClientSecret = $state('');
    let formRedirectUri = $state('');
    let formShowOnLogin = $state(true);
    let formErrors = $state<Record<string, string>>({});
    let saving = $state(false);

    let deleteConfirm = $state<ProviderItem | null>(null);
    let deleting = $state(false);

    function csrfToken(): string {
        return document.querySelector('meta[name=csrf-token]')?.getAttribute('content') ?? '';
    }

    const jsonHeaders = (): Record<string, string> => ({
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken(),
    });

    function loadProviders() {
        loading = true;
        fetch('/admin/oauth-providers', { headers: { 'Accept': 'application/json' } })
            .then((r) => r.json())
            .then((data) => (providers = data))
            .catch(() => (providers = []))
            .finally(() => (loading = false));
    }

    function openCreate() {
        editing = null;
        formProvider = '';
        formClientId = '';
        formClientSecret = '';
        formRedirectUri = '';
        formShowOnLogin = true;
        formErrors = {};
        dialogOpen = true;
    }

    function openEdit(p: ProviderItem) {
        editing = p;
        formProvider = p.provider;
        formClientId = p.client_id;
        formClientSecret = '';
        formRedirectUri = p.redirect_uri;
        formShowOnLogin = p.show_on_login;
        formErrors = {};
        dialogOpen = true;
    }

    async function save() {
        formErrors = {};
        const errs: Record<string, string> = {};

        if (!formProvider.trim()) {
errs.provider = 'El nombre del proveedor es obligatorio';
}

        if (!formClientId.trim()) {
errs.client_id = 'El Client ID es obligatorio';
}

        if (!editing && !formClientSecret.trim()) {
errs.client_secret = 'El Client Secret es obligatorio';
}

        if (!formRedirectUri.trim()) {
errs.redirect_uri = 'La URI de redirección es obligatoria';
}

        if (Object.keys(errs).length > 0) {
            formErrors = errs;

            return;
        }

        saving = true;

        const body: Record<string, unknown> = {
            provider: formProvider.trim(),
            client_id: formClientId.trim(),
            redirect_uri: formRedirectUri.trim(),
            enabled: true,
            show_on_login: formShowOnLogin,
        };

        if (!editing) {
            body.client_secret = formClientSecret.trim();
        } else if (formClientSecret.trim()) {
            body.client_secret = formClientSecret.trim();
        }

        try {
            const url = editing ? `/admin/oauth-providers/${editing.id}` : '/admin/oauth-providers';
            const method = editing ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method,
                headers: jsonHeaders(),
                body: JSON.stringify(body),
            });

            if (!res.ok) {
                const err = await res.json();
                formErrors = err.errors || { general: err.message || 'Error al guardar' };

                return;
            }

            dialogOpen = false;
            loadProviders();
        } catch {
            formErrors = { general: 'Error de conexión' };
        } finally {
            saving = false;
        }
    }

    async function toggleShowOnLogin(p: ProviderItem) {
        try {
            const res = await fetch(`/admin/oauth-providers/${p.id}/show-on-login`, {
                method: 'PATCH',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            });

            if (res.ok) {
                const data = await res.json();
                providers = providers.map((pr) =>
                    pr.id === p.id ? { ...pr, show_on_login: data.show_on_login } : pr,
                );
            }
        } catch {
            // ignore
        }
    }

    async function deleteProvider() {
        if (!deleteConfirm) {
 return; 
}

        deleting = true;

        try {
            await fetch(`/admin/oauth-providers/${deleteConfirm.id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            });
            deleteConfirm = null;
            loadProviders();
        } finally {
            deleting = false;
        }
    }

    $effect(() => {
        if (active && !hasLoaded && !loading) {
            hasLoaded = true;
            loadProviders();
        }
    });
</script>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-foreground">Proveedores OAuth</h3>
            <p class="mt-0.5 text-sm text-muted-foreground">
                Gestiona los proveedores de inicio de sesión social.
            </p>
        </div>
        <Button size="sm" onclick={openCreate}>
            <Plus class="mr-1.5 size-4" />
            Añadir
        </Button>
    </div>

    {#if loading}
        <div class="space-y-3">
            {#each [1, 2] as _, i (i)}
                <Skeleton class="h-16 w-full rounded-xl" />
            {/each}
        </div>
    {:else if providers.length === 0}
        <div class="flex flex-col items-center justify-center gap-4 rounded-xl border bg-card py-16">
            <Globe class="size-10 text-muted-foreground/30" />
            <p class="text-sm text-muted-foreground">No hay proveedores OAuth configurados.</p>
        </div>
    {:else}
        <div class="space-y-2">
            {#each providers as p (p.id)}
                <div class="flex items-center gap-4 rounded-xl border bg-accent/30 p-4">
                    <span
                        class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg {p.enabled ? 'bg-success/20 text-success' : 'bg-muted text-muted-foreground'}"
                        title={p.enabled ? 'Habilitado' : 'Deshabilitado'}
                    >
                        {#if p.enabled}
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        {:else}
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        {/if}
                    </span>
                    <Switch checked={p.show_on_login} onCheckedChange={() => toggleShowOnLogin(p)} size="sm" variant="primary" class="bg-destructive-foreground-soft/80" />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-foreground">
                            {p.provider}
                        </p>
                        <p class="truncate text-xs text-muted-foreground">
                            {p.redirect_uri}
                        </p>
                    </div>
                    <button
                        class="inline-flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                        onclick={() => openEdit(p)}
                        aria-label="Editar"
                    >
                        <Pencil class="size-3.5" />
                    </button>
                    <button
                        class="inline-flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-card-destructive hover:text-destructive"
                        onclick={() => (deleteConfirm = p)}
                        aria-label="Eliminar"
                    >
                        <Trash2 class="size-3.5" />
                    </button>
                </div>
            {/each}
        </div>
    {/if}
</div>

<Dialog open={dialogOpen} onOpenChange={(o) => {
 if (!o) {
 dialogOpen = false; formErrors = {}; 
} 
}}>
    <DialogContent class="sm:max-w-lg">
        <DialogHeader>
            <DialogTitle>{editing ? 'Editar proveedor' : 'Añadir proveedor'}</DialogTitle>
            <DialogDescription>
                {editing ? 'Modifica las credenciales del proveedor OAuth.' : 'Registra un nuevo proveedor OAuth.'}
            </DialogDescription>
        </DialogHeader>
        <div class="space-y-4 py-2">
            <div class="space-y-2">
                <Label for="provider-name">Proveedor</Label>
                <Input
                    id="provider-name"
                    placeholder="google, github, microsoft..."
                    bind:value={formProvider}
                    class={formErrors.provider ? 'border-destructive' : ''}
                    disabled={editing !== null}
                />
                {#if formErrors.provider}<p class="text-xs text-destructive">{formErrors.provider}</p>{/if}
            </div>
            <div class="space-y-2">
                <Label for="client-id">Client ID</Label>
                <Input
                    id="client-id"
                    placeholder="123456789-xxx.apps.googleusercontent.com"
                    bind:value={formClientId}
                    class={formErrors.client_id ? 'border-destructive' : ''}
                />
                {#if formErrors.client_id}<p class="text-xs text-destructive">{formErrors.client_id}</p>{/if}
            </div>
            <div class="space-y-2">
                <Label for="client-secret">Client Secret</Label>
                <Input
                    id="client-secret"
                    type="password"
                    placeholder={editing ? 'Dejar vacío para mantener el actual' : 'GOCSPX-xxx'}
                    bind:value={formClientSecret}
                    class={formErrors.client_secret ? 'border-destructive' : ''}
                />
                {#if formErrors.client_secret}<p class="text-xs text-destructive">{formErrors.client_secret}</p>{/if}
            </div>
            <div class="space-y-2">
                <Label for="redirect-uri">Redirect URI</Label>
                <Input
                    id="redirect-uri"
                    placeholder="https://tu-app.com/auth/google/callback"
                    bind:value={formRedirectUri}
                    class={formErrors.redirect_uri ? 'border-destructive' : ''}
                />
                {#if formErrors.redirect_uri}<p class="text-xs text-destructive">{formErrors.redirect_uri}</p>{/if}
            </div>
            <div class="flex items-center gap-2">
                <Checkbox id="show-on-login" bind:checked={formShowOnLogin} />
                <Label for="show-on-login" class="text-sm font-normal">Mostrar en la pantalla de login</Label>
            </div>
            {#if formErrors.general}
                <p class="text-xs text-destructive">{formErrors.general}</p>
            {/if}
        </div>
        <DialogFooter>
            <Button variant="outline" onclick={() => (dialogOpen = false)} disabled={saving}>Cancelar</Button>
            <Button type="button" onclick={save} disabled={saving}>
                {saving ? 'Guardando...' : editing ? 'Guardar cambios' : 'Añadir proveedor'}
            </Button>
        </DialogFooter>
    </DialogContent>
</Dialog>

<Dialog open={deleteConfirm !== null} onOpenChange={(o) => {
 if (!o) {
deleteConfirm = null;
} 
}}>
    <DialogContent class="sm:max-w-sm">
        <DialogHeader>
            <DialogTitle>Eliminar proveedor</DialogTitle>
            <DialogDescription>
                ¿Estás seguro de eliminar <strong>{deleteConfirm?.provider ?? ''}</strong>?
                Los usuarios no podrán iniciar sesión con este proveedor.
            </DialogDescription>
        </DialogHeader>
        <DialogFooter>
            <Button variant="outline" onclick={() => (deleteConfirm = null)} disabled={deleting}>Cancelar</Button>
            <Button variant="destructive" onclick={deleteProvider} disabled={deleting}>
                {deleting ? 'Eliminando...' : 'Eliminar'}
            </Button>
        </DialogFooter>
    </DialogContent>
</Dialog>
