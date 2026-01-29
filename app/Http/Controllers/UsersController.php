<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UsersController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.index', [
            'users' => User::where('role', 'user')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();
        $data['role'] = 'user';
        $data['status'] = 'active';
        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'Usuário criado.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Usuário atualizado.');
    }


    public function impersonate(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        if ($user->role !== 'user') {
            return back()->withErrors(['impersonate' => 'Apenas empresas podem ser acessadas.']);
        }

        request()->session()->put('impersonator_id', auth()->id());
        auth()->login($user);
        request()->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function deactivate(User $user): RedirectResponse
    {
        $this->authorize('deactivate', $user);

        $user->update(['status' => 'inactive']);

        return redirect()->route('admin.users.index')->with('success', 'Usuário desativado.');
    }
}
