<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PegawaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pegawaiId = $this->route('pegawai') ? ($this->route('pegawai')->id ?? $this->route('pegawai')) : null;

        return [
            'users_id'      => 'required|exists:users,id|unique:pegawais,users_id,' . $pegawaiId,
            'nama_staff'    => 'required|string|max:255',
            'jabatan'       => 'required|string|max:255',
            'nidn'          => 'nullable|string|max:50|unique:pegawais,nidn,' . $pegawaiId,
            'nup'           => 'required|string|max:50|unique:pegawais,nup,' . $pegawaiId,
            'homebase'      => 'required|string|max:255',
            'file'          => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'nama_staff.required'   => 'Nama pegawai wajib diisi',
            'users_id.required'     => 'User wajib dipilih',
            'users_id.exists'       => 'User tidak ditemukan',
            'users_id.unique'       => 'User ini sudah terdaftar sebagai pegawai',
            'jabatan.required'      => 'Jabatan wajib diisi',
            'nup.required'          => 'NUP wajib diisi',
            'nup.unique'            => 'NUP ini sudah terdaftar',
            'nidn.unique'           => 'NIDN ini sudah terdaftar',
            'file.mimes'            => 'File harus berformat JPEG, PNG, JPG',
            'file.max'              => 'Ukuran file tidak boleh melebihi 2MB',
        ];
    }
}
