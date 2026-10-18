<?php

namespace App\Http\Controllers\Admin;

use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\AuditLog;

class DashboardController extends AdminController
{
    public function __invoke(Request $request)
    {
        $counts = [];
        $chart = array_fill_keys(['draft', 'review', 'published', 'archived'], 0);
        $recent = collect();
        foreach (config('admin.modules') as $module => $definition) {
            $query = $this->contentQuery($request, $definition['model']);
            $counts[$module] = (clone $query)->count();
            foreach ((clone $query)->selectRaw('status, COUNT(*) AS total')->groupBy('status')->get() as $row) {
                $chart[$row->status->value] += $row->total;
            }
            $recent = $recent->concat((clone $query)->orderByDesc('updated_at')->limit(5)->get()->map(fn ($record) => [
                'title' => $record->{$definition['title_field']}, 'module' => $definition['label'],
                'status' => $record->status, 'updated_at' => $record->updated_at,
                'url' => route('admin.'.$module.'.edit', $record),
            ]));
        }
        $unread = ! $this->preview($request) && Gate::allows('viewAny', Enquiry::class) ? Enquiry::where('status', 'new')->count() : null;
        $recent = $recent->sortByDesc('updated_at')->take(6);
        $recentActivity = ! $this->preview($request) && Gate::allows('viewAny', AuditLog::class)
            ? AuditLog::query()->with('user')->latest('created_at')->limit(6)->get()
            : collect();

        return view('admin.dashboard', compact('counts', 'chart', 'recent', 'unread', 'recentActivity'));
    }
}
