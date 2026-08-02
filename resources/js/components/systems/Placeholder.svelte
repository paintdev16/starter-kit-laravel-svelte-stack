<script lang="ts">
    import type { Component } from 'svelte';

    let {
        icon,
        title,
        description,
    }: {
        icon: Component;
        title: string;
        description: string;
    } = $props();
</script>

<div
    class="flex flex-col items-center justify-center gap-4 rounded-xl border bg-card py-20"
>
    <div
        class="flex size-14 items-center justify-center rounded-2xl bg-secondary"
    >
        <!-- svelte-ignore svelte_component_deprecated -->
        <svelte:component
            this={icon}
            class="size-7 text-secondary-foreground-soft"
        />
    </div>
    <div class="max-w-md text-center">
        <p class="text-base font-semibold text-foreground">{title}</p>
        <p class="mt-1 text-sm text-muted-foreground">{description}</p>
    </div>
</div>
