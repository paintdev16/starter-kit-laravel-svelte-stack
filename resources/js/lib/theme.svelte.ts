import type { Appearance, ResolvedAppearance } from '@/types/ui';

export type { Appearance, ResolvedAppearance };

export type ThemeState = {
    appearance: { value: Appearance };
    resolvedAppearance: () => ResolvedAppearance;
    updateAppearance: (value: Appearance) => void;
};

const APPEARANCE_COOKIE = 'appearance';
const APPEARANCE_COOKIE_MAX_AGE = 60 * 60 * 24 * 365;
const SYSTEM_THEME_QUERY = '(prefers-color-scheme: dark)';

const isAppearance = (value: unknown): value is Appearance => {
    return value === 'light' || value === 'dark' || value === 'system';
};

const readCookie = (name: string): string | null => {
    const value = document.cookie
        .split('; ')
        .find((row) => row.startsWith(`${name}=`))
        ?.split('=')[1];

    return value ? decodeURIComponent(value) : null;
};

export const getStoredAppearance = (): Appearance => {
    if (typeof document === 'undefined') {
        return 'system';
    }

    const cookieAppearance = readCookie(APPEARANCE_COOKIE);

    if (isAppearance(cookieAppearance)) {
        document.documentElement.dataset.appearance = cookieAppearance;

        return cookieAppearance;
    }

    const htmlAppearance = document.documentElement.dataset.appearance;

    return isAppearance(htmlAppearance) ? htmlAppearance : 'system';
};

const appearance = $state<{ value: Appearance }>({
    value: getStoredAppearance(),
});

let systemThemeQuery: MediaQueryList | null = null;

const prefersDark = (): boolean => {
    return (
        typeof window !== 'undefined' &&
        window.matchMedia(SYSTEM_THEME_QUERY).matches
    );
};

const resolveAppearance = (value: Appearance): ResolvedAppearance => {
    return value === 'dark' || (value === 'system' && prefersDark())
        ? 'dark'
        : 'light';
};

const writeCookie = (value: Appearance): void => {
    document.cookie = `${APPEARANCE_COOKIE}=${encodeURIComponent(value)}; path=/; max-age=${APPEARANCE_COOKIE_MAX_AGE}; SameSite=Lax`;
};

const applyAppearance = (value: Appearance): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const resolvedAppearance = resolveAppearance(value);

    document.documentElement.dataset.appearance = value;
    document.documentElement.classList.toggle(
        'dark',
        resolvedAppearance === 'dark',
    );
    document.documentElement.style.colorScheme = resolvedAppearance;
};

const handleSystemThemeChange = (): void => {
    applyAppearance(appearance.value);
};

const stopListeningForSystemTheme = (): void => {
    systemThemeQuery?.removeEventListener('change', handleSystemThemeChange);
    systemThemeQuery = null;
};

export function initializeTheme(): () => void {
    if (typeof window === 'undefined') {
        return () => {};
    }

    appearance.value = getStoredAppearance();
    applyAppearance(appearance.value);

    try {
        localStorage.removeItem(APPEARANCE_COOKIE);
    } catch {}

    stopListeningForSystemTheme();

    systemThemeQuery = window.matchMedia(SYSTEM_THEME_QUERY);
    systemThemeQuery.addEventListener('change', handleSystemThemeChange);

    return stopListeningForSystemTheme;
}

export function updateAppearance(value: Appearance): void {
    appearance.value = value;
    writeCookie(value);
    applyAppearance(value);
}

export function themeState(): ThemeState {
    return {
        appearance,
        resolvedAppearance: () => resolveAppearance(appearance.value),
        updateAppearance,
    };
}
