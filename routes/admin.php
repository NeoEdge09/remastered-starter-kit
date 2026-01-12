<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PermissionGroupController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RouteAccessController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'permission.auto'])->prefix('admin')->name('admin.')->group(function () {
    // Users Management
    Route::resource('users', UserController::class);

    // Roles Management
    Route::resource('roles', RoleController::class);

    // Permissions Management
    Route::resource('permissions', PermissionController::class)->except(['show']);

    // Permission Groups Management
    Route::resource('permission-groups', PermissionGroupController::class)->except(['show']);

    // Menu Management
    Route::post('menus/reorder', [MenuController::class, 'reorder'])->name('menus.reorder');
    Route::resource('menus', MenuController::class)->except(['show']);

    // Route Access Management
    Route::post('route-accesses/scan', [RouteAccessController::class, 'scan'])->name('route-accesses.scan');
    Route::post('route-accesses/bulk-update', [RouteAccessController::class, 'bulkUpdate'])->name('route-accesses.bulk-update');
    Route::post('route-accesses/bulk-destroy', [RouteAccessController::class, 'bulkDestroy'])->name('route-accesses.bulk-destroy');
    Route::post('route-accesses/sync-permissions', [RouteAccessController::class, 'syncPermissions'])->name('route-accesses.sync-permissions');
    Route::resource('route-accesses', RouteAccessController::class)->except(['show']);

    // Activity Logs
    Route::get('activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');
    Route::post('activity-logs/bulk-destroy', [ActivityLogController::class, 'bulkDestroy'])->name('activity-logs.bulk-destroy');
    Route::post('activity-logs/clear', [ActivityLogController::class, 'clear'])->name('activity-logs.clear');
    Route::resource('activity-logs', ActivityLogController::class)->only(['index', 'show', 'destroy']);
});
