<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Traits\HasTableFeatures;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    use HasTableFeatures;

    /**
     * Display a listing of the users.
     */
    public function index(Request $request): Response
    {
        $query = User::with(['roles', 'permissions']);

        $query = $this->applyTableQuery($query, $request, [
            'searchColumns' => ['name', 'email'],
            'filters' => [
                'status' => [
                    'column' => 'is_active',
                    'boolean' => ['true' => 'active'],
                ],
                'role' => fn($q, $value) => $q->whereHas('roles', fn($r) => $r->where('id', $value)),
            ],
            'sortColumns' => ['name', 'email', 'is_active', 'created_at'],
        ]);

        return Inertia::render('Admin/Users/Index', [
            "page_setting" => [
                "title" => "Users",
                "subtitle" => "Manage system users and their roles",
                "breadcrumbs" => [
                    ["title" => "Dashboard", "href" => route('dashboard')],
                    ["title" => "Users", "href" => route('admin.users.index')],
                ],
            ],
            "page_data" => [
                "users" => UserResource::collection($query->paginate($this->getPerPage($request))->withQueryString()),
                "roles" => RoleResource::collection(Role::all()),
                "filters" => $this->getTableFilters($request, ['status', 'role']),
            ],
        ]);
    }


    /**
     * Show the form for creating a new user.
     */

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', [
            'page_setting' => [
                'title' => 'Create User',
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Users', 'href' => route('admin.users.index')],
                    ['title' => 'Create', 'href' => route('admin.users.create')],
                ],
                'back_link' => route('admin.users.index'),
                'action' => route('admin.users.store'),

            ],
            'page_data' => [
                'roles' => Role::all(),
                'permissions' => Permission::with('group')->get()
                    ->groupBy('permission_group_id'),
            ]
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'is_active' => $request->is_active ?? true,
        ]);

        if ($request->roles) {
            $user->syncRoles(Role::whereIn('id', $request->roles)->pluck('name'));
        }

        if ($request->permissions) {
            $user->syncPermissions(Permission::whereIn('id', $request->permissions)->pluck('name'));
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): Response
    {
        return Inertia::render('Admin/Users/Show', [
            'page_setting' => [
                'title' => 'User - ' . $user->name,
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Users', 'href' => route('admin.users.index')],
                    ['title' => $user->name, 'href' => route('admin.users.show', $user->id)],
                ],
                'back_link' => route('admin.users.index'),
                'action' => route('admin.users.edit', $user->id),
            ],
            'page_data' => [
                'user' => new UserResource($user->load(['roles', 'permissions'])),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'page_setting' => [
                'title' => 'Edit User - ' . $user->name,
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Users', 'href' => route('admin.users.index')],
                    ['title' => $user->name, 'href' => route('admin.users.show', $user->id)],
                    ['title' => 'Edit', 'href' => route('admin.users.edit', $user->id)],
                ],
                'back_link' => route('admin.users.show', $user->id),
                'action' => route('admin.users.update', $user->id),
            ],
            'page_data' => [
                'user' => $user->load(['roles', 'permissions']),
                'roles' => Role::all(),
                'permissions' => Permission::with('group')->get()
                    ->groupBy('permission_group_id'),
                'userRoles' => $user->roles->pluck('id'),
                'userPermissions' => $user->permissions->pluck('id'),
            ]
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'is_active' => $request->is_active ?? $user->is_active,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        if ($request->has('roles')) {
            $user->syncRoles(Role::whereIn('id', $request->roles)->pluck('name'));
        }

        if ($request->has('permissions')) {
            $user->syncPermissions(Permission::whereIn('id', $request->permissions)->pluck('name'));
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        // Prevent deleting yourself
        if ($user->id === $request->user()?->id) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
