<script lang="ts">
	import { Switch as SwitchPrimitive } from "bits-ui";
	import type { Component } from "svelte";
	import { cn, type WithoutChildrenOrChild } from "@/lib/utils.js";

	type SwitchVariant = "primary" | "success" | "warning" | "destructive";

	const variantCheckedClasses: Record<SwitchVariant, string> = {
		primary: "data-checked:bg-primary",
		success: "data-checked:bg-success",
		warning: "data-checked:bg-warning",
		destructive: "data-checked:bg-destructive",
	};

	let {
		ref = $bindable(null),
		class: className,
		checked = $bindable(false),
		size = "default",
		variant = "primary",
		checkedIcon,
		uncheckedIcon,
		...restProps
	}: WithoutChildrenOrChild<SwitchPrimitive.RootProps> & {
		size?: "sm" | "default";
		variant?: SwitchVariant;
		checkedIcon?: Component;
		uncheckedIcon?: Component;
	} = $props();
</script>

<SwitchPrimitive.Root
	bind:ref
	bind:checked
	data-slot="switch"
	data-size={size}
	data-variant={variant}
	class={cn(
		"shrink-0 rounded-full border border-transparent focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-3 aria-invalid:ring-destructive/20 data-[size=default]:h-[18.4px] data-[size=default]:w-[32px] data-[size=sm]:h-[14px] data-[size=sm]:w-[24px] dark:aria-invalid:border-destructive/50 dark:aria-invalid:ring-destructive/40 data-unchecked:bg-muted-foreground/35 dark:data-unchecked:bg-muted-foreground/40 peer group/switch relative inline-flex items-center transition-all outline-none after:absolute after:-inset-x-3 after:-inset-y-2 data-disabled:cursor-not-allowed data-disabled:opacity-50",
		variantCheckedClasses[variant],
		className
	)}
	{...restProps}
>
	<SwitchPrimitive.Thumb
		data-slot="switch-thumb"
		class="rounded-full bg-background group-data-[size=default]/switch:size-4 group-data-[size=sm]/switch:size-3 group-data-[size=default]/switch:data-checked:translate-x-[calc(100%-2px)] group-data-[size=sm]/switch:data-checked:translate-x-[calc(100%-2px)] dark:data-checked:bg-primary-foreground group-data-[size=default]/switch:data-unchecked:translate-x-0 group-data-[size=sm]/switch:data-unchecked:translate-x-0 dark:data-unchecked:bg-foreground pointer-events-none relative block ring-0 transition-transform rtl:data-[state=checked]:translate-x-[calc(-100%)]"
	>
		{#if checkedIcon}
			<!-- svelte-ignore svelte_component_deprecated -->
			<svelte:component
				this={checkedIcon}
				data-slot="switch-checked-icon"
				class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 size-3 text-primary opacity-0 transition-opacity group-data-[state=checked]/switch:opacity-100 group-data-[size=sm]/switch:size-2"
			/>
		{/if}
		{#if uncheckedIcon}
			<!-- svelte-ignore svelte_component_deprecated -->
			<svelte:component
				this={uncheckedIcon}
				data-slot="switch-unchecked-icon"
				class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 size-3 text-muted-foreground opacity-0 transition-opacity group-data-[state=unchecked]/switch:opacity-100 group-data-[size=sm]/switch:size-2"
			/>
		{/if}
	</SwitchPrimitive.Thumb>
</SwitchPrimitive.Root>
