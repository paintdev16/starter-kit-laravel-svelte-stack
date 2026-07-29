<script module lang="ts">
    import { show } from '@/routes/profile';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Perfil',
                href: show(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Form, page, router } from '@inertiajs/svelte';
    import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
    import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
    import AppHead from '@/components/AppHead.svelte';
    import DeleteUser from '@/components/DeleteUser.svelte';
    import InputError from '@/components/InputError.svelte';
    import ManagePasskeys from '@/components/ManagePasskeys.svelte';
    import type { Props as ManagePasskeysProps } from '@/components/ManagePasskeys.svelte';
    import ManageTwoFactor from '@/components/ManageTwoFactor.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import * as Card from '@/components/ui/card/index.js';
    import * as Dialog from '@/components/ui/dialog/index.js';

    import {
        Avatar,
        AvatarFallback,
        AvatarImage,
    } from '@/components/ui/avatar';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';

    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { send } from '@/routes/verification';
    import Calendar from '@lucide/svelte/icons/calendar';
    import ChevronRight from '@lucide/svelte/icons/chevron-right';
    import Camera from '@lucide/svelte/icons/camera';
    import KeyRound from '@lucide/svelte/icons/key-round';
    import Mail from '@lucide/svelte/icons/mail';
    import Pencil from '@lucide/svelte/icons/pencil';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import Shield from '@lucide/svelte/icons/shield';
    import * as Tooltip from '@/components/ui/tooltip/index.js';
    import Upload from '@lucide/svelte/icons/upload';
    import User from '@lucide/svelte/icons/user';
    import { X } from '@lucide/svelte';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Separator from '@/components/ui/separator/separator.svelte';

    const userRoles = $derived<string[]>(
        Array.isArray(page.props.auth.roles) ? page.props.auth.roles : [],
    );
    const userPermissions = $derived<string[]>(
        Array.isArray(page.props.auth.permissions)
            ? page.props.auth.permissions
            : [],
    );

    const user = $derived(page.props.auth.user);
    const initials = $derived(
        user.name
            .split(' ')
            .map((n: string) => n[0])
            .join('')
            .toUpperCase()
            .slice(0, 2),
    );
    const canManageTwoFactor = $derived(Boolean(page.props.canManageTwoFactor));
    const requiresConfirmation = $derived(
        Boolean(page.props.requiresConfirmation),
    );
    const twoFactorEnabled = $derived(Boolean(page.props.twoFactorEnabled));
    const canManagePasskeys = $derived(Boolean(page.props.canManagePasskeys));
    const passkeys = $derived(
        (Array.isArray(page.props.passkeys)
            ? page.props.passkeys
            : []) as ManagePasskeysProps['passkeys'],
    );

    let { passwordRules }: { passwordRules: string } = $props();

    let avatars = $derived<{ id: number; url: string; created_at: string }[]>(
        Array.isArray(page.props.auth.user.avatars)
            ? page.props.auth.user.avatars
            : [],
    );
    let latestAvatar = $derived(avatars[0] ?? null);
    let avatarGalleryOpen = $state(false);
    let avatarUploading = $state(false);
    let avatarDeleting = $state<number | null>(null);
    let previewFile = $state<File | null>(null);
    let previewUrl = $state<string | null>(null);
    let fileInput: HTMLInputElement | undefined = $state();

    function triggerFileInput() {
        fileInput?.click();
    }

    function openAvatarGallery() {
        previewFile = null;
        previewUrl = null;
        avatarGalleryOpen = true;
    }

    function onFileSelect(e: Event) {
        const target = e.currentTarget as HTMLInputElement;
        const file = target.files?.[0];
        if (!file) return;
        previewFile = file;
        previewUrl = URL.createObjectURL(file);
        target.value = '';
    }

    function saveAvatar() {
        if (!previewFile) return;
        avatarUploading = true;
        const formData = new FormData();
        formData.append('avatar', previewFile);

        router.post('/avatars', formData, {
            preserveScroll: true,
            onSuccess: () => {
                avatarUploading = false;
                previewFile = null;
                previewUrl = null;
            },
            onError: () => {
                avatarUploading = false;
            },
        });
    }

    function cancelPreview() {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewFile = null;
        previewUrl = null;
    }

    function deleteAvatar(id: number) {
        avatarDeleting = id;
        router.delete(`/avatars/${id}`, {
            preserveScroll: true,
            onFinish: () => {
                avatarDeleting = null;
            },
        });
    }
