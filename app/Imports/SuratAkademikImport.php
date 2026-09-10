<?php

namespace App\Imports;

use App\Models\SuratAkademik;
use App\Models\User;
use App\Models\ProgramStudi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class SuratAkademikImport implements ToModel, WithHeadingRow
{
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
            if (!$user) Log::warning('Import Surat Akademik: Mahasiswa ' . ($row['nama_mahasiswa'] ?? '???') . ' tidak ditemukan.');
            if (!$prodi) Log::warning('Import Surat Akademik: Prodi ' . ($row['program_studi'] ?? '???') . ' tidak ditemukan di database.');
            return null;
        }

        // Cek jika data pengajuan surat akademik sudah ada agar tidak double
        $exists = SuratAkademik::where('users_id', $user->id)
                    ->where('semester', $row['semester'] ?? null)
                    ->where('permohonan', $row['permohonan'] ?? 'Cuti Akademik')
                    ->exists();
        if ($exists) {
            Log::info('Import Surat Akademik: Data sudah ada untuk ' . $user->name . ', dilewati.');
            return null;
        }

        return new SuratAkademik([
            'users_id'                  => $user->id,
            'program_studi_id'          => $prodi->id,
            'npm'                       => $row['npm'] ?? $user->email,
            'status'                    => strtolower($row['status'] ?? 'pending'),
            'status_cuti'               => $row['status_cuti'] ?? 'Pending',
            'alamat'                    => $row['alamat'] ?? null,
            'no_wa'                     => $row['no_wa'] ?? null,
            'semester'                  => $row['semester'] ?? null,
            'status_semester'           => $row['status_semester'] ?? null,
            'tahun_akademik'            => $row['tahun_akademik'] ?? null,
            'permohonan'                => $row['permohonan'] ?? 'Cuti Akademik',
            'alasan_cuti'               => $row['alasan_cuti'] ?? null,
            'dosen_pembimbing_akademik' => $row['dosen_pembimbing_akademik'] ?? null,
            'kaprodi'                   => $row['kaprodi'] ?? 'Pending',
            'kabaak'                    => $row['kabaak'] ?? 'Pending',
            'kabauk'                    => $row['kabauk'] ?? 'Pending',
        ]);
    }
}
