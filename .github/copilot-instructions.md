# AI Coding Guidelines

- **Stack and entry points**

    - Laravel 12 + Inertia + Vue 3 + TypeScript; Vite entry is [resources/js/app.ts](resources/js/app.ts).
    - Vite alias `@` points to `resources/js` per [vite.config.ts](vite.config.ts); Ziggy is available globally.
    - Frontend pages live under [resources/js/pages](resources/js/pages); layouts and shared UI in `layouts/` and `components/`.

- **Auth, roles, permissions**

    - Spatie permission is used with custom models in [app/Models/Permission.php](app/Models/Permission.php) and [app/Models/Role.php](app/Models/Role.php); super-admin bypass is in [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php).
    - `is_active` gate is enforced by [app/Http/Middleware/EnsureUserIsActive.php](app/Http/Middleware/EnsureUserIsActive.php); inactive users are logged out.
    - Route-level permission enforcement relies on the `permission.auto` middleware in [app/Http/Middleware/AutoPermission.php](app/Http/Middleware/AutoPermission.php) which consults `route_accesses` rows and cache built by [app/Models/RouteAccess.php](app/Models/RouteAccess.php).

- **Route access workflow**

    - Routes are discovered and synced into `route_accesses` via [app/Http/Controllers/Admin/RouteAccessController.php](app/Http/Controllers/Admin/RouteAccessController.php) using [app/Services/RouteService.php](app/Services/RouteService.php). Use the admin UI (Routes > Scan) after adding or renaming routes so AutoPermission can authorize them.
    - RouteAccess records can be bulk-activated/deactivated or toggled public; cache clears automatically on save/delete.

- **Navigation menus**

    - Menu tree rendering and permission filtering live in [app/Services/MenuService.php](app/Services/MenuService.php); menus are cached (see `clearCache`) and filtered by user permissions unless the user is super-admin.
    - Menu CRUD uses nested structures via `parent_id`; admin management is in [app/Http/Controllers/Admin/MenuController.php](app/Http/Controllers/Admin/MenuController.php). Keep `order` and `parent_id` consistent for correct nesting.

- **Inertia shared props**

    - Shared data is set in [app/Http/Middleware/HandleInertiaRequests.php](app/Http/Middleware/HandleInertiaRequests.php): current user (via [app/Http/Resources/UserResource.php](app/Http/Resources/UserResource.php)), role/permission names, filtered menus, flash messages, and a rotating quote.
    - Controllers typically pass `page_setting` (title, breadcrumbs, action/back links) and `page_data` to pages, e.g., [app/Http/Controllers/Admin/ActivityLogController.php](app/Http/Controllers/Admin/ActivityLogController.php).

- **Table/list patterns**

    - Backend list queries use [app/Traits/HasTableFeatures.php](app/Traits/HasTableFeatures.php) for search, filters, sorting, and pagination; wire new list endpoints to this helper to keep behavior consistent.
    - Frontend tables use [resources/js/composables/useDataTable.ts](resources/js/composables/useDataTable.ts) to mirror filters/per-page/sort via Inertia query params (debounced GET with `preserveState`). Example usage in [resources/js/pages/Admin/ActivityLog/Index.vue](resources/js/pages/Admin/ActivityLog/Index.vue).

- **Activity logging**

    - Models include [app/Traits/LogsActivity.php](app/Traits/LogsActivity.php) to auto-log create/update/delete with Spatie activitylog; custom Activity model adds friendly labels in [app/Models/Activity.php](app/Models/Activity.php).
    - Admin can view/export/clear logs via Activity Log pages and controller in [app/Http/Controllers/Admin/ActivityLogController.php](app/Http/Controllers/Admin/ActivityLogController.php).

- **Backend conventions**

    - Admin routes live under [routes/admin.php](routes/admin.php) with middleware `auth`, `active`, `permission.auto`; user/settings routes are in [routes/web.php](routes/web.php) and [routes/settings.php](routes/settings.php).
    - Requests are validated with form request classes (see `app/Http/Requests/Admin/*`). Controllers lean on Eloquent resources in `app/Http/Resources` for shaping Inertia payloads.

- **Frontend conventions**

    - App layout and breadcrumbs come from `AppLayout` and `page_setting`; use Ziggy `route()` helper for links. UI kit components live in `resources/js/components/ui/` (shadcn-style) and icons from `lucide-vue-next`.
    - Theme initialization (light/dark and accent color) is run on boot in [resources/js/app.ts](resources/js/app.ts) via `useAppearance`/`useThemeColor` composables.

- **Build, lint, and tests**

    - Install: `composer install` + `npm install`. Dev stack: `npm run dev` (Vite), `npm run build` for production assets.
    - Full local stack (PHP server, queue listener, logs, Vite) can be started via `composer run dev` (uses `concurrently`).
    - Formatting/linting: `npm run format` / `npm run format:check` for Prettier; `npm run lint` for ESLint. Backend style: `vendor/bin/pint`.
    - Tests: use `php artisan test` or `./vendor/bin/pest` (Pest scaffolding present in `tests/`).

- **When adding routes/features**
    - Give routes meaningful names; after creating, run the Route Access scan so permissions map correctly, then set `permission_name`/`is_public` as needed.
    - Update menus if navigation should expose the new route; ensure permission names align with RouteAccess/Spatie permissions.
    - Keep `page_setting`/`page_data` shape aligned with existing pages to avoid front-end breakage.
