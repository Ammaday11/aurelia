<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.index', [
            'title' => 'Users',
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', ['title' => 'New User']);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')->with('success', "\"{$user->name}\" was created.");
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', ['title' => 'Edit User', 'editUser' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', "\"{$user->name}\" was updated.");
    }

    public function destroy(User $user): RedirectResponse
    {
        // Friendlier than letting these two fall through to the policy's
        // generic 403 — UserPolicy::delete() still enforces both as the
        // authoritative gate below, this just explains why up front.
        if (auth()->user()->is($user)) {
            return back()->with('error', 'You cannot delete your own account while logged in.');
        }

        if (User::query()->count() <= 1) {
            return back()->with('error', 'You cannot delete the last remaining admin account.');
        }

        $this->authorize('delete', $user);

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "\"{$name}\" was deleted.");
    }
}
