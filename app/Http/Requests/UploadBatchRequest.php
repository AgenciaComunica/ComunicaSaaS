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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $period = $this->input('period');
            if (!$period || !str_contains($period, '-')) {
                return;
            }
            [$year, $month] = array_map('intval', explode('-', $period));
            $exists = \App\Models\UploadBatch::where('year', $year)->where('month', $month)->exists();
            if ($exists) {
                $validator->errors()->add('period', 'Já existe um upload para este mês/ano.');
            }
        });
    }
}
