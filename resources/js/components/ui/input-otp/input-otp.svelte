<script lang="ts">
	import { PinInput as InputOTPPrimitive } from "bits-ui";
	import { cn } from "@/lib/utils.js";
	import type { Snippet } from "svelte";

	type PinInputRootSnippetProps = {
		cells: { char: string | null | undefined; isActive: boolean; hasFakeCaret: boolean }[];
		isFocused: boolean;
		isHovering: boolean;
	};

	let {
		ref = $bindable(null),
		class: className,
		value = $bindable(""),
		children: snippet,
		...restProps
	}: Omit<InputOTPPrimitive.RootProps, "children"> & {
		children?: Snippet<[PinInputRootSnippetProps]>;
	} = $props();
</script>

<InputOTPPrimitive.Root
	bind:ref
	bind:value
	data-slot="input-otp"
	spellcheck={false}
	class={cn(
		"cn-input-otp-input gap-2 flex items-center disabled:cursor-not-allowed has-disabled:opacity-50",
		className
	)}
	{...restProps}
>
	{#snippet children(propz)}
		{#if snippet}
			{@render snippet(propz)}
		{/if}
	{/snippet}
</InputOTPPrimitive.Root>
