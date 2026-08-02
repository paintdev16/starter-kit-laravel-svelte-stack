<script module lang="ts">
    export const layout = {
        title: 'Verificación de correo',
        description:
            'Verifica tu correo electrónico haciendo clic en el enlace que te enviamos.',
    };
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Button } from '@/components/ui/button';
    import { Spinner } from '@/components/ui/spinner';
    import { logout } from '@/routes';
    import { send } from '@/routes/verification';

    let {
        status = '',
    }: {
        status?: string;
    } = $props();
</script>

<AppHead title="Verificación de correo" />

{#if status === 'verification-link-sent'}
    <div class="mb-4 text-center text-sm font-medium text-success">
        Se ha enviado un nuevo enlace de verificación al correo que
        proporcionaste durante el registro.
    </div>
{/if}

<Form {...send.form()} class="space-y-6 text-center">
    {#snippet children({ processing })}
        <Button type="submit" disabled={processing} variant="secondary">
            {#if processing}<Spinner />{/if}
            Reenviar correo de verificación
        </Button>

        <TextLink href={logout()} as="button" class="mx-auto block text-sm">
            Cerrar sesión
        </TextLink>
    {/snippet}
</Form>
