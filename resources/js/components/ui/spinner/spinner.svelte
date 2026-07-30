<script lang="ts">
	import { cn } from "@/lib/utils.js";
	import Loader2Icon from '@lucide/svelte/icons/loader-2';
	import type { SVGAttributes } from "svelte/elements";

	type SpinnerVariant = "current" | "primary" | "secondary" | "muted" | "success" | "warning" | "info" | "destructive";

	const variantClasses: Record<SpinnerVariant, string> = {
		current: "text-current",
		primary: "text-primary",
		secondary: "text-secondary-foreground",
		muted: "text-muted-foreground",
		success: "text-success",
		warning: "text-warning",
		info: "text-info",
		destructive: "text-destructive",
	};

	let {
		class: className,
		role = "status",
		variant = "primary",
		// we add name, color, and stroke for compatibility with different icon libraries props
		name,
		color,
		stroke,
		"aria-label": ariaLabel = "Loading",
		...restProps
	}: SVGAttributes<SVGSVGElement> & { variant?: SpinnerVariant } = $props();
</script>

<Loader2Icon {role} name={name === null ? undefined : name} color={color === null ? undefined : color} stroke={stroke === null ? undefined : stroke} aria-label={ariaLabel} class={cn("size-4 animate-spin", variantClasses[variant], className)} {...restProps} />
