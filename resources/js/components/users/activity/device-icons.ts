import { Laptop, Monitor, Smartphone, Tablet, Tv } from '@lucide/svelte';
import type { Component } from 'svelte';

const DEVICE_ICON_MAP: [string[], Component][] = [
    [['smartphone', 'mobile', 'phone'], Smartphone],
    [['tablet', 'phablet'], Tablet],
    [['tv'], Tv],
    [['laptop', 'notebook'], Laptop],
];

export function getDeviceIcon(deviceType: string | null | undefined): Component {
    const type = deviceType?.toLowerCase() ?? '';
    const match = DEVICE_ICON_MAP.find(([keywords]) =>
        keywords.some((k) => type.includes(k)),
    );

    return match?.[1] ?? Monitor;
}