</script>

<AppHead title="Perfil" />

<div
    class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 pb-10 md:p-6 lg:p-8"
>
    <Card.Root class="relative">
        <!-- Acento lateral -->
        <div
            class="absolute inset-y-0 left-0 w-1 overflow-hidden rounded-l-xl bg-primary"
        ></div>

        <Card.Content
            class="flex flex-col gap-5 p-4 sm:flex-row sm:items-center sm:gap-6 sm:p-6 md:p-8"
        >
            <!-- Avatar -->
            <div class="group relative mx-auto shrink-0 sm:mx-0">
                <Avatar
                    class="size-16 overflow-hidden rounded-full sm:size-20 md:size-24"
                >
                    {#if user.avatar}
                        <AvatarImage
                            src={user.avatar}
                            alt={user.name}
                            class="object-cover"
                        />
                    {/if}
                    <AvatarFallback
                        class="rounded-full bg-primary/10 text-xl font-bold text-primary sm:text-2xl md:text-3xl"
                    >
                        {initials}
                    </AvatarFallback>
                </Avatar>

                {#if avatarUploading}
                    <div
                        class="absolute inset-0 flex items-center justify-center rounded-full bg-black/70 backdrop-blur-sm"
                    >
                        <Upload
                            class="size-4 animate-pulse text-white sm:size-5"
                        />
                    </div>
                {:else}
                    <button
                        onclick={openAvatarGallery}
                        class="group/overlay absolute inset-0 flex cursor-pointer flex-col items-center justify-center gap-1 overflow-hidden rounded-full bg-black/0 opacity-0 transition-all duration-200 hover:bg-black/60 hover:opacity-100"
                    >
                        <Camera class="size-4 text-white sm:size-5" />
                        <span
                            class="hidden text-[10px] font-medium text-white sm:block"
                            >{avatars.length > 0 ? 'Ver fotos' : 'Agregar foto'}</span
                        >
                    </button>
                {/if}

                {#if avatars.length > 0}
                    <button
                        onclick={openAvatarGallery}
                        class="absolute -bottom-1 -right-1 flex size-5 items-center justify-center rounded-full border-2 border-background bg-muted text-muted-foreground shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground sm:size-6"
                        aria-label="Ver avatars"
                    >
                        <span class="text-[9px] font-bold sm:text-[10px]"
                            >{avatars.length}</span
                        >
                    </button>
                {/if}
            </div>

            <!-- Info -->
            <div class="min-w-0 flex-1 text-center sm:text-left">
                <div
                    class="flex flex-wrap items-center justify-center gap-2.5 sm:justify-start"
                >
                    <h1
                        class="text-lg font-bold tracking-tight sm:text-xl md:text-2xl"
                    >
                        {user.name}
                    </h1>
                    {#if user.email_verified_at}
                        <Badge
                            variant="outline"
                            class="h-5 gap-1 border-success/30 px-2 text-[10px] font-normal text-success-foreground-soft"
                        >
                            Verificado
                        </Badge>
                    {:else}
                        <Badge
                            variant="outline"
                            class="h-5 gap-1 border-warning/30 px-2 text-[10px] font-normal text-warning-foreground-soft"
                        >
                            Pendiente
                        </Badge>
                    {/if}
                </div>

                <div
                    class="mt-2 flex flex-col items-center gap-1 sm:flex-row sm:items-center sm:gap-3"
                >
                    <p
                        class="flex items-center gap-1.5 text-sm text-muted-foreground"
                    >
                        <Mail class="size-4 shrink-0" />
                        <span class="max-w-[220px] truncate sm:max-w-none"
                            >{user.email}</span
                        >
                    </p>
                    <Separator
                        orientation="vertical"
                        class="hidden h-4 sm:block"
                    />
                    <p
                        class="flex items-center gap-1.5 text-sm text-muted-foreground"
                    >
                        <Calendar class="size-4 shrink-0" />
                        Miembro desde {new Date(
                            user.created_at,
                        ).toLocaleDateString('es-ES', {
                            year: 'numeric',
                            month: 'long',
                        })}
                    </p>
                </div>
            </div>

            <!-- Editar perfil -->
            <Dialog.Root>
                <Dialog.Trigger class="w-full sm:w-auto">
                    <Button
                        variant="outline"
                        size="sm"
                        class="w-full gap-1.5 sm:w-auto sm:shrink-0"
                    >
                        <Pencil class="size-3.5" />
                        Editar perfil
                    </Button>
                </Dialog.Trigger>
                <Dialog.Content class="sm:max-w-md">
                    <Dialog.Header>
                        <Dialog.Title>Editar perfil</Dialog.Title>
                        <Dialog.Description
                            >Actualiza tu nombre y correo electrónico</Dialog.Description
                        >
                    </Dialog.Header>
                    <Form
                        {...ProfileController.update.form()}
                        class="space-y-5"
                        options={{ preserveScroll: true }}
                    >
                        {#snippet children({ errors, processing })}
                            <div class="grid gap-2">
                                <Label for="name">Nombre</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    value={user.name}
                                    required
                                    autocomplete="name"
                                    placeholder="Nombre completo"
                                />
                                <InputError message={errors.name} />
                            </div>
                            <div class="grid gap-2">
                                <Label for="email">Correo electrónico</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value={user.email}
                                    required
                                    autocomplete="username"
                                    placeholder="Correo electrónico"
                                />
                                <InputError message={errors.email} />
                            </div>
                            {#if Boolean(page.props.mustVerifyEmail) && !user.email_verified_at}
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Tu correo electrónico no está
                                        verificado.
                                        <TextLink href={send()} as="button"
                                            >Reenviar verificación</TextLink
                                        >
                                    </p>
                                    {#if page.props.status === 'verification-link-sent'}
                                        <p
                                            class="mt-1 text-sm font-medium text-success-foreground-soft"
                                        >
                                            Enlace enviado a tu correo.
                                        </p>
                                    {/if}
                                </div>
                            {/if}
                            <Dialog.Footer class="gap-2">
                                <Dialog.Close>
                                    <Button variant="secondary" type="button"
                                        >Cancelar</Button
                                    >
                                </Dialog.Close>
                                <Button type="submit" disabled={processing}
                                    >Guardar cambios</Button
                                >
                            </Dialog.Footer>
                        {/snippet}
                    </Form>
                </Dialog.Content>
            </Dialog.Root>
        </Card.Content>
    </Card.Root>

    <div class="grid gap-6 lg:grid-cols-12">
        <!-- Left column -->
        <div class="space-y-6 lg:col-span-8">
            <Card.Root>
                <Card.Header>
                    <Card.Title class="flex items-center gap-2 text-lg">
                        <User class="size-5 text-primary" />
                        Información personal
                    </Card.Title>
                    <Card.Description
                        >Detalles básicos de tu cuenta</Card.Description
                    >
                </Card.Header>
                <Card.Content>
                    <dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
                        <div>
                            <dt
                                class="text-xs font-medium uppercase tracking-wide text-muted-foreground"
                            >
                                Nombre completo
                            </dt>
                            <dd class="mt-1 text-sm font-medium">
                                {user.name}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium uppercase tracking-wide text-muted-foreground"
                            >
                                Correo electrónico
                            </dt>
                            <dd
                                class="mt-1 flex items-center gap-2 text-sm font-medium"
                            >
                                {user.email}
                                {#if user.email_verified_at}
                                    <span
                                        class="inline-flex items-center rounded-full bg-success/10 px-1.5 py-0.5 text-[10px] font-medium text-success-foreground-soft"
                                    >
                                        Verificado
                                    </span>
                                {:else}
                                    <span
                                        class="inline-flex items-center rounded-full bg-warning/10 px-1.5 py-0.5 text-[10px] font-medium text-warning-foreground-soft"
                                    >
                                        Pendiente
                                    </span>
                                {/if}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium uppercase tracking-wide text-muted-foreground"
                            >
                                Miembro desde
                            </dt>
                            <dd class="mt-1 text-sm font-medium">
                                {new Date(user.created_at).toLocaleDateString(
                                    'es-ES',
                                    {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                    },
                                )}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium uppercase tracking-wide text-muted-foreground"
                            >
                                Última actualización
                            </dt>
                            <dd class="mt-1 text-sm font-medium">
                                {new Date(user.updated_at).toLocaleDateString(
                                    'es-ES',
                                    {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                    },
                                )}
                            </dd>
                        </div>
                    </dl>
                </Card.Content>
            </Card.Root>

            <Card.Root class="border-destructive/10">
                <Card.Content>
                    <DeleteUser />
                </Card.Content>
            </Card.Root>
        </div>

        <!-- Right column -->
        <div class="space-y-6 lg:col-span-4">
            {#if userRoles.length > 0}
                <Card.Root>
                    <Card.Header class="pb-4">
                        <Card.Title class="flex items-center gap-2 text-lg">
                            <Shield class="size-5 text-primary" />
                            Roles y permisos
                        </Card.Title>
                        <Card.Description
                            >Accesos asignados a tu cuenta</Card.Description
                        >
                    </Card.Header>
                    <Card.Content class="space-y-3">
                        <div>
                            <p
                                class="mb-1.5 text-xs font-medium uppercase tracking-wide text-muted-foreground"
                            >
                                Roles
                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                {#each userRoles as role}
                                    <span
                                        class="inline-flex items-center rounded-md bg-secondary px-2 py-0.5 text-xs font-medium text-secondary-foreground-soft"
                                    >
                                        {role}
                                    </span>
                                {/each}
                            </div>
                        </div>
                        <div>
                            <p
                                class="mb-1.5 text-xs font-medium uppercase tracking-wide text-muted-foreground"
                            >
                                Permisos
                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                {#each userPermissions as perm}
                                    <span
                                        class="inline-flex items-center rounded-md border bg-card px-2 py-0.5 text-[11px] font-medium text-card-foreground"
                                    >
                                        {perm}
                                    </span>
                                {/each}
                            </div>
                        </div>
                    </Card.Content>
                </Card.Root>
            {/if}

            <Card.Root>
                <Card.Header class="pb-4">
                    <Card.Title class="flex items-center gap-2 text-lg">
                        <ShieldCheck class="size-5 text-primary" />
                        Seguridad
                    </Card.Title>
                    <Card.Description
                        >Contraseña y métodos de acceso</Card.Description
                    >
                </Card.Header>
                <Card.Content class="space-y-2">
                    <Dialog.Root>
                        <Dialog.Trigger class="w-full">
                            <div
                                class="group flex w-full cursor-pointer items-center justify-between rounded-lg border p-3 text-left transition-colors hover:bg-muted/50"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-8 items-center justify-center rounded-md bg-muted"
                                    >
                                        <KeyRound
                                            class="size-4 text-muted-foreground"
                                        />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium">
                                            Contraseña
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Actualiza tu contraseña
                                            periódicamente
                                        </p>
                                    </div>
                                </div>
                                <ChevronRight
                                    class="size-4 text-muted-foreground transition-transform group-hover:translate-x-0.5"
                                />
                            </div>
                        </Dialog.Trigger>
                        <Dialog.Content class="sm:max-w-md">
                            <Dialog.Header>
                                <Dialog.Title>Cambiar contraseña</Dialog.Title>
                                <Dialog.Description>
                                    Asegúrate de usar una contraseña larga y
                                    segura
                                </Dialog.Description>
                            </Dialog.Header>
                            <Form
                                {...SecurityController.update.form()}
                                class="space-y-5"
                                options={{ preserveScroll: true }}
                                resetOnSuccess
                                resetOnError={[
                                    'password',
                                    'password_confirmation',
                                    'current_password',
                                ]}
                            >
                                {#snippet children({ errors, processing })}
                                    <div class="grid gap-2">
                                        <Label for="current_password"
                                            >Contraseña actual</Label
                                        >
                                        <PasswordInput
                                            id="current_password"
                                            name="current_password"
                                            autocomplete="current-password"
                                            placeholder="Contraseña actual"
                                        />
                                        <InputError
                                            message={errors.current_password}
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="password"
                                            >Nueva contraseña</Label
                                        >
                                        <PasswordInput
                                            id="password"
                                            name="password"
                                            autocomplete="new-password"
                                            placeholder="Nueva contraseña"
                                            passwordrules={passwordRules}
                                        />
                                        <InputError message={errors.password} />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="password_confirmation"
                                            >Confirmar contraseña</Label
                                        >
                                        <PasswordInput
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            autocomplete="new-password"
                                            placeholder="Confirmar contraseña"
                                            passwordrules={passwordRules}
                                        />
                                        <InputError
                                            message={errors.password_confirmation}
                                        />
                                    </div>
                                    <Dialog.Footer class="gap-2">
                                        <Dialog.Close>
                                            <Button
                                                variant="secondary"
                                                type="button">Cancelar</Button
                                            >
                                        </Dialog.Close>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            >Guardar</Button
                                        >
                                    </Dialog.Footer>
                                {/snippet}
                            </Form>
                        </Dialog.Content>
                    </Dialog.Root>
                </Card.Content>
            </Card.Root>

            {#if canManageTwoFactor}
                <Card.Root>
                    <Card.Content class="p-5">
                        <ManageTwoFactor
                            {canManageTwoFactor}
                            {requiresConfirmation}
                            {twoFactorEnabled}
                        />
                    </Card.Content>
                </Card.Root>
            {/if}

            {#if canManagePasskeys}
                <Card.Root>
                    <Card.Content class="p-5">
                        <ManagePasskeys {canManagePasskeys} {passkeys} />
                    </Card.Content>
                </Card.Root>
            {/if}
        </div>
    </div>
</div>

<Dialog.Root bind:open={avatarGalleryOpen}>
    <Dialog.Content class="sm:max-w-sm">
        <Dialog.Header>
            <Dialog.Title>Foto de perfil</Dialog.Title>
        </Dialog.Header>
        <div class="flex flex-col items-center gap-4 py-2">
            {#if previewUrl}
                <img
                    src={previewUrl}
                    alt="Previsualización"
                    class="size-32 rounded-full object-cover shadow-md sm:size-40"
                />
            {:else if latestAvatar}
                <img
                    src={latestAvatar.url}
                    alt="Foto actual"
                    class="size-32 rounded-full object-cover shadow-md ring-4 ring-primary/20 sm:size-40"
                />
            {:else}
                <div
                    class="flex size-32 items-center justify-center rounded-full bg-muted sm:size-40"
                >
                    <User class="size-12 text-muted-foreground/40" />
                </div>
            {/if}

            {#if previewUrl}
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" onclick={cancelPreview}>
                        Cancelar
                    </Button>
                    <Button size="sm" onclick={saveAvatar} disabled={avatarUploading}>
                        {#if avatarUploading}
                            <Upload class="mr-1.5 size-3.5 animate-pulse" />
                            Guardando...
                        {:else}
                            Guardar
                        {/if}
                    </Button>
                </div>
            {:else}
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" onclick={triggerFileInput}>
                        <Camera class="mr-1.5 size-3.5" />
                        {latestAvatar ? 'Cambiar' : 'Subir foto'}
                    </Button>
                    <input
                        type="file"
                        accept="image/jpeg,image/png,image/jpg,image/webp"
                        class="hidden"
                        bind:this={fileInput}
                        onchange={onFileSelect}
                    />
                    {#if latestAvatar}
                        <Button
                            variant="destructive"
                            size="sm"
                            onclick={() => deleteAvatar(latestAvatar.id)}
                            disabled={avatarDeleting === latestAvatar.id}
                        >
                            {#if avatarDeleting === latestAvatar.id}
                                <Upload class="mr-1.5 size-3.5 animate-pulse" />
                                Eliminando...
                            {:else}
                                <Trash2 class="mr-1.5 size-3.5" />
                                Eliminar
                            {/if}
                        </Button>
                    {/if}
                </div>
            {/if}
        </div>
    </Dialog.Content>
</Dialog.Root>
