<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $logs = AuditLog::with('user')
            ->when($request->event, fn($q, $e) => $q->where('event', $e))
            ->when($request->user_id, fn($q, $u) => $q->where('user_id', $u))
            ->when($request->from,  fn($q, $f) => $q->whereDate('created_at', '>=', $f))
            ->when($request->to,    fn($q, $t) => $q->whereDate('created_at', '<=', $t))
            ->when($request->ip,    fn($q, $ip) => $q->where('ip_address', 'like', "%$ip%"))
            ->latest()
            ->paginate(50)
            ->withQueryString()
            ->through(fn($log) => [
                'id'             => $log->id,
                'event'          => $log->event,
                'user'           => $log->user ? ['id' => $log->user->id, 'name' => $log->user->name, 'email' => $log->user->email] : null,
                'auditable_type' => class_basename($log->auditable_type ?? ''),
                'auditable_id'   => $log->auditable_id,
                'old_values'     => $log->old_values,
                'new_values'     => $log->new_values,
                'ip_address'     => $log->ip_address,
                'user_agent'     => $log->user_agent,
                'created_at'     => $log->created_at,
            ]);

        $eventTypes = AuditLog::distinct()->orderBy('event')->pluck('event');
        $users      = User::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs'        => $logs,
            'event_types' => $eventTypes,
            'users'       => $users,
            'filters'     => $request->only('event', 'user_id', 'from', 'to', 'ip'),
        ]);
    }
}
