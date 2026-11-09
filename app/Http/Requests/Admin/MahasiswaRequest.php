<?php

namespace App\Http\Requests\Admin;

use App\Models\Mahasiswa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MahasiswaRequest extends FormRequest
{
    /**
     * Hanya pengguna yang lolos middleware permission yang boleh memproses.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('kelola-mahasiswa') ?? false;
    }

    /**
     * Aturan validasi create/update dengan unique-aware terhadap route model.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Mahasiswa|null $mahasiswa */
        $mahasiswa = $this->route('mahasiswa');
        $id = $mahasiswa?->getKey();

        return [
            'departemen_id' => ['required', 'integer', 'exists:departemens,id'],
            'nrp' => [
                'required',
                'string',
                'digits:10',
                Rule::unique('mahasiswas', 'nrp')->ignore($id),
            ],
            'nama' => ['required', 'string', 'min:3', 'max:120'],
            'email' => [
                'required',
                'string',
                'email:filter',
                'max:150',
                Rule::unique('mahasiswas', 'email')->ignore($id),
            ],
            'angkatan' => ['required', 'integer', 'digits:4', 'between:2000,'.((int) date('Y') + 1)],
            'jenis_kelamin' => ['required', Rule::in(array_keys(Mahasiswa::JENIS_KELAMIN))],
            'ipk' => ['required', 'numeric', 'between:0,4'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Pesan validasi dalam bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nrp.digits' => 'NRP ITS harus tepat 10 digit angka (contoh: 5025241097).',
            'nrp.unique' => 'NRP tersebut sudah terdaftar.',
            'email.unique' => 'Email tersebut sudah dipakai mahasiswa lain.',
            'departemen_id.exists' => 'Departemen yang dipilih tidak valid.',
            'angkatan.between' => 'Angkatan tidak berada pada rentang yang wajar.',
        ];
    }

    /**
     * Normalisasi input sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nrp' => is_string($this->input('nrp')) ? preg_replace('/\D/', '', $this->input('nrp')) : $this->input('nrp'),
            'email' => is_string($this->input('email')) ? strtolower(trim($this->input('email'))) : $this->input('email'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
