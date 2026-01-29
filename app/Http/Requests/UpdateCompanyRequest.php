<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->route('company')?->id;
        $ownerId = $this->route('company')?->owner_user_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:companies,slug,'.$companyId],
            'status' => ['required', 'in:active,inactive'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$ownerId],
            'password' => ['nullable', 'min:8', 'confirmed'],
        ];
    }
}
