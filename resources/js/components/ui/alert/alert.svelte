<script lang="ts" module>
	import { type VariantProps, tv } from "tailwind-variants";

	export const alertVariants = tv({
		base: "grid gap-0.5 rounded-lg border px-2.5 py-2 text-left text-sm has-data-[slot=alert-action]:relative has-data-[slot=alert-action]:pr-18 has-[>svg]:grid-cols-[auto_1fr] has-[>svg]:gap-x-2 *:[svg]:row-span-2 *:[svg]:translate-y-0.5 *:[svg]:text-current *:[svg:not([class*='size-'])]:size-4 group/alert relative w-full",
		variants: {
			variant: {
default: "bg-card text-card-foreground border-border",
			destructive: "border-card-destructive-border bg-card-destructive text-destructive *:data-[slot=alert-description]:text-destructive/90 *:[svg]:text-current",
			success: "border-card-success-border bg-card-success text-success-foreground-soft dark:text-success *:data-[slot=alert-description]:text-success-foreground-soft/90 dark:*:data-[slot=alert-description]:text-success/90 *:[svg]:text-current",
			warning: "border-card-warning-border bg-card-warning text-warning-foreground-soft dark:text-warning *:data-[slot=alert-description]:text-warning-foreground-soft/90 dark:*:data-[slot=alert-description]:text-warning/90 *:[svg]:text-current",
			info: "border-card-info-border bg-card-info text-info-foreground-soft dark:text-info *:data-[slot=alert-description]:text-info-foreground-soft/90 dark:*:data-[slot=alert-description]:text-info/90 *:[svg]:text-current",
			},
		},
		defaultVariants: {
			variant: "default",
		},
	});

	export type AlertVariant = VariantProps<typeof alertVariants>["variant"];
</script>

<script lang="ts">
	import type { HTMLAttributes } from "svelte/elements";
	import { cn, type WithElementRef } from "@/lib/utils.js";

	let {
		ref = $bindable(null),
		class: className,
		variant = "default",
		children,
		...restProps
	}: WithElementRef<HTMLAttributes<HTMLDivElement>> & {
		variant?: AlertVariant;
	} = $props();
</script>

<div
	bind:this={ref}
	data-slot="alert"
	role="alert"
	class={cn(alertVariants({ variant }), className)}
	{...restProps}
>
	{@render children?.()}
</div>
