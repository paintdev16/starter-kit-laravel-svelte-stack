<script lang="ts">
    import { Form, page } from '@inertiajs/svelte';
    import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
    import Heading from '@/components/Heading.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
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
    import { Label } from '@/components/ui/label';

    const isLastRoot = $derived(Boolean(page.props.auth?.isLastRoot));
</script>

<div class="space-y-6">
    <Heading
        variant="small"
        title="Eliminar cuenta"
        description="Una vez eliminada la cuenta, todos sus datos se
                        perderán permanentemente."
    />
    {#if isLastRoot}
        <div
            class="space-y-4 rounded-xl border border-warning/30 bg-card-warning p-4"
        >
            <div class="relative space-y-0.5 text-warning-foreground-soft">
                <p class="font-medium">No disponible</p>
                <p class="text-sm">
                    Eres el único usuario root. No puedes eliminar tu cuenta.
                </p>
            </div>
        </div>
    {:else}
        <div
            class="space-y-4 rounded-xl border border-card-destructive-border bg-card-destructive p-4"
        >
            <div class="relative space-y-0.5 text-destructive">
                <p class="font-medium">Advertencia</p>
                <p class="text-sm text-destructive-foreground-soft">
                    Ten cuidado, esto no se puede deshacer.
                </p>
            </div>
            <Dialog>
                <DialogTrigger>
                    <Button variant="destructive" data-test="delete-user-button"
                        >Eliminar cuenta</Button
                    >
                </DialogTrigger>
                <DialogContent>
                    <Form
                        {...ProfileController.destroy.form()}
                        class="space-y-6"
                        options={{ preserveScroll: true }}
                    >
                        {#snippet children({ errors, processing })}
                            <div class="space-y-3">
                                <DialogTitle
                                    >¿Estás seguro de que quieres eliminar tu
                                    cuenta?</DialogTitle
                                >
                                <DialogDescription>
                                    Una vez eliminada tu cuenta, todos sus
                                    recursos y datos se eliminarán
                                    permanentemente. Ingresa tu contraseña para
                                    confirmar.
                                </DialogDescription>
                            </div>

                            <div class="grid gap-2">
                                <Label for="password" class="sr-only"
                                    >Contraseña</Label
                                >
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    placeholder="Contraseña"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <DialogFooter class="gap-2">
                                <DialogClose>
                                    <Button variant="secondary">Cancelar</Button
                                    >
                                </DialogClose>

                                <Button
                                    type="submit"
                                    variant="destructive"
                                    disabled={processing}
                                    data-test="confirm-delete-user-button"
                                >
                                    Eliminar cuenta
                                </Button>
                            </DialogFooter>
                        {/snippet}
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    {/if}
</div>
