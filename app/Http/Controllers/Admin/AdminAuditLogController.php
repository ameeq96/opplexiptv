<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminAuditLog;
use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureOwner($request);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'method' => ['nullable', Rule::in(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $logs = AdminAuditLog::query()
            ->with('admin:id,name,email')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('admin_name', 'like', "%{$search}%")
                        ->orWhere('admin_email', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('route_name', 'like', "%{$search}%")
                        ->orWhere('target_type', 'like', "%{$search}%")
                        ->orWhere('target_id', 'like', "%{$search}%");
                });
            })
            ->when($filters['method'] ?? null, fn ($query, string $method) => $query->where('method', $method))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.audit-logs.index', compact('logs', 'filters'));
    }

    public function show(Request $request, AdminAuditLog $auditLog): View
    {
        $this->ensureOwner($request);
        $auditLog->loadMissing('admin:id,name,email');

        return view('admin.audit-logs.show', compact('auditLog'));
    }

    private function ensureOwner(Request $request): void
    {
        $admin = $request->user('admin');
        abort_unless($admin instanceof Admin && $this->isOwner($admin), 403);
    }

    private function isOwner(Admin $admin): bool
    {
        if (method_exists($admin, 'isOwner')) {
            return (bool) $admin->isOwner();
        }

        if (method_exists($admin, 'hasRole')) {
            return (bool) $admin->hasRole('owner');
        }

        $role = $admin->getAttribute('role');
        if ($role instanceof BackedEnum) {
            $role = $role->value;
        }

        return $role === null || $role === 'owner';
    }
}
