<script lang="ts">
	import { Pagination as PaginationPrimitive } from "bits-ui";
	import { cn } from "@/lib/utils.js";
	import { buttonVariants } from "@/components/ui/button/index.js";
	import ChevronRightIcon from '@lucide/svelte/icons/chevron-right';

	let {
		ref = $bindable(null),
		class: className,
		...restProps
	}: PaginationPrimitive.NextButtonProps = $props();
</script>

<PaginationPrimitive.NextButton
	bind:ref
	aria-label="Ir a la página siguiente"
	class={cn(
		buttonVariants({ variant: "ghost", size: "icon" }),
		className
	)}
	{...restProps}
>
	<ChevronRightIcon />
</PaginationPrimitive.NextButton>
