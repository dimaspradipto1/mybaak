<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KategoriArsipRequest extends FormRequest
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
        $kategoriId = $this->route('kategoriarsip') ? ($this->route('kategoriarsip')->id ?? $this->route('kategoriarsip')) : null;

        return [
            'kategori_arsip' => 'required|string|max:255|unique:kategori_arsips,kategori_arsip,' . $kategoriId,
        ];
    }

    public function messages(): array
    {
        return [
            'kategori_arsip.required' => 'Nama kategori arsip wajib diisi.',
            'kategori_arsip.unique'   => 'Kategori arsip ini sudah ada, tidak boleh duplikat.',
            'kategori_arsip.string'   => 'Nama kategori arsip harus berupa teks.',
            'kategori_arsip.max'      => 'Nama kategori arsip maksimal 255 karakter.',
        ];
    }
}
