<?php

namespace App\Imports;

use App\Models\SuratAktif;
use App\Models\User;
use App\Models\ProgramStudi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class SuratAktifImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Cari User ID berdasarkan Nama
        $user = User::where('name', 'like', '%' . ($row['nama_mahasiswa'] ?? '') . '%')->first();
        
        // Bersihkan nama prodi (ganti spasi dengan tanda hubung jika perlu)
        $namaProdiSearch = str_replace(' ', '-', ($row['program_studi'] ?? ''));
        $prodi = ProgramStudi::where('program_studi', 'like', '%' . $namaProdiSearch . '%')
                    ->orWhere('program_studi', 'like', '%' . ($row['program_studi'] ?? '') . '%')
                    ->first();

        // Jika user atau prodi tidak ditemukan, jangan lanjut (mencegah SQL error)
        if (!$user || !$prodi) {
            if (!$user) Log::warning('Import Surat Aktif: Mahasiswa ' . ($row['nama_mahasiswa'] ?? '???') . ' tidak ditemukan.');
            if (!$prodi) Log::warning('Import Surat Aktif: Prodi ' . ($row['program_studi'] ?? '???') . ' tidak ditemukan di database.');
            return null;
        }

        // Cek jika nomor surat sudah ada agar tidak double data
        if (!empty($row['no_surat'])) {
            $exists = SuratAktif::where('no_surat', $row['no_surat'])->exists();
            if ($exists) {
                Log::info('Import Surat Aktif: No surat ' . $row['no_surat'] . ' sudah ada, dilewati.');
                return null;
            }
        }

        return new SuratAktif([
            'users_id'           => $user->id,
            'program_studi_id'   => $prodi->id,
            'no_surat'           => $row['no_surat'] ?? null,
            'tempat_lahir'       => $row['tempat_lahir'] ?? null,
            'tgl_lahir'          => $row['tgl_lahir'] ?? null,
            'npm'                => $row['npm'] ?? $user->email,
            'jenjang_pendidikan' => $row['jenjang_pendidikan'] ?? 'S1',
            'fakultas'           => $row['fakultas'] ?? 'ILMU KESEHATAN',
            'status'             => strtolower($row['status'] ?? 'pending'),
            'semester'           => $row['semester'] ?? null,
            'status_semester'    => $row['status_semester'] ?? 'Genap',
            'tahun_akademik'     => $row['tahun_akademik'] ?? date('Y') . '/' . (date('Y') + 1),
        ]);
    }
}
