<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the migrations.
     */
    public function run(): void
    {
        Permission::query()->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_permission')->truncate();
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $permissions = [
            // Dashboard
            ['name' => 'Lihat Dashboard', 'slug' => 'dashboard_view', 'module' => 'Dashboard'],

            // Layanan Mahasiswa
            ['name' => 'Lihat Surat Keterangan Aktif', 'slug' => 'surat_aktif_view', 'module' => 'Layanan Mahasiswa'],
            ['name' => 'Tambah Surat Keterangan Aktif', 'slug' => 'surat_aktif_create', 'module' => 'Layanan Mahasiswa'],
            ['name' => 'Edit Surat Keterangan Aktif', 'slug' => 'surat_aktif_edit', 'module' => 'Layanan Mahasiswa'],
            ['name' => 'Hapus Surat Keterangan Aktif', 'slug' => 'surat_aktif_delete', 'module' => 'Layanan Mahasiswa'],
            
            ['name' => 'Lihat Surat Layanan Akademik', 'slug' => 'surat_akademik_view', 'module' => 'Layanan Mahasiswa'],
            ['name' => 'Tambah Surat Layanan Akademik', 'slug' => 'surat_akademik_create', 'module' => 'Layanan Mahasiswa'],
            ['name' => 'Edit Surat Layanan Akademik', 'slug' => 'surat_akademik_edit', 'module' => 'Layanan Mahasiswa'],
            ['name' => 'Hapus Surat Layanan Akademik', 'slug' => 'surat_akademik_delete', 'module' => 'Layanan Mahasiswa'],

            // Arsip
            ['name' => 'Lihat Arsip Utama', 'slug' => 'arsip_utama_view', 'module' => 'Arsip'],
            ['name' => 'Tambah Arsip Utama', 'slug' => 'arsip_utama_create', 'module' => 'Arsip'],
            ['name' => 'Edit Arsip Utama', 'slug' => 'arsip_utama_edit', 'module' => 'Arsip'],
            ['name' => 'Hapus Arsip Utama', 'slug' => 'arsip_utama_delete', 'module' => 'Arsip'],

            ['name' => 'Lihat SK Kepanitiaan', 'slug' => 'sk_kepanitiaan_view', 'module' => 'Arsip'],
            ['name' => 'Tambah SK Kepanitiaan', 'slug' => 'sk_kepanitiaan_create', 'module' => 'Arsip'],
            ['name' => 'Edit SK Kepanitiaan', 'slug' => 'sk_kepanitiaan_edit', 'module' => 'Arsip'],
            ['name' => 'Hapus SK Kepanitiaan', 'slug' => 'sk_kepanitiaan_delete', 'module' => 'Arsip'],

            ['name' => 'Lihat LPJ Kepanitiaan', 'slug' => 'lpj_kepanitiaan_view', 'module' => 'Arsip'],
            ['name' => 'Tambah LPJ Kepanitiaan', 'slug' => 'lpj_kepanitiaan_create', 'module' => 'Arsip'],
            ['name' => 'Edit LPJ Kepanitiaan', 'slug' => 'lpj_kepanitiaan_edit', 'module' => 'Arsip'],
            ['name' => 'Hapus LPJ Kepanitiaan', 'slug' => 'lpj_kepanitiaan_delete', 'module' => 'Arsip'],

            ['name' => 'Lihat Kurikulum Prodi', 'slug' => 'kurikulum_view', 'module' => 'Arsip'],
            ['name' => 'Tambah Kurikulum Prodi', 'slug' => 'kurikulum_create', 'module' => 'Arsip'],
            ['name' => 'Edit Kurikulum Prodi', 'slug' => 'kurikulum_edit', 'module' => 'Arsip'],
            ['name' => 'Hapus Kurikulum Prodi', 'slug' => 'kurikulum_delete', 'module' => 'Arsip'],

            ['name' => 'Lihat Pedoman', 'slug' => 'pedoman_view', 'module' => 'Arsip'],
            ['name' => 'Tambah Pedoman', 'slug' => 'pedoman_create', 'module' => 'Arsip'],
            ['name' => 'Edit Pedoman', 'slug' => 'pedoman_edit', 'module' => 'Arsip'],
            ['name' => 'Hapus Pedoman', 'slug' => 'pedoman_delete', 'module' => 'Arsip'],

            ['name' => 'Lihat SOP Akademik', 'slug' => 'sop_akademik_view', 'module' => 'Arsip'],
            ['name' => 'Tambah SOP Akademik', 'slug' => 'sop_akademik_create', 'module' => 'Arsip'],
            ['name' => 'Edit SOP Akademik', 'slug' => 'sop_akademik_edit', 'module' => 'Arsip'],
            ['name' => 'Hapus SOP Akademik', 'slug' => 'sop_akademik_delete', 'module' => 'Arsip'],

            ['name' => 'Lihat Wasdalbin', 'slug' => 'wasdalbin_view', 'module' => 'Arsip'],
            ['name' => 'Tambah Wasdalbin', 'slug' => 'wasdalbin_create', 'module' => 'Arsip'],
            ['name' => 'Edit Wasdalbin', 'slug' => 'wasdalbin_edit', 'module' => 'Arsip'],
            ['name' => 'Hapus Wasdalbin', 'slug' => 'wasdalbin_delete', 'module' => 'Arsip'],

            // Master Data
            ['name' => 'Lihat Role Akses', 'slug' => 'role_view', 'module' => 'Master Data'],
            ['name' => 'Tambah Role Akses', 'slug' => 'role_create', 'module' => 'Master Data'],
            ['name' => 'Edit Role Akses', 'slug' => 'role_edit', 'module' => 'Master Data'],
            ['name' => 'Hapus Role Akses', 'slug' => 'role_delete', 'module' => 'Master Data'],
            
            ['name' => 'Lihat Pengguna', 'slug' => 'users_view', 'module' => 'Master Data'],
            ['name' => 'Tambah Pengguna', 'slug' => 'users_create', 'module' => 'Master Data'],
            ['name' => 'Edit Pengguna', 'slug' => 'users_edit', 'module' => 'Master Data'],
            ['name' => 'Hapus Pengguna', 'slug' => 'users_delete', 'module' => 'Master Data'],
            
            ['name' => 'Lihat Pegawai', 'slug' => 'pegawai_view', 'module' => 'Master Data'],
            ['name' => 'Tambah Pegawai', 'slug' => 'pegawai_create', 'module' => 'Master Data'],
            ['name' => 'Edit Pegawai', 'slug' => 'pegawai_edit', 'module' => 'Master Data'],
            ['name' => 'Hapus Pegawai', 'slug' => 'pegawai_delete', 'module' => 'Master Data'],
            
            ['name' => 'Lihat Dosen', 'slug' => 'dosen_view', 'module' => 'Master Data'],
            ['name' => 'Tambah Dosen', 'slug' => 'dosen_create', 'module' => 'Master Data'],
            ['name' => 'Edit Dosen', 'slug' => 'dosen_edit', 'module' => 'Master Data'],
            ['name' => 'Hapus Dosen', 'slug' => 'dosen_delete', 'module' => 'Master Data'],
            
            ['name' => 'Lihat Mahasiswa', 'slug' => 'mahasiswa_view', 'module' => 'Master Data'],
            ['name' => 'Tambah Mahasiswa', 'slug' => 'mahasiswa_create', 'module' => 'Master Data'],
            ['name' => 'Edit Mahasiswa', 'slug' => 'mahasiswa_edit', 'module' => 'Master Data'],
            ['name' => 'Hapus Mahasiswa', 'slug' => 'mahasiswa_delete', 'module' => 'Master Data'],
            
            ['name' => 'Lihat Jenis SK', 'slug' => 'jenis_sk_view', 'module' => 'Master Data'],
            ['name' => 'Tambah Jenis SK', 'slug' => 'jenis_sk_create', 'module' => 'Master Data'],
            ['name' => 'Edit Jenis SK', 'slug' => 'jenis_sk_edit', 'module' => 'Master Data'],
            ['name' => 'Hapus Jenis SK', 'slug' => 'jenis_sk_delete', 'module' => 'Master Data'],
            
            ['name' => 'Lihat Kategori Arsip', 'slug' => 'kategori_arsip_view', 'module' => 'Master Data'],
            ['name' => 'Tambah Kategori Arsip', 'slug' => 'kategori_arsip_create', 'module' => 'Master Data'],
            ['name' => 'Edit Kategori Arsip', 'slug' => 'kategori_arsip_edit', 'module' => 'Master Data'],
            ['name' => 'Hapus Kategori Arsip', 'slug' => 'kategori_arsip_delete', 'module' => 'Master Data'],
            
            ['name' => 'Lihat Tahun Akademik', 'slug' => 'tahun_akademik_view', 'module' => 'Master Data'],
            ['name' => 'Tambah Tahun Akademik', 'slug' => 'tahun_akademik_create', 'module' => 'Master Data'],
            ['name' => 'Edit Tahun Akademik', 'slug' => 'tahun_akademik_edit', 'module' => 'Master Data'],
            ['name' => 'Hapus Tahun Akademik', 'slug' => 'tahun_akademik_delete', 'module' => 'Master Data'],
            
            ['name' => 'Lihat Program Studi', 'slug' => 'program_studi_view', 'module' => 'Master Data'],
            ['name' => 'Tambah Program Studi', 'slug' => 'program_studi_create', 'module' => 'Master Data'],
            ['name' => 'Edit Program Studi', 'slug' => 'program_studi_edit', 'module' => 'Master Data'],
            ['name' => 'Hapus Program Studi', 'slug' => 'program_studi_delete', 'module' => 'Master Data'],

            ['name' => 'Lihat Unit Kerja', 'slug' => 'unit_kerja_view', 'module' => 'Master Data'],
            ['name' => 'Tambah Unit Kerja', 'slug' => 'unit_kerja_create', 'module' => 'Master Data'],
            ['name' => 'Edit Unit Kerja', 'slug' => 'unit_kerja_edit', 'module' => 'Master Data'],
            ['name' => 'Hapus Unit Kerja', 'slug' => 'unit_kerja_delete', 'module' => 'Master Data'],
            ['name' => 'Backup Database & Storage', 'slug' => 'backup_view', 'module' => 'Master Data'],

            // Rekapitulasi
            ['name' => 'Lihat Rekapitulasi Arsip', 'slug' => 'rekapitulasi_arsip_view', 'module' => 'Rekapitulasi'],
            ['name' => 'Lihat Rekapitulasi Surat Aktif', 'slug' => 'rekapitulasi_surat_aktif_view', 'module' => 'Rekapitulasi'],

            // Portal Artikel
            ['name' => 'Lihat Portal Artikel', 'slug' => 'artikel_view', 'module' => 'Portal Artikel'],
            ['name' => 'Tambah Portal Artikel', 'slug' => 'artikel_create', 'module' => 'Portal Artikel'],
            ['name' => 'Edit Portal Artikel', 'slug' => 'artikel_edit', 'module' => 'Portal Artikel'],
            ['name' => 'Hapus Portal Artikel', 'slug' => 'artikel_delete', 'module' => 'Portal Artikel'],
        ];

        foreach ($permissions as $permission) {
            \App\Models\Permission::updateOrCreate(['slug' => $permission['slug']], $permission);
        }
    }
}
