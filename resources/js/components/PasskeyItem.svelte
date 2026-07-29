<script lang="ts">
    import KeyRound from '@lucide/svelte/icons/key-round';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import { Button } from '@/components/ui/button';
    import {
        Dialog,
        DialogClose,
        DialogContent,
        DialogDescription,
        DialogFooter,
        DialogTitle,
        DialogTrigger,
    } from '@/components/ui/dialog';
    import type { Passkey } from '@/types/auth';

    let {
        passkey,
        onDelete,
    }: {
        passkey: Passkey;
        onDelete?: (id: number, onError: () => void) => void;
    } = $props();

    let isDeleting = $state(false);

    const handleDelete = () => {
        isDeleting = true;
        onDelete?.(passkey.id, () => {
            isDeleting = false;
        });
    };
</script>

<div class="flex items-center justify-between border-b p-4 last:border-b-0">
    <div class="flex items-center gap-4">
        <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-secondary"
        >
            <KeyRound class="h-5 w-5 text-secondary-foreground-soft" />
        </div>
        <div class="space-y-1">
            <div class="flex items-center gap-2.5">
                <p class="font-medium tracking-tight">{passkey.name}</p>
                {#if passkey.authenticator}
                    <span
                        class="inline-flex items-center gap-1 rounded-md bg-secondary px-2 py-0.5 text-[11px] font-medium uppercase tracking-wide text-secondary-foreground-soft ring-1 ring-inset ring-border"
                    >
                        {passkey.authenticator}
                    </span>
                {/if}
            </div>
            <p class="text-sm text-muted-foreground">
                Añadida {passkey.created_at_diff}
                {#if passkey.last_used_at_diff}
                    <span class="mx-1 text-muted-foreground/50">/</span>
                    Último uso {passkey.last_used_at_diff}
                {/if}
            </p>
        </div>
    </div>

    <Dialog>
        <DialogTrigger>
            {#snippet child({ props })}
                <Button
                    {...props}
                    variant="ghost"
                    size="sm"
                    class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                >
                    <Trash2 class="h-4 w-4" />
                    <span class="sr-only">Eliminar</span>
                </Button>
            {/snippet}
        </DialogTrigger>

        <DialogContent>
            <DialogTitle>Eliminar llave de acceso</DialogTitle>
            <DialogDescription>
                ¿Estás seguro de que quieres eliminar la llave "{passkey.name}"?
                Ya no podrás usarla para iniciar sesión.
            </DialogDescription>
            <DialogFooter>
            <DialogClose>
                {#snippet child({ props })}
                    <Button {...props} variant="secondary">
                        Cancelar
                    </Button>
                {/snippet}
            </DialogClose>
                <Button
                    variant="destructive"
                    disabled={isDeleting}
                    onclick={handleDelete}
                >
                    {isDeleting ? 'Eliminando...' : 'Eliminar llave de acceso'}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</div>
