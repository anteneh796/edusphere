<?php

namespace App\Domains\Accounts\Controllers;

use App\Domains\Accounts\Models\LoginLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SessionSecurityController extends Controller
{
    public function index(Request $request): View
    {
        $sessions = DB::table('sessions')
            ->leftJoin('users', 'users.id', '=', 'sessions.user_id')
            ->select('sessions.*', DB::raw("CONCAT_WS(' ', users.first_name, users.last_name) as user_name"))
            ->when($request->input('q'), function ($query, $search) {
                $query->where('users.first_name', 'like', "%{$search}%")
                    ->orWhere('users.last_name', 'like', "%{$search}%")
                    ->orWhere('sessions.ip_address', 'like', "%{$search}%");
            })
            ->orderByDesc('sessions.last_activity')
            ->paginate(20)
            ->withQueryString();

        return view('security.sessions', ['sessions' => $sessions]);
    }

    public function revoke(Request $request, string $sessionId): RedirectResponse
    {
        $userId = (int) ($request->input('user_id') ?? 0);
        $current = $request->session()->getId();

        if ($sessionId !== $current) {
            DB::table('sessions')->where('id', $sessionId)->delete();
            LoginLog::where('session_id', $sessionId)
                ->where('event', 'login')
                ->update(['logout_at' => now()]);
        }

        return back()->with('status', $sessionId === $current
            ? 'This is your current session and was left active.'
            : 'Session revoked successfully.');
    }

    public function loginHistory(Request $request): View
    {
        $logs = LoginLog::with('user:id,first_name,last_name')
            ->when($request->input('q'), function ($query, $search) {
                $query->where('identifier', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($q) => $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            })
            ->when($request->input('event'), fn ($query, $event) => $query->where('event', $event))
            ->latest('login_at')
            ->paginate(25)
            ->withQueryString();

        return view('security.login-history', ['logs' => $logs]);
    }
}
