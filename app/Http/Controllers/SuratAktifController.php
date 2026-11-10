<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\SuratAktif;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\Auth;
use App\DataTables\SuratAktifDataTable;
use RealRashid\SweetAlert\Facades\Alert;

use App\Imports\SuratAktifImport;
use App\Exports\SuratAktifTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\HandleGoogleDrive;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ArsipUtama;
use App\Models\KategoriArsip;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SuratAktifController extends Controller
{
    use HandleGoogleDrive;
    public function exportTemplate()
    {
        return Excel::download(new SuratAktifTemplateExport, 'template_surat_aktif.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new SuratAktifImport, $request->file('file'));
            Alert::success('Berhasil', 'Data Surat Aktif berhasil diimport')->autoclose(3000)->toToast();
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Terjadi kesalahan saat import: ' . $e->getMessage())->autoclose(5000)->toToast();
        }

        return redirect()->route('suratAktif.index');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(SuratAktifDataTable $dataTable)
    {
        $title = 'Surat Aktif';
        return $dataTable->render('pages.suratAktif.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Form Surat Aktif';

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
        return view('pages.suratAktif.create', compact('users', 'programStudi', 'title'));
    }

    public function pengajuan()
    {
        $mahasiswa = Mahasiswa::where('users_id', Auth::id())->first();

        if (!$mahasiswa) {
            Alert::error('Error', 'Data mahasiswa tidak ditemukan. Silahkan isi data mahasiswa terlebih dahulu.')->autoclose(10000)->toToast()->timerProgressBar();
            return redirect()->route('mahasiswa.create');
        }

        // Cek apakah ada pengajuan surat aktif yang masih berstatus pending
        $existingPending = SuratAktif::where('users_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            Alert::warning('Peringatan', 'Anda masih memiliki pengajuan Surat Aktif yang berstatus Pending. Mohon tunggu hingga proses verifikasi selesai.')
                ->autoclose(5000)->toToast()->timerProgressBar();
            return redirect()->route('suratAktif.index');
        }

        // Cek apakah sudah pernah diterbitkan di semester & tahun akademik yang sama
        if ($mahasiswa->semester && $mahasiswa->tahun_akademik) {
            $existingApproved = SuratAktif::where('users_id', Auth::id())
                ->where('status', 'diterima')
                ->where('semester', $mahasiswa->semester)
                ->where('tahun_akademik', $mahasiswa->tahun_akademik)
                ->first();

            if ($existingApproved) {
                Alert::warning('Peringatan', 'Anda sudah memiliki Surat Aktif yang telah disetujui untuk semester dan tahun akademik ini.')
                    ->autoclose(5000)->toToast()->timerProgressBar();
                return redirect()->route('suratAktif.index');
            }
        }

        $lastSurat = SuratAktif::latest('no_surat')->first();
        $noSurat = $lastSurat ? $lastSurat->no_surat + 1 : 1;
        $noSuratFormatted = sprintf("%03d", $noSurat);

        $data = [
            'no_surat' => $noSuratFormatted,
            'users_id' => Auth::id(),
            'program_studi_id' => $mahasiswa->program_studi_id,
            'tempat_lahir' => $mahasiswa->tempat_lahir,
            'tgl_lahir' => $mahasiswa->tgl_lahir,
            'npm' => $mahasiswa->npm,
            'jenjang_pendidikan' => $mahasiswa->jenjang_pendidikan,
            'fakultas' => $mahasiswa->fakultas,
            'status' => 'pending',
            'semester' => $mahasiswa->semester ?? null,
            'status_semester' => $mahasiswa->status_semester ?? null,
            'tahun_akademik' => $mahasiswa->tahun_akademik ?? null,
        ];

        SuratAktif::create($data);

        // Menampilkan pesan sukses
        Alert::success('Success', 'Surat Aktif berhasil dibuat secara otomatis')
            ->autoclose(3000)
            ->toToast()
            ->timerProgressBar()
            ->iconHtml('<i class="fa-solid fa-check"></i>');
        return redirect()->route('suratAktif.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $userId = Auth::user()->is_mahasiswa ? Auth::id() : $request->users_id;

        // Validasi tidak boleh double data jika masih ada pending
        $existingPending = SuratAktif::where('users_id', $userId)
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            Alert::warning('Peringatan', 'Pengguna ini masih memiliki pengajuan Surat Aktif yang berstatus Pending.')
                ->autoclose(5000)->toToast()->timerProgressBar();
            return redirect()->back()->withInput();
        }

        $lastSurat = SuratAktif::latest('no_surat')->first();
        $noSurat = $lastSurat ? (int) $lastSurat->no_surat + 1 : 1;
        $noSuratFormatted = sprintf("%03d", $noSurat);

        $data = [
            'no_surat' => $noSuratFormatted,
            'users_id' => $userId,
            'program_studi_id' => $request->program_studi_id,
            'tempat_lahir' => $request->tempat_lahir,
            'tgl_lahir' => $request->tgl_lahir,
            'npm' => $request->npm,
            'jenjang_pendidikan' => $request->jenjang_pendidikan,
            'fakultas' => $request->fakultas,
            'status' => 'pending',
            'semester' => $request->semester ?? null,
            'status_semester' => $request->status_semester ?? null,
            'tahun_akademik' => $request->tahun_akademik ?? null,
        ];

        SuratAktif::create($data);
        Alert::success('Success', 'Data created successfully')->autoclose(3000)->toToast();
        return redirect()->route('suratAktif.index');
    }

    /**
     * Display the specified resource.
     */
    public function getBulanRomawi()
    {
        $bulan = Carbon::now()->format('F Y');

        $petaRomawi = [
            'January' => 'I',
            'February' => 'II',
            'March' => 'III',
            'April' => 'IV',
            'May' => 'V',
            'June' => 'VI',
            'July' => 'VII',
            'August' => 'VIII',
            'September' => 'IX',
            'October' => 'X',
            'November' => 'XI',
            'December' => 'XII',
        ];

        return $petaRomawi[date('F', strtotime($bulan))];
    }

    /**
     * Display the specified resource.
     */
    public function show(SuratAktif $suratAktif)
    {
        $no_surat = $suratAktif->no_surat;
        $program_studi = ProgramStudi::find($suratAktif->program_studi_id)->program_studi;
        $pegawai = \App\Models\Pegawai::where('jabatan', 'LIKE', '%BIRO ADMINISTRASI AKADEMIK%')->first();
        $user = $pegawai ? $pegawai->user : null;
        $bulanRomawi = $this->getBulanRomawi();
        return view('pages.suratAktif.show', compact('suratAktif', 'no_surat', 'program_studi', 'bulanRomawi', 'user', 'pegawai'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SuratAktif $suratAktif)
    {
        $title = 'Form Surat Aktif';
        $tahunAkademik = TahunAkademik::all();
        return view('pages.suratAktif.edit', compact('suratAktif', 'tahunAkademik', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SuratAktif $suratAktif)
    {
        $request->validate([
            'tahun_akademik' => 'required',
            'status_semester' => 'required',
            'status' => 'required',
        ]);

        $suratAktif->update([
            'tahun_akademik' => $request->tahun_akademik,
            'status_semester' => $request->status_semester,
            'status' => $request->status,
        ]);

        Alert::success('Berhasil!', 'Data Surat Aktif berhasil diupdate')
            ->autoclose(3000)
            ->toToast()
            ->timerProgressBar()
            ->iconHtml('<i class="fa-solid fa-check"></i>');

        return redirect()->route('suratAktif.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SuratAktif $suratAktif)
    {
        $suratAktif->delete();
        Alert::success('Success', 'Data deleted successfully')->autoclose(3000)->toToast();
        return redirect()->route('suratAktif.index');
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

        $count = SuratAktif::whereIn('id', $ids)->delete();

        Alert::success('Berhasil!', "{$count} data surat aktif berhasil dihapus")
            ->autoclose(3000)
            ->toToast()
            ->timerProgressBar()
            ->iconHtml('<i class="fa-solid fa-check"></i>');

        return redirect()->route('suratAktif.index');
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

        $count = SuratAktif::whereIn('id', $ids)->update(['status' => $request->status]);

        $statusText = ucfirst($request->status);
        Alert::success('Berhasil!', "Status {$count} data surat aktif berhasil diubah menjadi {$statusText}")
            ->autoclose(3000)
            ->toToast()
            ->timerProgressBar()
            ->iconHtml('<i class="fa-solid fa-check"></i>');

        return redirect()->route('suratAktif.index');
    }

    public function validasi(SuratAktif $suratAktif)
    {
        $pegawai = \App\Models\Pegawai::where('jabatan', 'LIKE', '%BIRO ADMINISTRASI AKADEMIK%')->first();
        $userApproval = $pegawai ? $pegawai->user : null;

        return view('pages.suratAktif.detailsuratakademik', compact('suratAktif', 'pegawai', 'userApproval'));
    }

    public function preview(SuratAktif $suratAktif)
    {
        $no_surat = $suratAktif->no_surat;
        $program_studi = ProgramStudi::find($suratAktif->program_studi_id)->program_studi;
        $pegawai = \App\Models\Pegawai::where('jabatan', 'LIKE', '%BIRO ADMINISTRASI AKADEMIK%')->first();
        $user = $pegawai ? $pegawai->user : null;
        $bulanRomawi = $this->getBulanRomawi();
        $is_preview = true;
        return view('pages.suratAktif.show', compact('suratAktif', 'no_surat', 'program_studi', 'bulanRomawi', 'user', 'pegawai', 'is_preview'));
    }
    public function print(SuratAktif $suratAktif)
    {
        $no_surat = $suratAktif->no_surat;
        $program_studi = ProgramStudi::find($suratAktif->program_studi_id)->program_studi;
        $pegawai = \App\Models\Pegawai::where('jabatan', 'LIKE', '%BIRO ADMINISTRASI AKADEMIK%')->first();
        $user = $pegawai ? $pegawai->user : null;
        $bulanRomawi = $this->getBulanRomawi();
        return view('pages.suratAktif.show', compact('suratAktif', 'no_surat', 'program_studi', 'bulanRomawi', 'user', 'pegawai'));
    }

    /**
     * Upload berkas arsip surat aktif ke Google Drive dan sinkronkan ke Arsip.
     */
    public function uploadArsip(Request $request, SuratAktif $suratAktif)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:15360',
        ]);

        try {
            $uploaded = $request->file('file');
            $ext = $uploaded->getClientOriginalExtension();

            $noUrut = sprintf('%03d', (int) ($suratAktif->no_surat ?? $suratAktif->id));
            $bulanRomawi = $this->getBulanRomawi();
            $tahun = $suratAktif->created_at ? $suratAktif->created_at->format('Y') : date('Y');
            $nomorSurat = "{$noUrut}/UIS.B1/LL/{$bulanRomawi}/{$tahun}";
            $nomorSuratClean = "{$noUrut}-UIS.B1-LL-{$bulanRomawi}-{$tahun}";
            $mahasiswaName = Str::slug($suratAktif->users->name ?? 'mahasiswa', '_');

            // Format nama berkas sesuai nomor urut dan nomor surat
            $filename = "{$noUrut}_{$nomorSuratClean}_{$mahasiswaName}.{$ext}";

            $service = $this->driveService();
            $folderId = env('GOOGLE_DRIVE_SURAT_AKTIF_MAHASISWA_FOLDER_ID', '1OjALgLazwTvedOG9WthVjbr7Wozl3AX3');

            if (!$folderId) {
                throw new \Exception('GOOGLE_DRIVE_SURAT_AKTIF_MAHASISWA_FOLDER_ID tidak ditemukan di .env');
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
            if ($suratAktif->file) {
                $this->deleteDriveFile($suratAktif->file);
            }

            $suratAktif->update(['file' => $driveUrl]);

            // Sinkronkan ke Arsip Utama
            $kategori = KategoriArsip::firstOrCreate(['kategori_arsip' => 'Surat Keterangan Aktif']);
            ArsipUtama::updateOrCreate(
                [
                    'kategori_arsip_id' => $kategori->id,
                    'nama_arsip' => "Surat Keterangan Aktif - No. {$nomorSurat} - " . ($suratAktif->users->name ?? '-'),
                ],
                [
                    'user_id' => Auth::id() ?? ($suratAktif->users_id ?? 1),
                    'tahun_arsip' => $tahun,
                    'file_arsip' => $driveUrl,
                    'is_active' => 1,
                ]
            );

            Alert::success('Berhasil', "Berkas arsip {$filename} berhasil diunggah ke Google Drive.")
                ->autoclose(4000)->toToast()->timerProgressBar();

        } catch (\Throwable $e) {
            Log::error('Upload Arsip Surat Aktif Error: ' . $e->getMessage());
            Alert::error('Gagal', 'Gagal mengunggah berkas arsip: ' . $e->getMessage())
                ->autoclose(5000)->toToast()->timerProgressBar();
        }

        return redirect()->back();
    }

    /**
     * Otomatis generate PDF Surat Aktif dan simpan ke folder Google Drive Arsip.
     */
    public function generateArsip(SuratAktif $suratAktif)
    {
        try {
            $no_surat = $suratAktif->no_surat;
            $noUrut = sprintf('%03d', (int) ($no_surat ?? $suratAktif->id));
            $program_studi = ProgramStudi::find($suratAktif->program_studi_id)->program_studi ?? '-';
            $pegawai = \App\Models\Pegawai::where('jabatan', 'LIKE', '%BIRO ADMINISTRASI AKADEMIK%')->first();
            $user = $pegawai ? $pegawai->user : null;
            $bulanRomawi = $this->getBulanRomawi();
            $tahun = $suratAktif->created_at ? $suratAktif->created_at->format('Y') : date('Y');

            $nomorSurat = "{$noUrut}/UIS.B1/LL/{$bulanRomawi}/{$tahun}";
            $nomorSuratClean = "{$noUrut}-UIS.B1-LL-{$bulanRomawi}-{$tahun}";
            $mahasiswaName = Str::slug($suratAktif->users->name ?? 'mahasiswa', '_');

            // Format nama berkas sesuai nomor urut dan nomor surat
            $filename = "{$noUrut}_{$nomorSuratClean}_{$mahasiswaName}.pdf";

            $pdf = Pdf::loadView('pages.suratAktif.pdf', compact('suratAktif', 'no_surat', 'program_studi', 'bulanRomawi', 'user', 'pegawai'));
            $pdfContent = $pdf->output();

            $service = $this->driveService();
            $folderId = env('GOOGLE_DRIVE_SURAT_AKTIF_MAHASISWA_FOLDER_ID', '1OjALgLazwTvedOG9WthVjbr7Wozl3AX3');

            if (!$folderId) {
                throw new \Exception('GOOGLE_DRIVE_SURAT_AKTIF_MAHASISWA_FOLDER_ID tidak ditemukan di .env');
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
            if ($suratAktif->file) {
                $this->deleteDriveFile($suratAktif->file);
            }

            $suratAktif->update(['file' => $driveUrl]);

            // Sinkronkan ke Arsip Utama
            $kategori = KategoriArsip::firstOrCreate(['kategori_arsip' => 'Surat Keterangan Aktif']);
            ArsipUtama::updateOrCreate(
                [
                    'kategori_arsip_id' => $kategori->id,
                    'nama_arsip' => "Surat Keterangan Aktif - No. {$nomorSurat} - " . ($suratAktif->users->name ?? '-'),
                ],
                [
                    'user_id' => Auth::id() ?? ($suratAktif->users_id ?? 1),
                    'tahun_arsip' => $tahun,
                    'file_arsip' => $driveUrl,
                    'is_active' => 1,
                ]
            );

            Alert::success('Berhasil', "PDF Surat Aktif {$filename} berhasil dibuat dan diarsipkan di Google Drive.")
                ->autoclose(4000)->toToast()->timerProgressBar();

        } catch (\Throwable $e) {
            Log::error('Generate Arsip Surat Aktif Error: ' . $e->getMessage());
            Alert::error('Gagal', 'Gagal membuat arsip PDF: ' . $e->getMessage())
                ->autoclose(5000)->toToast()->timerProgressBar();
        }

        return redirect()->back();
    }
}
