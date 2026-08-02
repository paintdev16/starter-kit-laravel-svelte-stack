# app-paint

Aplicación web construida con **Laravel 13 + Svelte 5 + Inertia** que combina un panel de administración con autenticación avanzada, control de acceso por roles y permisos, auditoría de actividad y soporte en tiempo real.

## Características

- **Autenticación**: registro, login, verificación de email, autenticación de dos factores (2FA/TOTP), passkeys (WebAuthn) y recuperación de contraseña — todo vía Laravel Fortify.
- **Login social**: Google y GitHub mediante Socialite, configurables desde el panel admin.
- **RBAC**: roles y permisos con Spatie Permission (`root`, `super-admin`, `admin`, `user`).
- **Auditoría**: registro de actividad por usuario con detección de dispositivo, SO y navegador (matomo/device-detector), agrupada y con estado online.
- **Avatares**: subida local y avatar desde proveedores sociales.
- **API**: tokens de acceso con Laravel Sanctum y endpoints REST para clientes externos.
- **Tiempo real**: Laravel Reverb con gestión del servicio (local, systemd o supervisor) desde la propia UI.
- **Gestión de usuarios**: búsqueda global, verificación manual de email y control de actividad.
- **Seguridad**: rate limiting en login, recuperación de contraseña y otros endpoints sensibles.

## Stack

| Capa | Tecnologías |
| --- | --- |
| Backend | PHP 8.3+, Laravel 13, Fortify, Sanctum, Socialite, Spatie Permission, Reverb, matomo/device-detector |
| Frontend | Svelte 5, TypeScript, Inertia v3, Tailwind CSS 4, shadcn-svelte (bits-ui), Vite |
| Calidad | Pest, PHPStan, Pint, ESLint, Prettier, svelte-check |

## Requisitos

- PHP 8.3 o superior
- Composer
- Node.js 22 o superior
- SQLite (por defecto), MySQL o PostgreSQL

## Instalación

1. Clona el repositorio y entra en el directorio.
2. Instala dependencias y prepara el entorno:

   ```bash
   composer run setup
   ```

   Esto copia `.env.example` a `.env`, genera la `APP_KEY`, ejecuta las migraciones y compila los assets.

3. Configura `.env` con tus credenciales:

   - **Reverb** (tiempo real): `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`.
   - **OAuth** (login social): `GOOGLE_CLIENT_ID`/`GOOGLE_CLIENT_SECRET` y `GITHUB_CLIENT_ID`/`GITHUB_CLIENT_SECRET` si quieres usarlo.
   - **Gestión de servicios**: `SERVICE_MANAGER=local|systemd|supervisor` (ver `.env.example`).

4. Siembra los roles, permisos y datos iniciales:

   ```bash
   php artisan migrate --seed
   ```

5. Arranca en desarrollo (servidor, cola y Vite):

   ```bash
   composer run dev
   ```

   o de forma separada: `php artisan serve`, `php artisan queue:listen` y `npm run dev`.

## Comandos

| Comando | Descripción |
| --- | --- |
| `composer run dev` | Servidor + cola + Vite en paralelo |
| `composer run lint` | Aplica Pint |
| `composer run lint:check` | Verifica el estilo con Pint |
| `composer run types:check` | Analiza tipos con PHPStan |
| `composer run test` | Pint + PHPStan + Pest |
| `composer run ci:check` | Verificación completa de CI |
| `npm run dev` / `npm run build` | Assets en desarrollo / producción |
| `npm run lint` / `npm run format` / `npm run types:check` | Calidad del frontend |

## Arquitectura

La lógica de negocio está organizada en capas y es compartida entre la interfaz web (Inertia) y la API (JSON):

- **`app/Actions/<Dominio>/`** — coordinan los casos de uso (orquestan los pasos y la auditoría).
- **`app/Services/`** — tareas de dominio específicas y reutilizables.
- **`app/Http/Requests/`** — validación de entrada (Form Requests).
- **`app/Enums/`** — enums tipados de dominio (`ActivityAction`, `AvatarSource`).
- **`app/Support/`** — presentadores que dan forma a las respuestas.
- **`app/Http/Controllers/`** — delgados: autorización + respuesta (Inertia o JSON).

## Testing

La suite usa Pest:

```bash
composer run test
```

## Licencia

Este proyecto está licenciado bajo la **MIT License**.
