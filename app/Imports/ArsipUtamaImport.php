<?php

namespace App\Imports;

use App\Models\ArsipUtama;
use App\Models\KategoriArsip;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ArsipUtamaImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $kategori = KategoriArsip::firstOrCreate([
            'kategori_arsip' => $row['kategori']
        ]);

        // Cek jika arsip dengan nama, kategori, dan tahun yang sama sudah ada
        $exists = ArsipUtama::where('nama_arsip', $row['nama_arsip'])
            ->where('kategori_arsip_id', $kategori->id)
            ->where('tahun_arsip', $row['tahun'])
            ->exists();

        if ($exists) {
            return null;
        }

        return new ArsipUtama([
            'user_id'           => Auth::id(),
            'kategori_arsip_id' => $kategori->id,
            'tahun_arsip'       => $row['tahun'],
            'nama_arsip'        => $row['nama_arsip'],
            'file_arsip'        => $row['link_file_drive'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'kategori'   => 'required|string',
            'tahun'      => 'required|numeric',
            'nama_arsip' => 'required|string',
        ];
    }
}
