<script lang="ts">
	import { cn, type WithElementRef, type WithoutChildren } from "@/lib/utils.js";
	import type { HTMLTextareaAttributes } from "svelte/elements";

	type TextareaVariant = "default" | "success" | "warning" | "info" | "destructive";

	const variantClasses: Record<Exclude<TextareaVariant, "default">, string> = {
		success: "border-success/40 focus-visible:border-success focus-visible:ring-success/50",
		warning: "border-warning/40 focus-visible:border-warning focus-visible:ring-warning/50",
		info: "border-info/40 focus-visible:border-info focus-visible:ring-info/50",
		destructive: "border-destructive/40 focus-visible:border-destructive focus-visible:ring-destructive/50",
	};

	let {
		ref = $bindable(null),
		value = $bindable(),
		class: className,
		"data-slot": dataSlot = "textarea",
		variant = "default",
		...restProps
	}: WithoutChildren<WithElementRef<HTMLTextareaAttributes>> & { variant?: TextareaVariant } = $props();
</script>

<textarea
	bind:this={ref}
	data-slot={dataSlot}
	data-variant={variant}
	class={cn(
		"border-input dark:bg-input/30 focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive dark:aria-invalid:border-destructive/50 disabled:bg-input/50 dark:disabled:bg-input/80 rounded-lg border bg-transparent px-2.5 py-2 text-base transition-colors focus-visible:ring-3 aria-invalid:ring-3 md:text-sm placeholder:text-muted-foreground flex field-sizing-content min-h-16 w-full outline-none disabled:cursor-not-allowed disabled:opacity-50",
		variant !== "default" ? variantClasses[variant] : "",
		className
	)}
	bind:value
	{...restProps}
></textarea>
