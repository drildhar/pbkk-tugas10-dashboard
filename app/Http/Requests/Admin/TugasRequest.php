<?php

namespace App\Http\Requests\Admin;

use App\Models\Tugas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TugasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kelola-tugas') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mahasiswa_id' => ['required', 'integer', 'exists:mahasiswas,id'],
            'dosen_id' => ['nullable', 'integer', 'exists:dosens,id'],
            'mata_kuliah' => ['required', 'string', 'min:3', 'max:120'],
            'judul' => ['required', 'string', 'min:5', 'max:180'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(array_keys(Tugas::STATUSES))],
            'prioritas' => ['required', Rule::in(array_keys(Tugas::PRIORITAS))],
            'deadline' => ['required', 'date', 'after_or_equal:2000-01-01'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mahasiswa_id.exists' => 'Mahasiswa yang dipilih tidak valid.',
            'dosen_id.exists' => 'Dosen yang dipilih tidak valid.',
            'status.in' => 'Status tugas tidak dikenali.',
            'prioritas.in' => 'Prioritas tugas tidak dikenali.',
        ];
    }
}
