<script lang="ts">
    import {
        Check,
        Copy,
        Eye,
        EyeOff,
        KeyRound,
        Plus,
        Trash2,
    } from '@lucide/svelte';
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
    import { ScrollArea } from '@/components/ui/scroll-area';
    import Skeleton from '@/components/ui/skeleton/skeleton.svelte';
    import {
        Tooltip,
        TooltipContent,
        TooltipProvider,
        TooltipTrigger,
    } from '@/components/ui/tooltip';

    interface TokenItem {
        id: number;
        name: string;
        abilities: string[];
        last_used_at: string | null;
        created_at: string;
    }

    interface PermissionItem {
        id: number;
        name: string;
        guard_name: string;
    }

    let { active = false }: { active?: boolean } = $props();

    let tokens = $state<TokenItem[]>([]);
    let allPermissions = $state<PermissionItem[]>([]);
    let tokensLoading = $state(false);

    let createDialogOpen = $state(false);
    let tokenName = $state('');
    let tokenNameError = $state('');
    let selectedAbilities = $state<string[]>([]);
    let creating = $state(false);
    let createError = $state('');

    let newTokenPlainText = $state<string | null>(null);
    let tokenCopied = $state(false);
    let tokenRevealed = $state(false);

    let deleteConfirmToken = $state<TokenItem | null>(null);
    let deleting = $state(false);
    let hasLoaded = $state(false);

    function formatDate(dateStr: string | null): string {
        if (!dateStr) {
            return 'Nunca';
        }

        return new Date(dateStr).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    function formatAbilityName(name: string): string {
        if (name === '*') {
            return 'Acceso total';
        }

        return name
            .split(/[-_]/)
            .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
            .join(' ');
    }

    async function loadTokens() {
        tokensLoading = true;

        try {
            const [tokensRes, permsRes] = await Promise.all([
                fetch('/admin/api-tokens'),
                fetch('/admin/permissions'),
            ]);
            tokens = await tokensRes.json();
            allPermissions = await permsRes.json();
        } catch {
            tokens = [];
            allPermissions = [];
        } finally {
            tokensLoading = false;
        }
    }

    function openCreateDialog() {
        tokenName = '';
        tokenNameError = '';
        selectedAbilities = [];
        createError = '';
        newTokenPlainText = null;
        createDialogOpen = true;

        if (allPermissions.length === 0) {
            fetch('/admin/permissions')
                .then((r) => r.json())
                .then((data) => (allPermissions = data))
                .catch(() => {});
        }
    }

    function toggleAbility(permName: string) {
        if (selectedAbilities.includes(permName)) {
            selectedAbilities = selectedAbilities.filter((p) => p !== permName);
        } else {
            selectedAbilities = [...selectedAbilities, permName];
        }
    }

    async function createToken() {
        tokenNameError = '';
        createError = '';

        if (!tokenName.trim()) {
            tokenNameError = 'El nombre del token es obligatorio';

            return;
        }

        creating = true;

        try {
            const csrf =
                document
                    .querySelector('meta[name=csrf-token]')
                    ?.getAttribute('content') ?? '';

            const res = await fetch('/admin/api-tokens', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    name: tokenName.trim(),
                    abilities: selectedAbilities,
                }),
            });

            if (!res.ok) {
                const err = await res.json();
                createError =
                    err.errors?.name?.[0] ||
                    err.message ||
                    'Error al crear token';

                return;
            }

            const data = await res.json();
            newTokenPlainText = data.plain_text_token;
            await loadTokens();
        } catch {
            createError = 'Error de conexión';
        } finally {
            creating = false;
        }
    }

    async function copyToken() {
        if (!newTokenPlainText) {
            return;
        }

        try {
            await navigator.clipboard.writeText(newTokenPlainText);
            tokenCopied = true;
            setTimeout(() => (tokenCopied = false), 2000);
        } catch {
            tokenCopied = false;
        }
    }

    async function deleteToken() {
        if (!deleteConfirmToken) {
            return;
        }

        deleting = true;

        try {
            const csrf =
                document
                    .querySelector('meta[name=csrf-token]')
                    ?.getAttribute('content') ?? '';
            await fetch(`/admin/api-tokens/${deleteConfirmToken.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf },
            });
            deleteConfirmToken = null;
            await loadTokens();
        } finally {
            deleting = false;
        }
    }

    $effect(() => {
        if (active && !hasLoaded && !tokensLoading) {
            hasLoaded = true;
            loadTokens();
        }
    });
</script>

<div
    class="flex items-center justify-between rounded-xl bg-secondary/60 p-4 backdrop-blur-xl"
>
    <p class="text-sm text-muted-foreground">
        <span class="font-semibold text-foreground">{tokens.length}</span>
        {tokens.length === 1 ? 'token generado' : 'tokens generados'}
    </p>
    <Button size="sm" variant="success" onclick={openCreateDialog}>
        <Plus class="mr-1.5 size-4" />
        Nuevo token
    </Button>
</div>

<div class="mt-4">
    {#if tokensLoading}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {#each [1, 2, 3] as _, i (i)}
                <div class="rounded-xl border p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <Skeleton class="size-9 rounded-lg" />
                            <Skeleton class="h-4 w-28" />
                        </div>
                    </div>
                    <div class="mt-4 space-y-2.5">
                        <Skeleton class="h-3 w-20" />
                        <div class="flex flex-wrap gap-1.5">
                            <Skeleton class="h-5 w-16 rounded-md" />
                            <Skeleton class="h-5 w-20 rounded-md" />
                        </div>
                    </div>
                </div>
            {/each}
        </div>
    {:else if tokens.length === 0}
        <div class="flex flex-col items-center justify-center gap-4 py-20">
            <div
                class="flex size-16 items-center justify-center rounded-2xl bg-secondary"
            >
                <KeyRound class="size-8 text-secondary-foreground-soft" />
            </div>
            <div class="text-center">
                <p class="text-base font-medium text-foreground">
                    No hay tokens de API
                </p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Crea un token para integraciones con terceros
                </p>
            </div>
        </div>
    {:else}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {#each tokens as token (token.id)}
                <div
                    class="group relative overflow-hidden rounded-xl border border-card-primary-border bg-card-primary/40 p-5 transition-all hover:shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div
                                class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-card-primary/80"
                            >
                                <KeyRound class="size-4.5 text-primary" />
                            </div>
                            <h3
                                class="truncate text-sm font-semibold text-foreground"
                            >
                                {token.name}
                            </h3>
                        </div>
                        <button
                            class="ml-2 inline-flex size-8 shrink-0 items-center justify-center rounded-lg text-destructive opacity-0 transition-all hover:bg-card-destructive hover:text-destructive group-hover:opacity-100"
                            onclick={() => (deleteConfirmToken = token)}
                            aria-label="Eliminar token"
                        >
                            <Trash2 class="size-3.5" />
                        </button>
                    </div>
                    <div class="mt-3.5 space-y-2.5">
                        <p class="text-xs text-muted-foreground">
                            Último uso: {formatDate(token.last_used_at)}
                        </p>
                        <div class="flex flex-wrap gap-1.5">
                            {#each token.abilities as ability (ability)}
                                <span
                                    class="inline-flex items-center rounded-md bg-secondary px-2 py-0.5 text-[11px] font-medium text-secondary-foreground-soft"
                                >
                                    {ability === '*' ? 'Acceso total' : ability}
                                </span>
                            {/each}
                        </div>
                        <p class="text-[10px] text-muted-foreground/60">
                            Creado {formatDate(token.created_at)}
                        </p>
                    </div>
                </div>
            {/each}
        </div>
    {/if}
</div>

<Dialog
    open={createDialogOpen && !newTokenPlainText}
    onOpenChange={(o) => {
        if (!o) {
            createDialogOpen = false;
        }
    }}
>
    <DialogContent class="sm:max-w-lg">
        <DialogHeader>
            <DialogTitle>Crear token de API</DialogTitle>
            <DialogDescription>
                Genera un token para integraciones con terceros.
            </DialogDescription>
        </DialogHeader>
        <div class="space-y-4 py-2">
            <div class="space-y-2">
                <Label for="token-name">Nombre del token</Label>
                <Input
                    id="token-name"
                    placeholder="ej. Integración ERP"
                    bind:value={tokenName}
                    class={tokenNameError ? 'border-destructive' : ''}
                    onkeydown={(e) => {
                        if (e.key === 'Enter' && !creating) {
                            createToken();
                        }
                    }}
                />
                {#if tokenNameError}
                    <p class="text-xs text-destructive">{tokenNameError}</p>
                {/if}
            </div>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <Label>Permisos / Abilities</Label>
                    <span class="text-xs text-muted-foreground"
                        >{allPermissions.length} permisos disponibles</span
                    >
                </div>
                <p class="text-xs text-muted-foreground/70">
                    Selecciona los permisos mínimos que necesitará esta
                    integración.
                </p>
                <ScrollArea class="h-64 rounded-xl border bg-accent/30 p-2">
                    {#if allPermissions.length === 0}
                        <p
                            class="py-8 text-center text-sm text-muted-foreground"
                        >
                            No hay permisos disponibles. Crea uno en la pestaña
                            Roles.
                        </p>
                    {:else}
                        <div class="space-y-0.5">
                            {#each allPermissions as perm (perm.name)}
                                <label
                                    class="group flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 transition-all hover:bg-accent/60"
                                >
                                    <Checkbox
                                        checked={selectedAbilities.includes(
                                            perm.name,
                                        )}
                                        onCheckedChange={() =>
                                            toggleAbility(perm.name)}
                                        class="shrink-0"
                                    />
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium">
                                            {formatAbilityName(perm.name)}
                                        </p>
                                        <p
                                            class="truncate text-xs text-muted-foreground/60"
                                        >
                                            {perm.name}
                                        </p>
                                    </div>
                                </label>
                            {/each}
                        </div>
                    {/if}
                </ScrollArea>
            </div>
            {#if createError}
                <p class="text-xs text-destructive">{createError}</p>
            {/if}
        </div>
        <DialogFooter>
            <Button
                variant="outline"
                onclick={() => (createDialogOpen = false)}
                disabled={creating}>Cancelar</Button
            >
            <Button variant="success" onclick={createToken} disabled={creating}>
                {creating ? 'Creando...' : 'Crear token'}
            </Button>
        </DialogFooter>
    </DialogContent>
</Dialog>

<Dialog
    open={newTokenPlainText !== null}
    onOpenChange={(o) => {
        if (!o) {
            newTokenPlainText = null;
            createDialogOpen = false;
            tokenRevealed = false;
        }
    }}
>
    <DialogContent class="sm:max-w-lg">
        <DialogHeader>
            <DialogTitle>Token creado</DialogTitle>
            <DialogDescription>
                Copia este token ahora. No podrás verlo de nuevo.
            </DialogDescription>
        </DialogHeader>
        <div class="space-y-4 py-2">
            <div class="rounded-lg border bg-accent/30 p-4">
                <div class="flex items-center justify-between gap-2">
                    <p
                        class="truncate text-sm font-mono font-medium text-foreground"
                    >
                        {tokenRevealed
                            ? newTokenPlainText
                            : newTokenPlainText?.slice(0, 20) + '••••••••••'}
                    </p>
                    <div class="flex gap-1 shrink-0">
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger>
                                    <button
                                        class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent"
                                        onclick={() =>
                                            (tokenRevealed = !tokenRevealed)}
                                        aria-label={tokenRevealed
                                            ? 'Ocultar token'
                                            : 'Mostrar token'}
                                    >
                                        {#if tokenRevealed}
                                            <EyeOff class="size-3.5" />
                                        {:else}
                                            <Eye class="size-3.5" />
                                        {/if}
                                    </button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    {tokenRevealed ? 'Ocultar' : 'Mostrar'}
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger>
                                    <button
                                        class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent"
                                        onclick={copyToken}
                                        aria-label="Copiar token"
                                    >
                                        {#if tokenCopied}
                                            <Check
                                                class="size-3.5 text-success"
                                            />
                                        {:else}
                                            <Copy class="size-3.5" />
                                        {/if}
                                    </button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    {tokenCopied ? 'Copiado' : 'Copiar'}
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </div>
                </div>
            </div>
            <p class="text-xs text-warning-foreground-soft">
                Por seguridad, no podrás volver a ver este token. Guárdalo en un
                lugar seguro.
            </p>
        </div>
        <DialogFooter>
            <Button
                onclick={() => {
                    newTokenPlainText = null;
                    createDialogOpen = false;
                    tokenRevealed = false;
                }}
            >
                Cerrar
            </Button>
        </DialogFooter>
    </DialogContent>
</Dialog>

<Dialog
    open={deleteConfirmToken !== null}
    onOpenChange={(o) => {
        if (!o) {
            deleteConfirmToken = null;
        }
    }}
>
    <DialogContent class="sm:max-w-sm">
        <DialogHeader>
            <DialogTitle>Eliminar token</DialogTitle>
            <DialogDescription>
                ¿Estás seguro de eliminar el token <strong
                    >{deleteConfirmToken?.name ?? ''}</strong
                >? Cualquier integración que lo use dejará de funcionar.
            </DialogDescription>
        </DialogHeader>
        <DialogFooter>
            <Button
                variant="outline"
                onclick={() => (deleteConfirmToken = null)}
                disabled={deleting}>Cancelar</Button
            >
            <Button
                variant="destructive"
                onclick={deleteToken}
                disabled={deleting}
            >
                {deleting ? 'Eliminando...' : 'Eliminar'}
            </Button>
        </DialogFooter>
    </DialogContent>
</Dialog>
