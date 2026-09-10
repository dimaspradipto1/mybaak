<?php

namespace App\Http\Controllers;

use App\DataTables\ArsipUtamaDataTable;
use App\Exports\ArsipUtamaTemplateExport;
use App\Http\Requests\ArsipUtamaRequest;
use App\Imports\ArsipUtamaImport;
use App\Models\ArsipUtama;
use App\Models\KategoriArsip;
use App\Models\UnitKerja;
use App\Traits\HandleGoogleDrive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use RealRashid\SweetAlert\Facades\Alert;

class ArsipUtamaController extends Controller
{
    use HandleGoogleDrive;

    public function index(ArsipUtamaDataTable $dataTable)
    {
        $user = Auth::user();
        $title = 'Arsip Utama';
        $kategoriArsips = KategoriArsip::orderBy('kategori_arsip', 'asc')->get();

        // Ambil data unit kerja berdasarkan hak akses
        $unitKerjaQuery = UnitKerja::with('children');

        $isHigherLevel = $user->is_superadmin || $user->is_admin || ($user->role && strtoupper($user->role->nama_role) == 'REKTOR');

        if (!$isHigherLevel && $user->role?->unit_kerja_id) {
            $myUnit = $user->role->unitKerja;
            // Hanya tampilkan unit dia dan bawahannya
            $unitKerjas = collect([$myUnit->load('children')]);
        } else {
            // Admin atau User Tanpa Unit (Super User) melihat semua akar organisasi
            $unitKerjas = UnitKerja::whereNull('parent_id')->with('children')->get();

            // Fallback: Jika tidak ada unit root, ambil semua
            if ($unitKerjas->isEmpty()) {
                $unitKerjas = UnitKerja::with('children')->get();
            }
        }

        return $dataTable->render('pages.arsiputama.index', compact('title', 'kategoriArsips', 'unitKerjas'));
    }

    public function create()
    {
        $title = 'Form Arsip Utama';
        $kategoriArsips = KategoriArsip::orderBy('kategori_arsip', 'asc')->get();
        return view('pages.arsiputama.create', compact('title', 'kategoriArsips'));
    }

    public function store(ArsipUtamaRequest $request)
    {
        try {
            $data = $request->validated();

            // Handle custom category
            if ($request->filled('custom_kategori')) {
                $newKategori = KategoriArsip::firstOrCreate([
                    'kategori_arsip' => $request->custom_kategori
                ]);
                $data['kategori_arsip_id'] = $newKategori->id;
            } elseif (!is_numeric($data['kategori_arsip_id'])) {
                // Fallback for select2 tags
                $newKategori = KategoriArsip::firstOrCreate([
                    'kategori_arsip' => $data['kategori_arsip_id']
                ]);
                $data['kategori_arsip_id'] = $newKategori->id;
            }

            $data['user_id'] = Auth::id();

            // Cek apakah arsip dengan nama, kategori, dan tahun yang sama sudah ada
            $existingArsip = ArsipUtama::where('nama_arsip', $data['nama_arsip'])
                ->where('kategori_arsip_id', $data['kategori_arsip_id'])
                ->where('tahun_arsip', $data['tahun_arsip'])
                ->first();

            if ($existingArsip) {
                Alert::warning('Peringatan', 'Data arsip dengan nama, kategori, dan tahun tersebut sudah pernah ditambahkan.')
                    ->autoclose(5000)->toToast()->timerProgressBar();
                return redirect()->back()->withInput();
            }

            if ($request->hasFile('file_arsip')) {
                $uploaded = $request->file('file_arsip');
                $safeOriginal = preg_replace('/[^A-Za-z0-9\.\-_ ]/', '', $uploaded->getClientOriginalName());

                $service     = $this->driveService();
                $folderId    = env('GOOGLE_DRIVE_ARSIP_UTAMA_FOLDER_ID');

                if (!$folderId) {
                    throw new \Exception('GOOGLE_DRIVE_ARSIP_UTAMA_FOLDER_ID tidak ditemukan di .env');
                }

                $fileMetadata = new \Google\Service\Drive\DriveFile([
                    'name'    => $safeOriginal,
                    'parents' => [$folderId],
                ]);

                $content = File::get($uploaded->getRealPath());
                $file    = $service->files->create($fileMetadata, [
                    'data'       => $content,
                    'uploadType' => 'multipart',
                    'fields'     => 'id',
                ]);

                $fileId = $file->getId();
                $this->makeDriveFilePublic($fileId);
                $data['file_arsip'] = "https://drive.google.com/file/d/{$fileId}/view";
            }

            ArsipUtama::create($data);

            Alert::success('Berhasil', 'Arsip Utama berhasil ditambahkan')
                ->autoclose(4000)->toToast()->timerProgressBar()
                ->iconHtml('<i class="fa fa-check-circle"></i>');

            return redirect()->route('arsiputama.index');
        } catch (\Throwable $th) {
            Log::error('Error storing ArsipUtama: ' . $th->getMessage());
            Alert::error('Gagal', 'Gagal menambahkan data: ' . $th->getMessage())
                ->autoclose(5000)->toToast()->timerProgressBar()
                ->iconHtml('<i class="fa fa-times-circle"></i>');
            return redirect()->back()->withInput();
        }
    }

