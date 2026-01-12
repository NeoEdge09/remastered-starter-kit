# Laravel 12 + Inertia + Vue 3 Starter Kit (Remastered)

A production-ready, enterprise-grade boilerplate built on top of Laravel, Inertia.js, and Vue 3. Designed to jumpstart robust applications with strictly typed code, advanced permission handling, and standardized data table workflows.

## 🚀 Key Features

- **Security & Auth**: Laravel Breeze + role-based access control (RBAC).
- **Auto-Permission Middleware**: Zero-config permission handling. Routes are scanned and mapped to permissions automatically.
- **Dynamic Navigation**: Database-driven menu builder with drag-and-drop reordering and granular permission filtering.
- **Standardized Data Tables**: Plug-and-play backend trait and frontend composable for search, sort, filters, and pagination.
- **Activity Logging**: Integrated audit trails for all model actions and auth events.
- **Developer Experience**:
    - Full TypeScript support with Ziggy for typed routes.
    - Shadcn-style UI components (Radix Vue + Tailwind).
    - Dark mode support (System/Light/Dark).
    - Command-line optimized (`concurrently` dev server).

## 🛠 Tech Stack

- **Backend**: Laravel 12, Spatie Permission, Spatie Activitylog.
- **Frontend**: Vue 3 (Composition API), Inertia.js, TypeScript, Tailwind CSS.
- **Tools**: Vite, ESLint, Prettier, Pest PHP.

---

## 📦 Installation

1.  **Clone the repository**

    ```bash
    git clone https://github.com/yourusername/laravel-inertia-vue-remastered.git
    cd laravel-inertia-vue-remastered
    ```

2.  **Install dependencies**

    ```bash
    composer install
    npm install
    ```

3.  **Environment setup**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Database setup**

    - Create a database (e.g., in MySQL or SQLite).
    - Update `.env` with your credentials.
    - Run migrations and seeders (creates Super Admin, basic roles, and menus):

    ```bash
    php artisan migrate --seed
    ```

5.  **Run development server**
    ```bash
    # Starts Laravel server, Queue worker, Model watcher, and Vite concurrently
    composer run dev
    ```

> **Default Login (Super Admin Role)**:
>
> - **Email**: `superadmin@example.com`
> - **Password**: `password`

---

## 📖 Documentation

### 1. Dynamic Route & Permission System

This starter kit uses a unique **"Auto Permission"** approach. Instead of hardcoding permission strings in your controllers or routes, the system maps routes to database records.

**How it works:**

1.  **Define Routes**: Simply create routes in `routes/admin.php` wrapped in the `permission.auto` middleware.

    ```php
    Route::middleware(['auth', 'active', 'permission.auto'])->group(function () {
        Route::resource('products', ProductController::class);
    });
    ```

2.  **Create Permissions**: Enable granular control by creating permissions first.

    - Go to **Admin > Permissions** create a Group (e.g., "Products") and Permissions (e.g., `product.view`, `product.create`).
    - Go to **Admin > Roles** and assign these permissions to the relevant roles.

3.  **Scan Routes**: Go to **Admin > Route Access** and click **"Scan Routes"**.

    - The system detects new routes (e.g., `admin.products.index`).
    - It **automatically maps** the route to the permission `product.view` if you created it in Step 2.
    - _Note: If you haven't created the permission yet, the route will still be registered but unlinked. You can create the permission later and re-scan._

4.  **Authorize**:
    - Once mapped, the middleware automatically enforces the check. Only users with the `product.view` permission can access that route.

### 2. Data Tables (Search, Sort, Filter)

We use a standardized pattern to handle tables, avoiding boilerplate in every controller.

#### Backend: `HasTableFeatures` Trait

Use this trait in your Controller to automatically handle `search`, `sort`, `per_page`, and `filters` query parameters.

```php
use App\Traits\HasTableFeatures;
use App\Models\Product;

class ProductController extends Controller
{
    use HasTableFeatures;

    public function index(Request $request)
    {
        // Start your query
        $query = Product::query()->with('category');

        // Apply features
        $query = $this->applyTableQuery($query, $request, [
            // Columns to search against
            'searchColumns' => ['name', 'sku', 'category.name'],

            // Exact match filters
            'filters' => [
                'status' => 'status', // ?status=active maps to where('status', 'active')
                'category' => 'category_id', // ?category=5 maps to where('category_id', 5)
            ],

            // Allowed sort columns
            'sortColumns' => ['name', 'price', 'created_at'],
            'defaultSort' => 'created_at',
        ]);

        return Inertia::render('Admin/Product/Index', [
             // Use applyTableQuery pagination result
            'products' => ProductResource::collection(
                $query->paginate($this->getPerPage($request))->withQueryString()
            ),
            // Pass current filters back to view to repopulate inputs
            'filters' => $this->getTableFilters($request, ['status', 'category']),
        ]);
    }
}
```

#### Frontend: `useDataTable` Composable

Vue composable that syncs URL query parameters with your state, handling debouncing and state preservation.

```vue
<script setup lang="ts">
import { useDataTable } from '@/composables/useDataTable';

// 1. Setup composable with initial props data
const props = defineProps<{ filters: Object }>();

const { filters, sort, perPage, clearFilters } = useDataTable({
    routeName: 'admin.products.index',
    filters: {
        search: props.filters.search || '',
        status: props.filters.status || '',
        category: props.filters.category || '',
    },
});
</script>

<template>
    <!-- 2. Bind inputs directly to filters object -->
    <Input v-model="filters.search" placeholder="Search..." />

    <Select v-model="filters.status" />

    <!-- 3. Table headers control sorting -->
    <TableHead @click="toggleSort('name')">
        Name
        <SortIcon :direction="getSortDirection('name')" />
    </TableHead>
</template>
```

### 3. Navigation & Menus

The sidebar menu is dynamic and database-driven.

- **Management**: Go to **Admin > Menus**.
- **Structure**: Supports infinite nesting via `parent_id`.
- **Visibility**: Menus are automatically hidden if the user lacks the permission specified in the 'Permission' field.
- **Icons**: Uses Lucide icons strings (e.g., `Users`, `Settings`).

### 4. Activity Logging

Models use the `LogsActivity` trait to automatically record events.

```php
use App\Traits\LogsActivity;

class Product extends Model
{
    use LogsActivity;
    // That's it! Created/Updated/Deleted events are now logged.
}
```

## 📂 Project Structure

```
app/
├── Http/
│   ├── Middleware/
│   │   ├── AutoPermission.php   # The magic behind route permissions
│   │   └── EnsureUserIsActive.php
├── Models/
│   ├── RouteAccess.php          # Database storage for route metadata
├── Services/
│   ├── MenuService.php          # Menu tree building & caching
│   └── RouteService.php         # Router scanning logic
├── Traits/
│   ├── HasTableFeatures.php     # Backend table logic
│   └── LogsActivity.php         # Activity log wrapper
resources/js/
├── components/ui/               # Reusable atomic components
├── composables/
│   └── useDataTable.ts          # Frontend table logic
├── pages/                       # Inertia pages
```
