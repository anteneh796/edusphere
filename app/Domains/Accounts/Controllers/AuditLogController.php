<?php

namespace App\Domains\Accounts\Controllers;

use App\Domains\Accounts\Models\AuditLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->with('user')
            ->when($request->query('q'), fn ($query, $term) => $query->where(function ($inner) use ($term) {
                $inner->where('action', 'like', "%{$term}%")
                    ->orWhere('module', 'like', "%{$term}%")
                    ->orWhere('record_id', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($user) => $user->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%"));
            }))
            ->when($request->query('module'), fn ($query, $module) => $query->where('module', $module))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $modules = AuditLog::query()->distinct()->orderBy('module')->pluck('module')->mapWithKeys(fn ($module) => [$module => str($module)->title()->toString()]);

        return view('audit.index', compact('logs', 'modules'));
    }

    public function show(AuditLog $auditLog): View
    {
        $auditLog->load('user');

        return view('audit.show', compact('auditLog'));
    }
}
