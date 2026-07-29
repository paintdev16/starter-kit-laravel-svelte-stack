<script lang="ts">
    import { router } from "@inertiajs/svelte";
    import { UtensilsCrossed, Table2, User, Search } from '@lucide/svelte';
    import { Badge } from "@/components/ui/badge/index.js";
    import * as Command from "@/components/ui/command/index.js";
    import Button from "./ui/button/button.svelte";

    type SearchItem = {
        type: string;
        label: string;
        description: string;
        url: string;
    };

    type SearchGroup = {
        group: string;
        items: SearchItem[];
    };

    let open = $state(false);
    let query = $state("");
    let results = $state<SearchGroup[]>([]);
    let loading = $state(false);

    let searchTimeout: ReturnType<typeof setTimeout>;

    let hasResults = $derived(results.some((group) => group.items.length > 0));

const icons: Record<string, typeof Search> = {
        table: Table2,
        product: UtensilsCrossed,
        user: User,
    };

    const typeLabels: Record<string, string> = {
        table: "Mesa",
        product: "Producto",
        user: "Usuario",
    };

    function handleQueryChange(value: string) {
        clearTimeout(searchTimeout);

        const trimmed = value.trim();

        if (trimmed.length < 2) {
            results = [];
            loading = false;

            return;
        }

        loading = true;

        searchTimeout = setTimeout(async () => {
            try {
                const url = `/global-search?q=${encodeURIComponent(trimmed)}`;
                const response = await fetch(url);
                results = await response.json();
            } catch (error) {
                console.error("[GlobalSearch] error:", error);
                results = [];
            } finally {
                loading = false;
            }
        }, 300);
    }

    $effect(() => {
        handleQueryChange(query);
    });

    function handleOpenChange(value: boolean) {
        open = value;

        if (!value) {
            query = "";
            results = [];
            clearTimeout(searchTimeout);
        }
    }

    function select(url: string) {
        handleOpenChange(false);
        router.visit(url);
    }

    function handleKeydown(e: KeyboardEvent) {
        if (e.key === "k" && (e.metaKey || e.ctrlKey)) {
            e.preventDefault();
            open = !open;
        }
    }
</script>

<svelte:document onkeydown={handleKeydown} />

<Button
    type="button"
    variant="outline"
    onclick={() => (open = true)}
    class="w-9 justify-center gap-2 px-0 text-sm text-muted-foreground sm:w-64 sm:justify-between sm:px-3"
>
    <span class="flex items-center gap-2">
        <Search class="size-4" />
        <span class="hidden sm:inline">Buscar...</span>
    </span>
    <kbd
        class="bg-muted text-muted-foreground pointer-events-none hidden h-5 items-center gap-1 rounded border px-1.5 font-mono text-[10px] font-medium select-none sm:inline-flex"
    >
        <span class="text-xs">⌘</span>K
    </kbd>
</Button>

<Command.Dialog bind:open onOpenChange={handleOpenChange} class="py-2 px-1">
    <Command.Input
        bind:value={query}
        placeholder="Buscar mesas, productos, usuarios..."
    />
    <Command.List>
        {#if loading}
            <Command.Empty>Buscando...</Command.Empty>
        {:else if query.trim().length < 2}
            <Command.Empty
                >Escribe al menos 2 caracteres para buscar</Command.Empty
            >
        {:else if !hasResults}
            <Command.Empty>No hay resultados para "{query}"</Command.Empty>
        {:else}
            {#each results as group, i (group.group)}
                {#if group.items.length > 0}
                    {#if i > 0}<Command.Separator />{/if}
                    <Command.Group heading={group.group}>
                        {#each group.items as item (item.label)}
                            {@const Icon = icons[item.type] ?? Search}
                            <Command.Item
                                value={item.label}
                                onSelect={() => select(item.url)}
                            >
                                <Icon class="me-2 size-4" />
                                <div class="flex min-w-0 flex-1 flex-col">
                                    <span class="truncate">{item.label}</span>
                                    <span
                                        class="truncate text-xs text-muted-foreground"
                                    >
                                        {item.description}
                                    </span>
                                </div>
                                <Badge
                                    variant="secondary"
                                    class="text-[10px] font-normal"
                                >
                                    {typeLabels[item.type] ?? item.type}
                                </Badge>
                            </Command.Item>
                        {/each}
                    </Command.Group>
                {/if}
            {/each}
        {/if}
    </Command.List>
</Command.Dialog>