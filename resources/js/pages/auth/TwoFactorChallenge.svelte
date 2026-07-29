<script lang="ts">
    import { Form, setLayoutProps } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import {
        InputOTP,
        InputOTPGroup,
        InputOTPSlot,
    } from '@/components/ui/input-otp';
    import { store } from '@/routes/two-factor/login';
    import type { TwoFactorConfigContent } from '@/types';

    let showRecoveryInput = $state(false);
    let code = $state('');

    const authConfigContent: TwoFactorConfigContent = $derived.by(() => {
        if (showRecoveryInput) {
            return {
                title: 'Código de recuperación',
                description:
                    'Confirma el acceso a tu cuenta ingresando uno de tus códigos de recuperación.',
                buttonText: 'inicia sesión usando un código de autenticación',
            };
        }

        return {
            title: 'Código de autenticación',
            description:
                'Ingresa el código de autenticación proporcionado por tu aplicación.',
            buttonText: 'inicia sesión usando un código de recuperación',
        };
    });

    $effect(() => {
        setLayoutProps({
            title: authConfigContent.title,
            description: authConfigContent.description,
        });
    });

    function toggleRecoveryMode(clearErrors: () => void) {
        showRecoveryInput = !showRecoveryInput;
        clearErrors();
        code = '';
    }
</script>

<AppHead title="Autenticación de dos factores" />

<div class="space-y-6">
    {#if !showRecoveryInput}
        <Form
            {...store.form()}
            class="space-y-4"
            resetOnError
            onError={() => (code = '')}
        >
            {#snippet children({ errors, processing, clearErrors })}
                <input type="hidden" name="code" value={code} />
                <div
                    class="flex flex-col items-center justify-center space-y-3 text-center"
                >
                    <div class="flex w-full items-center justify-center">
                        <InputOTP
                            id="otp"
                            bind:value={code}
                            maxlength={6}
                            disabled={processing}
                            autofocus
                        >
                            {#snippet children({ cells })}
                                <InputOTPGroup>
                                    {#each cells as cell, i (i)}
                                        <InputOTPSlot {cell} />
                                    {/each}
                                </InputOTPGroup>
                            {/snippet}
                        </InputOTP>
                    </div>
                    <InputError message={errors.code} />
                </div>
                <Button type="submit" class="w-full" disabled={processing}
                    >Continuar</Button
                >
                <div class="text-center text-sm text-muted-foreground">
                    <span>o puedes </span>
                    <button
                        type="button"
                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        onclick={() => toggleRecoveryMode(clearErrors)}
                    >
                        {authConfigContent.buttonText}
                    </button>
                </div>
            {/snippet}
        </Form>
    {:else}
        <Form {...store.form()} class="space-y-4" resetOnError>
            {#snippet children({ errors, processing, clearErrors })}
                <Input
                    name="recovery_code"
                    type="text"
                    placeholder="Ingresa el código de recuperación"
                    required
                />
                <InputError message={errors.recovery_code} />
                <Button type="submit" class="w-full" disabled={processing}
                    >Continuar</Button
                >

                <div class="text-center text-sm text-muted-foreground">
                    <span>o puedes </span>
                    <button
                        type="button"
                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        onclick={() => toggleRecoveryMode(clearErrors)}
                    >
                        {authConfigContent.buttonText}
                    </button>
                </div>
            {/snippet}
        </Form>
    {/if}
</div>
