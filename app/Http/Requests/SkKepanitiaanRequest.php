<?php

namespace App\Http\Requests;

use App\Models\RekapitulasiArsip;
use Illuminate\Foundation\Http\FormRequest;

class SkKepanitiaanRequest extends FormRequest
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
        $skId = $this->route('skkepanitiaan') ? ($this->route('skkepanitiaan')->id ?? $this->route('skkepanitiaan')) : null;

        return [
            'tahun_akademik_id' => 'required',
            'semester' => 'required',
            'nama_dokumen' => 'required',
            'nomor_sk' => 'required|string|max:255|unique:sk_kepanitiaans,nomor_sk,' . $skId,
            'jenissk_id' => 'required',
            'fakultas' => 'required',
            'file' => ($this->isMethod('post') ? 'required' : 'nullable') . '|file|mimes:pdf|max:51200',
        ];
    }

    public function messages()
    {
        return [
            'tahun_akademik_id.required' => 'Tahun Akademik wajib diisi',
            'semester.required' => 'Semester wajib diisi',
            'nama_dokumen.required' => 'Nama Dokumen wajib diisi',
            'nomor_sk.required' => 'Nomor SK wajib diisi',
            'nomor_sk.unique' => 'Nomor SK sudah digunakan, tidak boleh ada nomor SK ganda.',
            'jenissk_id.required' => 'Jenis SK wajib diisi',
            'fakultas.required' => 'Fakultas wajib diisi',
            'file.required' => 'File wajib diisi',
            'file.file' => 'File harus berupa file',
            'file.mimes' => 'File harus berupa PDF',
            'file.max' => 'File maksimal 50MB',
        ];
    }
}
