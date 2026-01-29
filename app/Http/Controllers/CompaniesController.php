<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CompaniesController extends Controller
{
    public function index(): View
    {
        return view('admin.companies.index', [
            'companies' => Company::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.companies.create');
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        $company = Company::create($data);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'status' => 'active',
            'company_id' => $company->id,
        ]);

        $company->update(['owner_user_id' => $user->id]);

        return redirect()->route('admin.companies.index')->with('success', 'Empresa criada.');
    }

    public function edit(Company $company): View
    {
        return view('admin.companies.edit', ['company' => $company]);
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        $company->update($data);

        if ($company->owner_user_id) {
            $owner = User::find($company->owner_user_id);
            if ($owner) {
                $owner->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                ]);
                if (!empty($data['password'])) {
                    $owner->update(['password' => Hash::make($data['password'])]);
                }
            }
        }

        return redirect()->route('admin.companies.index')->with('success', 'Empresa atualizada.');
    }

    public function impersonate(Company $company): RedirectResponse
    {
        if (!$company->owner_user_id) {
            return back()->withErrors(['impersonate' => 'Empresa sem usuário vinculado.']);
        }

        $user = User::find($company->owner_user_id);
        if (!$user) {
            return back()->withErrors(['impersonate' => 'Usuário não encontrado.']);
        }

        auth()->login($user);
        request()->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function deactivate(Company $company): RedirectResponse
    {
        $company->update(['status' => 'inactive']);
        if ($company->owner_user_id) {
            User::where('id', $company->owner_user_id)->update(['status' => 'inactive']);
        }

        return redirect()->route('admin.companies.index')->with('success', 'Empresa desativada.');
    }
}