    public function show(ArsipUtama $arsiputama)
    {
        //
    }

    public function edit(ArsipUtama $arsiputama)
    {
        $title = 'Edit Arsip Utama';
        $kategoriArsips = KategoriArsip::orderBy('kategori_arsip', 'asc')->get();
        return view('pages.arsiputama.edit', compact('title', 'arsiputama', 'kategoriArsips'));
    }

    public function update(ArsipUtamaRequest $request, ArsipUtama $arsiputama)
    {
        try {
            $data = $request->validated();

            // Handle custom category
            if ($request->filled('custom_kategori')) {
                $newKategori = KategoriArsip::firstOrCreate([
                    'kategori_arsip' => $request->custom_kategori
                ]);
                $data['kategori_arsip_id'] = $newKategori->id;
            } elseif (!is_numeric($data['kategori_arsip_id'])) {
                // Fallback for select2 tags
                $newKategori = KategoriArsip::firstOrCreate([
                    'kategori_arsip' => $data['kategori_arsip_id']
                ]);
                $data['kategori_arsip_id'] = $newKategori->id;
            }

            $data['user_id'] = Auth::id();

            // Cek apakah arsip lain dengan nama, kategori, dan tahun yang sama sudah ada
            $existingArsip = ArsipUtama::where('nama_arsip', $data['nama_arsip'])
                ->where('kategori_arsip_id', $data['kategori_arsip_id'])
                ->where('tahun_arsip', $data['tahun_arsip'])
                ->where('id', '!=', $arsiputama->id)
                ->first();

            if ($existingArsip) {
                Alert::warning('Peringatan', 'Data arsip dengan nama, kategori, dan tahun tersebut sudah ada.')
                    ->autoclose(5000)->toToast()->timerProgressBar();
                return redirect()->back()->withInput();
            }

            if ($request->hasFile('file_arsip')) {
                // Hapus file lama di Google Drive
                if ($arsiputama->file_arsip && str_contains($arsiputama->file_arsip, 'drive.google.com')) {
                    $this->deleteDriveFile($arsiputama->file_arsip);
                }

                $uploaded = $request->file('file_arsip');
                $safeOriginal = preg_replace('/[^A-Za-z0-9\.\-_ ]/', '', $uploaded->getClientOriginalName());

                $service  = $this->driveService();
                $folderId = env('GOOGLE_DRIVE_ARSIP_UTAMA_FOLDER_ID');

                if (!$folderId) {
                    throw new \Exception('GOOGLE_DRIVE_ARSIP_UTAMA_FOLDER_ID tidak ditemukan di .env');
                }

                $fileMetadata = new \Google\Service\Drive\DriveFile([
                    'name'    => $safeOriginal,
                    'parents' => [$folderId],
                ]);

                $content = File::get($uploaded->getRealPath());
                $file    = $service->files->create($fileMetadata, [
                    'data'       => $content,
                    'uploadType' => 'multipart',
                    'fields'     => 'id',
                ]);

                $fileId = $file->getId();
                $this->makeDriveFilePublic($fileId);
                $data['file_arsip'] = "https://drive.google.com/file/d/{$fileId}/view";
            } else {
                unset($data['file_arsip']);
            }

            $arsiputama->update($data);

            Alert::success('Berhasil', 'Data Berhasil Diubah')
                ->autoclose(3000)->toToast()->timerProgressBar()
                ->iconHtml('<i class="fa fa-check-circle"></i>');

            return redirect()->route('arsiputama.index');
        } catch (\Throwable $th) {
            Log::error('Error updating ArsipUtama: ' . $th->getMessage());
            Alert::error('Gagal', 'Data Gagal Diubah: ' . $th->getMessage())
                ->autoclose(3000)->toToast()->timerProgressBar()
                ->iconHtml('<i class="fa fa-times-circle"></i>');
            return redirect()->back()->withInput();
        }
    }

    public function destroy(ArsipUtama $arsiputama)
    {
        if ($arsiputama->file_arsip && str_contains($arsiputama->file_arsip, 'drive.google.com')) {
            $this->deleteDriveFile($arsiputama->file_arsip);
        }

        $arsiputama->delete();

        Alert::success('Berhasil', 'Data Berhasil Dihapus')
            ->autoclose(3000)->toToast()->timerProgressBar()
            ->iconHtml('<i class="fa fa-check-circle"></i>');

        return redirect()->route('arsiputama.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new ArsipUtamaImport, $request->file('file'));

            Alert::success('Sukses', 'Data Arsip Utama berhasil diimpor')
                ->autoclose(4000)
                ->toToast();
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Terjadi kesalahan saat impor: ' . $e->getMessage())
                ->autoclose(5000)
                ->toToast();
        }

        return redirect()->back();
    }

    public function exportTemplate()
    {
        return Excel::download(new ArsipUtamaTemplateExport, 'format_import_arsip_utama.xlsx');
    }

    public function toggleStatus(Request $request)
    {
        try {
            $arsip = ArsipUtama::findOrFail($request->id);
            $arsip->is_active = $request->status;
            $arsip->save();

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diubah'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ], 500);
        }
    }
}
