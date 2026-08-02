<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import {
        ArrowRight,
        CheckCircle,
        Database,
        KeyRound,
        LayoutDashboard,
        Palette,
        ShieldCheck,
        Users,
        Zap,
    } from '@lucide/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { toUrl } from '@/lib/utils';
    import { dashboard, login } from '@/routes';
    import { register } from '@/routes';

    const auth = $derived(page.props.auth);

    const features = [
        {
            icon: ShieldCheck,
            title: 'Roles y permisos',
            desc: 'Control de acceso granular con Spatie Permission. Crea roles, asigna permisos y protege cada recurso de tu aplicación.',
            color: 'text-primary bg-primary/10',
        },
        {
            icon: KeyRound,
            title: 'Autenticación segura',
            desc: 'Soporte completo para 2FA, passkeys (WebAuthn), verificación de email y recuperación de contraseña.',
            color: 'text-info bg-info/10',
        },
        {
            icon: Users,
            title: 'Gestión de usuarios',
            desc: 'Panel administrativo con CRUD completo, paginación, búsqueda y asignación de roles desde una interfaz moderna.',
            color: 'text-success bg-success/10',
        },
        {
            icon: Palette,
            title: 'Tema claro/oscuro',
            desc: 'Interfaz adaptable con modo claro y oscuro, diseño responsive y componentes accesibles.',
            color: 'text-warning bg-warning/10',
        },
        {
            icon: Database,
            title: 'Base de datos robusta',
            desc: 'Seeders y migraciones listas para roles, permisos y datos demo. Listo para desarrollar.',
            color: 'text-accent-foreground bg-accent',
        },
        {
            icon: LayoutDashboard,
            title: 'Panel moderno',
            desc: 'Sidebar colapsable, navegación por pestañas, diálogos modales y componentes shadcn-svelte.',
            color: 'text-primary bg-primary/10',
        },
    ];

    const steps = [
        {
            num: '01',
            title: 'Regístrate',
            desc: 'Crea tu cuenta en segundos con email y contraseña.',
        },
        {
            num: '02',
            title: 'Configura tu equipo',
            desc: 'Gestiona usuarios, asigna roles y define permisos.',
        },
        {
            num: '03',
            title: 'Construye',
            desc: 'Desarrolla tu aplicación sobre una base sólida y segura.',
        },
    ];
</script>

<AppHead title="Inicio" />

<div
    class="flex min-h-screen flex-col bg-background text-foreground overflow-x-hidden"
