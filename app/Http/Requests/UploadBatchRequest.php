<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period' => ['required', 'date_format:Y-m'],
            'meta_csv' => ['required', 'file', 'mimes:csv,txt'],
            'intelbras_xlsx' => ['required', 'file', 'mimes:xlsx'],
        ];
    }

}
