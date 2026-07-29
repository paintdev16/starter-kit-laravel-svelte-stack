<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

            <script>
        (() => {
            try {
                const validAppearances = ["light", "dark", "system"];
                const cookieValue = document.cookie
                    .split("; ")
                    .find((row) => row.startsWith("appearance="))
                    ?.split("=")[1];
                const cookieAppearance = cookieValue ? decodeURIComponent(cookieValue) : null;
                const serverAppearance = document.documentElement.dataset.appearance;
                const appearance = validAppearances.includes(cookieAppearance)
                    ? cookieAppearance
                    : validAppearances.includes(serverAppearance)
                        ? serverAppearance
                        : "system";

                document.documentElement.dataset.appearance = appearance;
                document.cookie = `appearance=${encodeURIComponent(appearance)}; path=/; max-age=31536000; SameSite=Lax`;

                const systemDark = window.matchMedia(
                    "(prefers-color-scheme: dark)"
                ).matches;

                const isDark =
                    appearance === "dark" ||
                    (appearance === "system" && systemDark);

                document.documentElement.classList.toggle(
                    "dark",
                    isDark
                );

                document.documentElement.style.colorScheme =
                    isDark ? "dark" : "light";
            } catch {}
        })();
    </script>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
