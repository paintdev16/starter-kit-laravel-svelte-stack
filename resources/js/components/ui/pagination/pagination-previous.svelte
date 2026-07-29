<script lang="ts">
	import { Pagination as PaginationPrimitive } from "bits-ui";
	import { cn } from "@/lib/utils.js";
	import { buttonVariants } from "@/components/ui/button/index.js";
	import ChevronLeftIcon from '@lucide/svelte/icons/chevron-left';

	let {
		ref = $bindable(null),
		class: className,
		...restProps
	}: PaginationPrimitive.PrevButtonProps = $props();
</script>

<PaginationPrimitive.PrevButton
	bind:ref
	aria-label="Ir a la página anterior"
	class={cn(
		buttonVariants({ variant: "ghost", size: "icon" }),
		className
	)}
	{...restProps}
>
	<ChevronLeftIcon />
</PaginationPrimitive.PrevButton>
