<script lang="ts">
	import type { HTMLAttributes } from "svelte/elements";
	import { cn, type WithElementRef } from "@/lib/utils.js";

	type CardVariant = "default" | "primary" | "success" | "warning" | "info" | "destructive";

	const variantClasses: Record<CardVariant, string> = {
		default: "bg-card text-card-foreground",
		primary: "bg-card-primary text-card-foreground border-card-primary-border",
		success: "bg-card-success text-card-foreground border-card-success-border",
		warning: "bg-card-warning text-card-foreground border-card-warning-border",
		info: "bg-card-info text-card-foreground border-card-info-border",
		destructive: "bg-card-destructive text-card-foreground border-card-destructive-border",
	};

	let {
		ref = $bindable(null),
		class: className,
		children,
		size = "default",
		variant = "default",
		...restProps
	}: WithElementRef<HTMLAttributes<HTMLDivElement>> & {
		size?: "default" | "sm";
		variant?: CardVariant;
	} = $props();
</script>

<div
	bind:this={ref}
	data-slot="card"
	data-size={size}
	data-variant={variant}
	class={cn(
		"gap-(--card-spacing) overflow-hidden rounded-xl py-(--card-spacing) text-sm [--card-spacing:--spacing(4)] has-data-[slot=card-footer]:pb-0 has-[>img:first-child]:pt-0 data-[size=sm]:[--card-spacing:--spacing(3)] data-[size=sm]:has-data-[slot=card-footer]:pb-0 *:[img:first-child]:rounded-t-xl *:[img:last-child]:rounded-b-xl group/card flex flex-col",
		variantClasses[variant],
		variant === "default" ? "ring-foreground/10 ring-1" : "border ring-0",
		className
	)}
	{...restProps}
>
	{@render children?.()}
</div>
