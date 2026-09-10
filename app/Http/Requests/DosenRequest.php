<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DosenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $dosenId = $this->route('dosen') ? ($this->route('dosen')->id ?? $this->route('dosen')) : null;

        return [
            'nama_dosen'       => 'required|string|max:255',
            'email'            => 'required|email|unique:dosens,email,' . $dosenId,
            'nidn'             => 'nullable|numeric|unique:dosens,nidn,' . $dosenId,
            'nuptk'            => 'nullable|numeric|unique:dosens,nuptk,' . $dosenId,
            'nup'              => 'nullable|numeric|unique:dosens,nup,' . $dosenId,
            'program_studi_id' => 'required|exists:program_studis,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_dosen.required'       => 'Nama dosen wajib diisi.',
            'nama_dosen.max'            => 'Nama dosen maksimal 255 karakter.',
            'email.required'            => 'Email wajib diisi.',
            'email.email'               => 'Email tidak valid.',
            'email.unique'              => 'Email ini sudah terdaftar, tidak boleh duplikat.',
            'nidn.numeric'              => 'NIDN harus berupa angka.',
            'nidn.unique'               => 'NIDN ini sudah terdaftar, tidak boleh duplikat.',
            'nuptk.numeric'             => 'NUPTK harus berupa angka.',
            'nuptk.unique'              => 'NUPTK ini sudah terdaftar, tidak boleh duplikat.',
            'nup.numeric'               => 'NUP harus berupa angka.',
            'nup.unique'                => 'NUP ini sudah terdaftar, tidak boleh duplikat.',
            'program_studi_id.required' => 'Program studi wajib diisi.',
            'program_studi_id.exists'   => 'Program studi tidak valid.',
        ];
    }
}
