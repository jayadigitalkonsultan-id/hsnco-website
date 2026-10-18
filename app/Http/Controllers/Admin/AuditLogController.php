<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', AuditLog::class);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'outcome' => ['nullable', \Illuminate\Validation\Rule::in(['success', 'failed', 'blocked'])],
            'per_page' => ['nullable', \Illuminate\Validation\Rule::in([10, 20, 50])],
        ]);
        $query = AuditLog::query()->with('user')->latest('created_at');
        if ($search = trim($filters['q'] ?? '')) {
            $query->where(fn ($q) => $q->where('event', 'like', '%'.$search.'%')->orWhere('ip_address', 'like', '%'.$search.'%')->orWhereHas('user', fn ($user) => $user->where('email', 'like', '%'.$search.'%')));
        }
        if ($filters['outcome'] ?? null) $query->where('outcome', $filters['outcome']);
        $logs = $query->paginate((int) ($filters['per_page'] ?? 20))->withQueryString();
        return view('admin.audit.index', compact('logs', 'filters'));
    }
}
