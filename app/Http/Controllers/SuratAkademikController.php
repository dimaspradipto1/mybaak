<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Dosen;
use App\Models\Pegawai;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Models\SuratAkademik;
use RealRashid\SweetAlert\Facades\Alert;
use App\DataTables\SuratAkademikDataTable;
use Illuminate\Support\Facades\Auth;

use App\Imports\SuratAkademikImport;
use App\Exports\SuratAkademikTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\HandleGoogleDrive;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ArsipUtama;
use App\Models\KategoriArsip;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SuratAkademikController extends Controller
{
    use HandleGoogleDrive;
    public function exportTemplate()
    {
        return Excel::download(new SuratAkademikTemplateExport, 'template_surat_akademik.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new SuratAkademikImport, $request->file('file'));
            Alert::success('Berhasil', 'Data Surat Akademik berhasil diimport')->autoclose(3000)->toToast();
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Terjadi kesalahan saat import: ' . $e->getMessage())->autoclose(5000)->toToast();
        }

        return redirect()->route('suratAkademik.index');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(SuratAkademikDataTable $dataTable)
    {
        $title = 'Surat Akademik';
        return $dataTable->render('pages.suratAkademik.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $dosens = Dosen::all();
        $title = 'Form Surat Akademik';

        if (Auth::user()->is_mahasiswa) {
            $users = User::where('id', Auth::id())->get();
        } else {
            $users = User::whereHas('role', function ($query) {
                $query->where('nama_role', 'LIKE', '%MAHASISWA%');
            })->orWhereHas('mahasiswa')->with('mahasiswa')->get();

            if ($users->isEmpty()) {
                $users = User::with('mahasiswa')->get();
            }
        }

        $programStudi = ProgramStudi::all();
        return view('pages.suratAkademik.create', compact('users', 'programStudi', 'title', 'dosens'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $userId = Auth::user()->is_mahasiswa ? Auth::id() : $request->users_id;
        $mahasiswa = Mahasiswa::where('users_id', $userId)->first();

        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Data mahasiswa tidak ditemukan!');
        }

        if (!$mahasiswa->programStudi) {
            return redirect()->back()->with('error', 'Program studi mahasiswa tidak ditemukan!');
        }

        // Cek apakah mahasiswa masih memiliki pengajuan surat akademik yang berstatus pending
        $existingPending = SuratAkademik::where('users_id', $userId)
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            Alert::warning('Peringatan', 'Pengguna ini masih memiliki pengajuan Surat Layanan Akademik yang berstatus Pending. Mohon tunggu verifikasi selesai.')
                ->autoclose(5000)->toToast()->timerProgressBar();
            return redirect()->back()->withInput();
        }

        // Cek apakah pengajuan identik sudah pernah dibuat untuk semester dan permohonan yang sama
        $existingIdentical = SuratAkademik::where('users_id', $userId)
            ->where('semester', $request->semester)
            ->where('permohonan', $request->permohonan)
            ->whereIn('status', ['pending', 'diterima'])
            ->first();

        if ($existingIdentical) {
            Alert::warning('Peringatan', 'Pengajuan untuk semester dan permohonan tersebut sudah ada sebelumnya.')
                ->autoclose(5000)->toToast()->timerProgressBar();
            return redirect()->back()->withInput();
        }

        $kabaak = Pegawai::where('jabatan', 'LIKE', '%BIRO ADMINISTRASI AKADEMIK%')->first();
        $kabauk = Pegawai::where('jabatan', 'LIKE', '%BIRO ADMINISTRASI UMUM%')->first();

        if (!$kabaak) {
            Alert::error('Gagal', 'Data Kepala Biro Administrasi Akademik tidak ditemukan!')->autoclose(3000)->toToast();
            return redirect()->back();
        }

        if (!$kabauk) {
            Alert::error('Gagal', 'Data Kepala Biro Administrasi Umum tidak ditemukan!')->autoclose(3000)->toToast();
            return redirect()->back();
        }

        $lastSurat = SuratAkademik::whereNotNull('no_surat')->orderByRaw('CAST(no_surat AS UNSIGNED) DESC')->first();
        $nextNo = $lastSurat ? ((int) $lastSurat->no_surat + 1) : 1;
        $noSuratFormatted = sprintf("%03d", $nextNo);

        $data = [
            'no_surat' => $noSuratFormatted,
            'users_id' => $userId,
            'program_studi_id' => $mahasiswa->programStudi->id,
            'npm' => $mahasiswa->npm,
            'status_cuti' => 'Belum Pernah Cuti',
            'alamat' => $mahasiswa->alamat,
            'no_wa' => $mahasiswa->no_wa,
            'semester' => $request->semester,
            'permohonan' => $request->permohonan,
            'alasan_cuti' => $request->alasan_cuti,
            'dosen_pembimbing_akademik' => $request->dosen_pembimbing_akademik,
            'kaprodi' => $request->kaprodi,
            'kabaak' => $kabaak->id,
            'kabauk' => $kabauk->id,
        ];

        SuratAkademik::create($data);
        Alert::success('success', 'Data berhasil dibuat')
            ->autoclose(3000)
            ->toToast()
            ->timerProgressBar()
            ->iconHtml('<i class="fa-solid fa-circle-check"></i>');

        return redirect()->route('suratAkademik.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(SuratAkademik $suratAkademik)
    {

        $mahasiswa = Mahasiswa::where('users_id', $suratAkademik->users_id)->first();

        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Data mahasiswa tidak ditemukan!');
        }

        $dosen = Dosen::find($suratAkademik->dosen_pembimbing_akademik);
        $kaprodi = Dosen::find($suratAkademik->kaprodi);
        $kabaak = Pegawai::where('jabatan', 'LIKE', '%BIRO ADMINISTRASI AKADEMIK%')->first();
        $kabauk = Pegawai::where('jabatan', 'LIKE', '%BIRO ADMINISTRASI UMUM%')->first();
        $programStudi = ProgramStudi::find($mahasiswa->program_studi_id);
        $fakultas = $mahasiswa->fakultas;
        $user = User::find($suratAkademik->users_id);
        $no_surat = SuratAkademik::count();
        return view('pages.suratAkademik.show', compact('suratAkademik', 'mahasiswa', 'programStudi', 'user', 'no_surat', 'fakultas', 'dosen', 'kaprodi', 'kabaak', 'kabauk')); //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SuratAkademik $suratAkademik)
    {
        $title = 'Form Edit Surat Akademik';
        $tahunAkademik = \App\Models\TahunAkademik::all();
        $dosens = Dosen::all();
        return view('pages.suratAkademik.edit', compact('suratAkademik', 'title', 'tahunAkademik', 'dosens'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SuratAkademik $suratAkademik)
    {
        $suratAkademik->update($request->all());
        Alert::success('success', 'data updated successfully')->autoclose(3000)->toToast();
        return redirect()->route('suratAkademik.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SuratAkademik $suratAkademik)
    {
        $suratAkademik->delete();
        Alert::success('success', 'data deleted successfully')->autoclose(3000)->toToast();
        return redirect()->route('suratAkademik.index');
    }

    /**
     * Remove multiple resources from storage.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $ids = array_filter((array) $ids);

        if (empty($ids)) {
            Alert::warning('Peringatan', 'Tidak ada data yang dipilih.')->autoclose(3000)->toToast();
            return redirect()->back();
        }

        $count = SuratAkademik::whereIn('id', $ids)->delete();

        Alert::success('Berhasil!', "{$count} data surat akademik berhasil dihapus")
            ->autoclose(3000)
            ->toToast()
            ->timerProgressBar()
            ->iconHtml('<i class="fa-solid fa-check"></i>');

        return redirect()->route('suratAkademik.index');
    }

    /**
     * Update status of multiple resources in storage.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,diterima,ditolak',
        ]);

        $ids = $request->input('ids');
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $ids = array_filter((array) $ids);

        if (empty($ids)) {
            Alert::warning('Peringatan', 'Tidak ada data yang dipilih.')->autoclose(3000)->toToast();
            return redirect()->back();
        }

        $count = SuratAkademik::whereIn('id', $ids)->update(['status' => $request->status]);

        $statusText = ucfirst($request->status);
        Alert::success('Berhasil!', "Status {$count} data surat akademik berhasil diubah menjadi {$statusText}")
            ->autoclose(3000)
            ->toToast()
            ->timerProgressBar()
            ->iconHtml('<i class="fa-solid fa-check"></i>');

        return redirect()->route('suratAkademik.index');
    }

    /**
     * Show the form for editing status only.
     */
    public function editStatus(SuratAkademik $suratAkademik)
    {
        $title = 'Form Update Status Surat Akademik';
        $tahunAkademik = \App\Models\TahunAkademik::all();
        return view('pages.suratAkademik.updateStatus', compact('suratAkademik', 'title', 'tahunAkademik'));
    }

    /**
     * Update status of the specified resource in storage.
     */
    public function updateStatus(Request $request, SuratAkademik $suratAkademik)
    {
        // Validasi input
        $validated = $request->validate([
            'tahun_akademik' => 'nullable|string',
            'status_semester' => 'nullable|in:Gasal,Genap',
            'status' => 'required|in:pending,diterima,ditolak',
        ]);

        // Update hanya field yang diperlukan
        $suratAkademik->update($validated);

        Alert::success('Success', 'Status surat akademik berhasil diupdate!')
            ->autoclose(3000)
            ->toToast();

        return redirect()->route('suratAkademik.index');
    }
    
    public function validasi(SuratAkademik $suratAkademik)
    {
        $kabaak = Pegawai::where('jabatan', 'LIKE', '%BIRO ADMINISTRASI AKADEMIK%')->first();
        $kabauk = Pegawai::where('jabatan', 'LIKE', '%BIRO ADMINISTRASI UMUM%')->first();

        return view('pages.suratAkademik.validasi', compact('suratAkademik', 'kabaak', 'kabauk'));
    }

    public function getBulanRomawi()
    {
        $petaRomawi = [
            'January' => 'I', 'February' => 'II', 'March' => 'III', 'April' => 'IV',
            'May' => 'V', 'June' => 'VI', 'July' => 'VII', 'August' => 'VIII',
            'September' => 'IX', 'October' => 'X', 'November' => 'XI', 'December' => 'XII',
        ];
        return $petaRomawi[date('F')] ?? 'I';
    }

    /**
     * Upload berkas arsip surat layanan akademik ke Google Drive dan sinkronkan ke Arsip.
     */
    public function uploadArsip(Request $request, SuratAkademik $suratAkademik)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:15360',
        ]);

        try {
            $uploaded = $request->file('file');
            $ext = $uploaded->getClientOriginalExtension();

            $noUrut = sprintf('%03d', (int) ($suratAkademik->no_surat ?? $suratAkademik->id));
            $bulanRomawi = $this->getBulanRomawi();
            $tahun = $suratAkademik->created_at ? $suratAkademik->created_at->format('Y') : date('Y');
            $nomorSurat = "{$noUrut}/UIS.B1/LA/{$bulanRomawi}/{$tahun}";
            $nomorSuratClean = "{$noUrut}-UIS.B1-LA-{$bulanRomawi}-{$tahun}";
            $permohonanSlug = Str::slug(($suratAkademik->permohonan ?? 'Layanan') . '_' . ($suratAkademik->user->name ?? 'Mahasiswa'), '_');

            // Format nama berkas sesuai nomor urut dan nomor surat
            $filename = "{$noUrut}_{$nomorSuratClean}_{$permohonanSlug}.{$ext}";

            $service = $this->driveService();
            $folderId = env('GOOGLE_DRIVE_SURAT_LAYANAN_AKADEMIK_FOLDER_ID', '1Qo-lkub8YdsMpfyNHkt-sU-Xiw1n2Ral');

            if (!$folderId) {
                throw new \Exception('GOOGLE_DRIVE_SURAT_LAYANAN_AKADEMIK_FOLDER_ID tidak ditemukan di .env');
            }

            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $filename,
                'parents' => [$folderId],
            ]);

            $content = File::get($uploaded->getRealPath());

            $file = $service->files->create($fileMetadata, [
                'data' => $content,
                'uploadType' => 'multipart',
                'fields' => 'id',
            ]);

            $fileId = $file->getId();
            $this->makeDriveFilePublic($fileId);
            $driveUrl = "https://drive.google.com/file/d/{$fileId}/view";

            // Hapus file lama di Drive jika ada
            if ($suratAkademik->file) {
                $this->deleteDriveFile($suratAkademik->file);
            }

            $suratAkademik->update(['file' => $driveUrl]);

            // Sinkronkan ke Arsip Utama
            $kategori = KategoriArsip::firstOrCreate(['kategori_arsip' => 'Surat Layanan Akademik']);
            ArsipUtama::updateOrCreate(
                [
                    'kategori_arsip_id' => $kategori->id,
                    'nama_arsip' => "Surat Layanan Akademik - No. {$nomorSurat} - " . ($suratAkademik->user->name ?? '-') . " ({$suratAkademik->permohonan})",
                ],
                [
                    'user_id' => Auth::id() ?? ($suratAkademik->users_id ?? 1),
                    'tahun_arsip' => $tahun,
                    'file_arsip' => $driveUrl,
                    'is_active' => 1,
                ]
            );

            Alert::success('Berhasil', "Berkas arsip {$filename} berhasil diunggah ke Google Drive.")
                ->autoclose(4000)->toToast()->timerProgressBar();

        } catch (\Throwable $e) {
            Log::error('Upload Arsip Surat Akademik Error: ' . $e->getMessage());
            Alert::error('Gagal', 'Gagal mengunggah berkas arsip: ' . $e->getMessage())
                ->autoclose(5000)->toToast()->timerProgressBar();
        }

        return redirect()->back();
    }

    /**
     * Otomatis generate PDF Formulir Layanan Akademik dan simpan ke folder Google Drive Arsip.
     */
    public function generateArsip(SuratAkademik $suratAkademik)
    {
        try {
            $noUrut = sprintf('%03d', (int) ($suratAkademik->no_surat ?? $suratAkademik->id));
            $bulanRomawi = $this->getBulanRomawi();
            $tahun = $suratAkademik->created_at ? $suratAkademik->created_at->format('Y') : date('Y');

            $nomorSurat = "{$noUrut}/UIS.B1/LA/{$bulanRomawi}/{$tahun}";
            $nomorSuratClean = "{$noUrut}-UIS.B1-LA-{$bulanRomawi}-{$tahun}";
            $permohonanSlug = Str::slug(($suratAkademik->permohonan ?? 'Layanan') . '_' . ($suratAkademik->user->name ?? 'Mahasiswa'), '_');

            // Format nama berkas sesuai nomor urut dan nomor surat
            $filename = "{$noUrut}_{$nomorSuratClean}_{$permohonanSlug}.pdf";

            $dosen = Dosen::find($suratAkademik->dosen_pembimbing_akademik);
            $kaprodi = Dosen::find($suratAkademik->kaprodi);
            $kabaak = Pegawai::where('jabatan', 'LIKE', '%BIRO ADMINISTRASI AKADEMIK%')->first();
            $kabauk = Pegawai::where('jabatan', 'LIKE', '%BIRO ADMINISTRASI UMUM%')->first();
            $fakultas = $suratAkademik->mahasiswa->fakultas ?? 'Fakultas Sains dan Teknologi';

            $pdf = Pdf::loadView('pages.suratAkademik.pdf', compact('suratAkademik', 'dosen', 'kaprodi', 'kabaak', 'kabauk', 'fakultas', 'bulanRomawi'));
            $pdfContent = $pdf->output();

            $service = $this->driveService();
            $folderId = env('GOOGLE_DRIVE_SURAT_LAYANAN_AKADEMIK_FOLDER_ID', '1Qo-lkub8YdsMpfyNHkt-sU-Xiw1n2Ral');

            if (!$folderId) {
                throw new \Exception('GOOGLE_DRIVE_SURAT_LAYANAN_AKADEMIK_FOLDER_ID tidak ditemukan di .env');
            }

            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $filename,
                'parents' => [$folderId],
            ]);

            $file = $service->files->create($fileMetadata, [
                'data' => $pdfContent,
                'mimeType' => 'application/pdf',
                'uploadType' => 'multipart',
                'fields' => 'id',
            ]);

            $fileId = $file->getId();
            $this->makeDriveFilePublic($fileId);
            $driveUrl = "https://drive.google.com/file/d/{$fileId}/view";

            // Hapus file lama di Drive jika ada
            if ($suratAkademik->file) {
                $this->deleteDriveFile($suratAkademik->file);
            }

            $suratAkademik->update(['file' => $driveUrl]);

            // Sinkronkan ke Arsip Utama
            $kategori = KategoriArsip::firstOrCreate(['kategori_arsip' => 'Surat Layanan Akademik']);
            ArsipUtama::updateOrCreate(
                [
                    'kategori_arsip_id' => $kategori->id,
                    'nama_arsip' => "Surat Layanan Akademik - No. {$nomorSurat} - " . ($suratAkademik->user->name ?? '-') . " ({$suratAkademik->permohonan})",
                ],
                [
                    'user_id' => Auth::id() ?? ($suratAkademik->users_id ?? 1),
                    'tahun_arsip' => $tahun,
                    'file_arsip' => $driveUrl,
                    'is_active' => 1,
                ]
            );

            Alert::success('Berhasil', "PDF Surat Akademik {$filename} berhasil dibuat dan diarsipkan di Google Drive.")
                ->autoclose(4000)->toToast()->timerProgressBar();

        } catch (\Throwable $e) {
            Log::error('Generate Arsip Surat Akademik Error: ' . $e->getMessage());
            Alert::error('Gagal', 'Gagal membuat arsip PDF: ' . $e->getMessage())
                ->autoclose(5000)->toToast()->timerProgressBar();
        }

        return redirect()->back();
    }
}
