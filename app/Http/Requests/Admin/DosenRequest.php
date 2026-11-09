<?php

namespace App\Http\Requests\Admin;

use App\Models\Dosen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DosenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kelola-dosen') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Dosen|null $dosen */
        $dosen = $this->route('dosen');
        $id = $dosen?->getKey();

        return [
            'departemen_id' => ['required', 'integer', 'exists:departemens,id'],
            'nidn' => [
                'required',
                'string',
                'digits:10',
                Rule::unique('dosens', 'nidn')->ignore($id),
            ],
            'nama' => ['required', 'string', 'min:3', 'max:120'],
            'email' => [
                'required',
                'string',
                'email:filter',
                'max:150',
                Rule::unique('dosens', 'email')->ignore($id),
            ],
            'jabatan' => ['required', 'string', 'max:60'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nidn.digits' => 'NIDN harus tepat 10 digit angka.',
            'nidn.unique' => 'NIDN tersebut sudah terdaftar.',
            'email.unique' => 'Email tersebut sudah dipakai dosen lain.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nidn' => is_string($this->input('nidn')) ? preg_replace('/\D/', '', $this->input('nidn')) : $this->input('nidn'),
            'email' => is_string($this->input('email')) ? strtolower(trim($this->input('email'))) : $this->input('email'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
