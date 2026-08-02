<script module lang="ts">
    export const layout = {
        title: 'Inicia sesión en tu cuenta',
        description: 'Ingresa tu correo y contraseña para iniciar sesión',
    };
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Google, Github, Facebook, X, Apple } from '@/components/icons';
    import InputError from '@/components/InputError.svelte';
    import PasskeyVerify from '@/components/PasskeyVerify.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Button } from '@/components/ui/button';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { register } from '@/routes';
    import { store } from '@/routes/login';
    import { request } from '@/routes/password';

    let {
        status = '',
        canResetPassword,
        enabledProviders = [],
    }: {
        status?: string;
        canResetPassword: boolean;
        enabledProviders?: string[];
    } = $props();

    function providerLabel(name: string): string {
        const labels: Record<string, string> = {
            google: 'Google',
            github: 'GitHub',
            microsoft: 'Microsoft',
            facebook: 'Facebook',
            x: 'X',
            linkedin: 'LinkedIn',
            apple: 'Apple',
            discord: 'Discord',
            gitlab: 'GitLab',
            bitbucket: 'Bitbucket',
            slack: 'Slack',
        };

        return labels[name] ?? name.charAt(0).toUpperCase() + name.slice(1);
    }

    function providerIcon(name: string) {
        const icons: Record<string, any> = {
            google: Google,
            github: Github,
            facebook: Facebook,
            x: X,
            apple: Apple,
        };

        return icons[name];
    }
</script>

<AppHead title="Iniciar sesión" />

{#if status}
    <div class="mb-4 text-center text-sm font-medium text-success">
        {status}
    </div>
{/if}

<PasskeyVerify />

<Form
    {...store.form()}
    resetOnSuccess={['password']}
    class="flex flex-col gap-6"
>
    {#snippet children({ errors, processing })}
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email">Correo electrónico</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autocomplete="email"
                    placeholder="correo@ejemplo.com"
                />
                <InputError message={errors.email} />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">Contraseña</Label>
                    {#if canResetPassword}
                        <TextLink href={request()} class="text-sm">
                            ¿Olvidaste tu contraseña?
                        </TextLink>
                    {/if}
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Contraseña"
                />
                <InputError message={errors.password} />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3">
                    <Checkbox id="remember" name="remember" />
                    <span>Recuérdame</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-4 w-full"
                disabled={processing}
                data-test="login-button"
            >
                {#if processing}<Spinner />{/if}
                Iniciar sesión
            </Button>
        </div>

        {#if enabledProviders.length > 0}
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <span class="w-full border-t"></span>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-card px-2 text-muted-foreground">
                        O continúa con
                    </span>
                </div>
            </div>

            <div
                class="grid gap-3 {enabledProviders.length === 1
                    ? 'grid-cols-1'
                    : 'grid-cols-2'}"
            >
                {#each enabledProviders as provider (provider)}
                    {@const Icon = providerIcon(provider)}
                    <a
                        href="/auth/{provider}/redirect"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border bg-card px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-accent"
                    >
                        {#if Icon}
                            <Icon class="size-4 shrink-0" />
                        {/if}
                        {providerLabel(provider)}
                    </a>
                {/each}
            </div>
        {/if}

        <div class="text-center text-sm text-muted-foreground">
            ¿No tienes una cuenta?
            <TextLink href={register()}>Regístrate</TextLink>
        </div>
    {/snippet}
</Form>