>
    <header
        class="sticky top-0 z-50 border-b bg-background/80 backdrop-blur-lg"
    >
        <div
            class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 md:px-6"
        >
            <div class="flex items-center gap-8">
                <Link
                    href={toUrl(dashboard())}
                    class="flex items-center gap-2 text-lg font-bold tracking-tight"
                >
                    <div
                        class="flex size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground text-xs font-bold"
                    >
                        AP
                    </div>
                    AppPaint
                </Link>
                <nav class="hidden items-center gap-6 md:flex">
                    <a
                        href="#features"
                        class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >Características</a
                    >
                    <a
                        href="#how"
                        class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >Cómo funciona</a
                    >
                    <a
                        href="#stack"
                        class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >Stack</a
                    >
                </nav>
            </div>
            <nav class="flex items-center gap-3">
                {#if auth.user}
                    <Link
                        href={toUrl(dashboard())}
                        class="inline-flex h-9 items-center justify-center rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                        >Panel principal</Link
                    >
                {:else}
                    <Link
                        href={toUrl(login())}
                        class="inline-flex h-9 items-center justify-center rounded-lg px-4 text-sm font-medium text-foreground transition-colors hover:bg-accent"
                        >Iniciar sesión</Link
                    >
                    <Link
                        href={toUrl(register())}
                        class="inline-flex h-9 items-center justify-center rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                        >Registrarse</Link
                    >
                {/if}
            </nav>
        </div>
    </header>

    <main class="flex-1">
        <section class="relative overflow-hidden border-b">
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-primary/8 via-transparent to-transparent"
            ></div>
            <div
                class="pointer-events-none absolute left-1/2 top-0 -translate-x-1/2 overflow-hidden"
            >
                <div
                    class="h-[600px] w-[600px] rounded-full bg-primary/5 blur-3xl max-w-none"
                ></div>
            </div>
            <div class="relative mx-auto max-w-6xl px-4 py-24 md:px-6 md:py-36">
                <div class="mx-auto max-w-3xl text-center">
                    <div
                        class="mb-6 inline-flex items-center gap-1.5 rounded-full border bg-card px-3 py-1 text-xs font-medium text-muted-foreground"
                    >
                        <Zap class="size-3 text-primary" />
                        Starter kit v1.0
                    </div>
                    <h1
                        class="text-4xl font-bold leading-tight tracking-tight md:text-6xl lg:text-7xl"
                    >
                        Construye tu aplicación con
                        <span class="text-primary"> AppPaint</span>
                    </h1>
                    <p
                        class="mt-4 max-w-xl mx-auto text-base text-muted-foreground md:text-lg"
                    >
                        Un starter kit moderno con autenticación completa,
                        roles, permisos, 2FA, passkeys y panel administrativo.
                        Todo listo para desarrollar.
                    </p>
                    <div
                        class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row"
                    >
                        {#if auth.user}
                            <Link
                                href={toUrl(dashboard())}
                                class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-primary px-8 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90 hover:shadow-lg sm:w-auto"
                            >
                                Ir al panel
                                <ArrowRight class="ml-2 size-4" />
                            </Link>
                        {:else}
                            <Link
                                href={toUrl(register())}
                                class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-primary px-8 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90 hover:shadow-lg sm:w-auto"
                            >
                                Comenzar gratis
                                <ArrowRight class="ml-2 size-4" />
                            </Link>
                            <Link
                                href={toUrl(login())}
                                class="inline-flex h-12 w-full items-center justify-center rounded-xl border bg-card px-8 text-sm font-medium text-foreground transition-colors hover:bg-accent sm:w-auto"
                                >Iniciar sesión</Link
                            >
                        {/if}
                    </div>
                </div>

                {#if !auth.user}
                    <div
                        class="mx-auto mt-12 flex max-w-lg items-center justify-center gap-6 rounded-xl border bg-card/50 p-4 text-sm text-muted-foreground backdrop-blur-sm md:mt-16"
                    >
                        <span class="flex items-center gap-1.5"
                            ><CheckCircle class="size-3.5 text-success" />Sin
                            tarjeta</span
                        >
                        <span class="h-4 w-px bg-border"></span>
                        <span class="flex items-center gap-1.5"
                            ><CheckCircle
                                class="size-3.5 text-success"
                            />Gratis</span
                        >
                        <span class="h-4 w-px bg-border"></span>
                        <span class="flex items-center gap-1.5"
                            ><CheckCircle class="size-3.5 text-success" />Sin
                            límite</span
                        >
                    </div>
                {/if}
            </div>
        </section>

        <section id="features" class="scroll-mt-20 border-b py-20 md:py-28">
            <div class="mx-auto max-w-6xl px-4 md:px-6">
                <div class="mx-auto max-w-xl text-center">
                    <span
                        class="text-xs font-semibold uppercase tracking-widest text-primary"
                        >Características</span
                    >
                    <h2
                        class="mt-2 text-3xl font-bold tracking-tight md:text-4xl"
                    >
                        Todo lo que necesitas para empezar
                    </h2>
                    <p class="mt-2 text-muted-foreground">
                        Características listas para usar, sin configuración
                        adicional.
                    </p>
                </div>
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {#each features as feature (feature.title)}
                        <div
                            class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all hover:shadow-md hover:-translate-y-0.5"
                        >
                            <div
                                class="flex size-11 items-center justify-center rounded-lg {feature.color}"
                            >
                                <!-- {feature.icon({ class: 'size-5' })} -->
                            </div>
                            <h3 class="mt-4 font-semibold">{feature.title}</h3>
                            <p
                                class="mt-1.5 text-sm leading-relaxed text-muted-foreground"
                            >
                                {feature.desc}
                            </p>
                        </div>
                    {/each}
                </div>
            </div>
        </section>

        <section id="how" class="scroll-mt-20 border-b py-20 md:py-28">
            <div class="mx-auto max-w-6xl px-4 md:px-6">
                <div class="mx-auto max-w-xl text-center">
                    <span
                        class="text-xs font-semibold uppercase tracking-widest text-primary"
                        >Cómo funciona</span
                    >
                    <h2
                        class="mt-2 text-3xl font-bold tracking-tight md:text-4xl"
                    >
                        Listo en minutos
                    </h2>
                    <p class="mt-2 text-muted-foreground">
                        Tres pasos simples para empezar a construir.
                    </p>
                </div>
                <div class="relative mt-12 grid gap-8 md:grid-cols-3">
                    <div
                        class="hidden md:block absolute left-1/2 top-1/3 h-px w-3/4 -translate-x-1/2 -translate-y-1/2 bg-gradient-to-r from-transparent via-border to-transparent"
                    ></div>
                    {#each steps as step (step.num)}
                        <div
                            class="relative flex flex-col items-center text-center"
                        >
                            <div
                                class="flex size-16 items-center justify-center rounded-2xl bg-primary/10 text-2xl font-bold text-primary"
                            >
                                {step.num}
                            </div>
                            <h3 class="mt-4 text-lg font-semibold">
                                {step.title}
                            </h3>
                            <p class="mt-1.5 text-sm text-muted-foreground">
                                {step.desc}
                            </p>
                        </div>
                    {/each}
                </div>
            </div>
        </section>

        <section id="stack" class="scroll-mt-20 border-b py-20 md:py-28">
            <div class="mx-auto max-w-6xl px-4 md:px-6">
                <div class="mx-auto max-w-xl text-center">
                    <span
                        class="text-xs font-semibold uppercase tracking-widest text-primary"
                        >Stack tecnológico</span
                    >
                    <h2
                        class="mt-2 text-3xl font-bold tracking-tight md:text-4xl"
                    >
                        Tecnologías modernas
                    </h2>
                    <p class="mt-2 text-muted-foreground">
                        Construido con las mejores herramientas del ecosistema.
                    </p>
                </div>
                <div class="mt-12 mx-auto max-w-3xl">
                    <!-- <div class="grid gap-4 sm:grid-cols-2">
                        {#each [{ name: 'Laravel', desc: 'Backend robusto con Eloquent ORM, colas, notificaciones y más.', icon: Database }, { name: 'Svelte 5', desc: 'Interfaz reactiva con runes, snippets y rendimiento excepcional.', icon: Zap }, { name: 'Inertia.js', desc: 'Integración seamless entre Laravel y Svelte sin API adicional.', icon: ArrowRight }, { name: 'Tailwind CSS v4', desc: 'Estilos utilitarios con tokens semánticos y modo oscuro.', icon: Palette }, { name: 'Spatie Permission', desc: 'Control de roles y permisos robusto y flexible.', icon: Lock }, { name: 'Laravel Fortify', desc: 'Autenticación backend con 2FA y protección de rutas.', icon: ShieldCheck }] as tech}
                            <div
                                class="flex items-start gap-3 rounded-xl border bg-card p-4 transition-colors hover:bg-accent/50"
                            >
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                                >
                                    <svelte:component
                                        this={tech.icon}
                                        class="size-4"
                                    />
                                </div>
                                <div>
                                    <p class="font-medium">{tech.name}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {tech.desc}
                                    </p>
                                </div>
                            </div>
                        {/each}
                    </div> -->
                </div>
            </div>
        </section>

        <section class="relative overflow-hidden py-20 md:py-28">
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-primary/5 via-transparent to-transparent"
            ></div>
            <div class="relative mx-auto max-w-6xl px-4 text-center md:px-6">
                <div class="mx-auto max-w-xl">
                    <h2 class="text-3xl font-bold tracking-tight md:text-4xl">
                        ¿Listo para empezar?
                    </h2>
                    <p class="mt-2 text-muted-foreground">
                        Crea tu cuenta y comienza a construir tu aplicación en
                        minutos.
                    </p>
                </div>
                <div
                    class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row"
                >
                    {#if auth.user}
                        <Link
                            href={toUrl(dashboard())}
                            class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-primary px-8 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90 hover:shadow-lg sm:w-auto"
                            >Ir al panel</Link
                        >
                    {:else}
                        <Link
                            href={toUrl(register())}
                            class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-primary px-8 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90 hover:shadow-lg sm:w-auto"
                        >
                            Crear cuenta gratis
                            <ArrowRight class="ml-2 size-4" />
                        </Link>
                        <Link
                            href={toUrl(login())}
                            class="inline-flex h-12 w-full items-center justify-center rounded-xl border bg-card px-8 text-sm font-medium text-foreground transition-colors hover:bg-accent sm:w-auto"
                            >Iniciar sesión</Link
                        >
                    {/if}
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t">
        <div
            class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-6 px-4 py-8 text-sm md:flex-row md:px-6"
        >
            <div class="flex items-center gap-2 text-muted-foreground">
                <div
                    class="flex size-6 items-center justify-center rounded-md bg-primary text-primary-foreground text-[10px] font-bold"
                >
                    AP
                </div>
                <span
                    >&copy; {new Date().getFullYear()} AppPaint. Todos los derechos
                    reservados.</span
                >
            </div>
            <div class="flex items-center gap-6">
                <a
                    href="#features"
                    class="text-muted-foreground transition-colors hover:text-foreground"
                    >Características</a
                >
                <a
                    href="#how"
                    class="text-muted-foreground transition-colors hover:text-foreground"
                    >Cómo funciona</a
                >
                <a
                    href="#stack"
                    class="text-muted-foreground transition-colors hover:text-foreground"
                    >Stack</a
                >
                <a
                    href="https://github.com"
                    target="_blank"
                    class="text-muted-foreground transition-colors hover:text-foreground"
                    aria-label="GitHub"
                >
                    <!-- <Github class="size-4" /> -->
                    xd
                </a>
            </div>
        </div>
    </footer>
</div>
