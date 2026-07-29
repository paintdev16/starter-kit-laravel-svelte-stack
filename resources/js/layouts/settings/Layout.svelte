<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import Heading from '@/components/Heading.svelte';
    import { Separator } from '@/components/ui/separator';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { toUrl } from '@/lib/utils';
    import { edit as editAppearance } from '@/routes/appearance';
    import { show as showProfile } from '@/routes/profile';
    import type { NavItem } from '@/types';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    const sidebarNavItems: NavItem[] = [
        {
            title: 'Perfil',
            href: showProfile(),
        },
        {
            title: 'Seguridad',
            href: showProfile(),
        },
        {
            title: 'Apariencia',
            href: editAppearance(),
        },
    ];

    const url = currentUrlState();
</script>

<div class="px-4 py-6">
    <Heading
        title="Configuración"
        description="Administra tu perfil y configuración de la cuenta"
    />

    <div class="flex flex-col lg:flex-row lg:space-x-12">
        <aside class="w-full max-w-xl lg:w-48">
            <nav
                class="flex flex-col space-y-1 space-x-0"
                aria-label="Configuración"
            >
                {#each sidebarNavItems as item (toUrl(item.href))}
                    <Link
                        href={toUrl(item.href)}
                        class="inline-flex h-8 w-full items-center justify-start rounded-lg px-2.5 text-sm font-medium transition-colors hover:bg-muted hover:text-foreground {url.isCurrentUrl(
                            item.href,
                            url.currentUrl,
                        )
                            ? 'bg-muted text-foreground'
                            : ''}"
                    >
                        {item.title}
                    </Link>
                {/each}
            </nav>
        </aside>

        <Separator class="my-6 lg:hidden" />

        <div class="flex-1 md:max-w-2xl">
            <section class="max-w-xl space-y-12">
                {@render children?.()}
            </section>
        </div>
    </div>
</div>
