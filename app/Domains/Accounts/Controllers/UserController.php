<?php

namespace App\Domains\Accounts\Controllers;

use App\Domains\Accounts\Models\User;
use App\Domains\Accounts\Requests\StoreUserRequest;
use App\Domains\Accounts\Requests\UpdateUserRequest;
use App\Domains\Accounts\Services\UserService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = $this->userService->paginate($request->only(['q', 'role', 'status']));
        $roles = $this->userService->roles();

        return view('users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        $roleOptions = $this->userService->roles();

        return view('users.create', ['roleOptions' => $roleOptions]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = $this->userService->create($request->validated(), $request->input('roles', []));
        ActivityLogger::log('created user '.$user->full_name, 'users', $user->id);

        return redirect()->route('users.index')
            ->with('status', 'User "'.$user->full_name.'" created successfully.');
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $roleOptions = $this->userService->roles();

        return view('users.edit', compact('user'))->with('roleOptions', $roleOptions);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $updated = $this->userService->update($user, $request->validated(), $request->input('roles', []));
        ActivityLogger::log('updated user '.$user->full_name, 'users', $user->id);

        return redirect()->route('users.index')
            ->with('status', 'User "'.$updated->full_name.'" updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->userService->delete($user);
        ActivityLogger::log('deleted user '.$user->full_name, 'users', $user->id);

        return redirect()->route('users.index')
            ->with('status', 'User "'.$user->full_name.'" deleted.');
    }
}
