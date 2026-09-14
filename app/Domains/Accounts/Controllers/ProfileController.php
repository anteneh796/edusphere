<?php

namespace App\Domains\Accounts\Controllers;

use App\Domains\Accounts\Requests\UpdatePasswordRequest;
use App\Domains\Accounts\Requests\UpdateProfileRequest;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        return view('profile.index', ['user' => auth()->user()]);
    }

    public function security(): View
    {
        return view('profile.security', ['user' => auth()->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $user->update($request->validated());
        ActivityLogger::log('updated own profile', 'profile', $user->id);

        return back()->with('status', 'Profile updated successfully.');
    }

    public function password(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $user->update(['password' => $request->validated('new_password')]);
        ActivityLogger::log('changed own password', 'profile', $user->id);

        return back()->with('status', 'Password changed successfully.');
    }
}
