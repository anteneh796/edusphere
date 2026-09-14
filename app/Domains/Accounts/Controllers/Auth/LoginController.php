<?php

namespace App\Domains\Accounts\Controllers\Auth;

use App\Domains\Accounts\Requests\Auth\LoginRequest;
use App\Domains\Accounts\Services\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Throwable;

class LoginController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function authenticate(LoginRequest $request): RedirectResponse
    {
        $email = strtolower($request->input('email', ''));
        $key = 'login:'.$email;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()
                ->withErrors(['email' => "Too many attempts. Please try again in {$seconds} seconds."])
                ->onlyInput('email');
        }

        try {
            $this->authService->login($request->validated(), (bool) $request->boolean('remember'));
        } catch (Throwable $e) {
            RateLimiter::hit($key, 60);

            return back()
                ->withErrors(['email' => $e->getMessage()])
                ->onlyInput('email');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        $role = auth()->user()?->roles?->first()?->name;
        $home = match ($role) {
            'student' => route('cms.student.dashboard'),
            'parent' => route('cms.parent.dashboard'),
            default => route('dashboard'),
        };

        return redirect()->intended($home);
    }

    public function logout(): RedirectResponse
    {
        $this->authService->logout();

        return redirect()->route('auth.login');
    }
}
