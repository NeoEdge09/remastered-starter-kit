<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\UserResource;
use App\Models\Activity;
use App\Models\User;
use App\Traits\HasTableFeatures;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    use HasTableFeatures;

    /**
     * Display activity log listing.
     */
    public function index(Request $request): Response
    {
        $query = Activity::with('causer')
            ->latest();

        // Apply table features (search, filters, sorting)
        $query = $this->applyTableQuery($query, $request, [
            'searchColumns' => ['description', 'log_name', 'event'],
            'filters' => [
                'log_name' => 'log_name',
                'event' => 'event',
                'causer_id' => 'causer_id',
            ],
            'sortColumns' => ['created_at', 'log_name', 'event'],
            'defaultSort' => 'created_at',
            'defaultDirection' => 'desc',
        ]);

        // Get filter options
        $logNames = Activity::distinct()->pluck('log_name')->filter()->values();
        $events = Activity::distinct()->pluck('event')->filter()->values();
        $users = User::select('id', 'name')->orderBy('name')->get();

        // Get statistics
        $stats = [
            'today' => Activity::today()->count(),
            'thisWeek' => Activity::thisWeek()->count(),
            'thisMonth' => Activity::thisMonth()->count(),
            'total' => Activity::count(),
        ];

        return Inertia::render('Admin/ActivityLog/Index', [
            'page_setting' => [
                'title' => 'Activity Log',
                'subtitle' => 'View and manage system activity logs',
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Activity Log', 'href' => route('admin.activity-logs.index')],
                ],
            ],
            'page_data' => [
                'activities' => ActivityLogResource::collection($query->paginate($this->getPerPage($request))->withQueryString()),
                'filters' => $this->getTableFilters($request),
                'logNames' => $logNames,
                'events' => $events,
                'users' => $users,
                'stats' => $stats,
            ],
        ]);
    }

    /**
     * Display a specific activity log detail.
     */
    public function show(Activity $activityLog): Response
    {
        $activityLog->load('causer', 'subject');

        return Inertia::render('Admin/ActivityLog/Show', [
            'page_setting' => [
                'title' => 'Activity Log Detail',
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Activity Log', 'href' => route('admin.activity-logs.index')],
                    ['title' => 'Detail', 'href' => route('admin.activity-logs.show', $activityLog->id)],
                ],
                'back_link' => route('admin.activity-logs.index'),
            ],
            'page_data' => [
                'activity' => $activityLog,
            ],
        ]);
    }

    /**
     * Delete an activity log entry.
     */
    public function destroy(Activity $activityLog)
    {
        $activityLog->delete();

        return redirect()->route('admin.activity-logs.index')
            ->with('success', 'Activity log entry deleted successfully.');
    }

    /**
     * Delete multiple activity log entries.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:activity_log,id',
        ]);

        Activity::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.activity-logs.index')
            ->with('success', count($request->ids) . ' activity log entries deleted successfully.');
    }

    /**
     * Clear all activity logs.
     */
    public function clear(Request $request)
    {
        $request->validate([
            'confirm' => 'required|in:DELETE_ALL',
        ]);

        Activity::truncate();

        return redirect()->route('admin.activity-logs.index')
            ->with('success', 'All activity logs have been cleared.');
    }

    /**
     * Export activity logs.
     */
    public function export(Request $request)
    {
        $query = Activity::with('causer')->latest();

        // Apply filters
        if ($request->filled('log_name')) {
            $query->forLog($request->log_name);
        }

        if ($request->filled('event')) {
            $query->forEvent($request->event);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->get();

        $csvData = "ID,Log Name,Description,Event,Subject Type,Subject ID,Causer,Created At\n";

        foreach ($activities as $activity) {
            $csvData .= sprintf(
                "%d,%s,\"%s\",%s,%s,%s,%s,%s\n",
                $activity->id,
                $activity->log_name,
                str_replace('"', '""', $activity->description),
                $activity->event,
                $activity->subject_type_name,
                $activity->subject_id ?? 'N/A',
                $activity->causer?->name ?? 'System',
                $activity->created_at->format('Y-m-d H:i:s')
            );
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="activity_logs_' . now()->format('Y-m-d_His') . '.csv"');
    }
}
