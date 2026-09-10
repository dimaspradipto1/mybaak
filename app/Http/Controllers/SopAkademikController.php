<?php

namespace App\Http\Controllers;

use App\Models\SopAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\DataTables\SopAkademikDataTable;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Requests\SopAkademikRequest;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\Permission;

use App\Traits\HandleGoogleDrive;

class SopAkademikController extends Controller
{
    use HandleGoogleDrive;

    /**
     * Display a listing of the resource.
     */
    public function index(SopAkademikDataTable $datatable)
    {
        $title = 'SOP Akademik';
        return $datatable->render('pages.sopakademik.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Form SOP Akademik';
        return view('pages.sopakademik.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SopAkademikRequest $request)
    {
        try {
            $data = $request->validated();
            $data['users_id'] = Auth::id();

            // Set fakultas otomatis jika tidak ada di request
            if (!isset($data['fakultas']) || empty($data['fakultas'])) {
                $data['fakultas'] = Auth::user()->fakultas;
            }

            // Cek apakah SOP dengan nama dan fakultas yang sama sudah ada
            $existing = SopAkademik::where('nama_sop', $data['nama_sop'])
                ->where('fakultas', $data['fakultas'])
                ->first();

            if ($existing) {
                Alert::warning('Peringatan', 'Data SOP Akademik dengan nama dan fakultas tersebut sudah ada.')
                    ->autoclose(5000)->toToast()->timerProgressBar();
                return redirect()->back()->withInput();
            }

            if ($request->hasFile('file')) {
                $uploaded = $request->file('file');
                $safeOriginal = preg_replace('/[^A-Za-z0-9\.\-_ ]/', '', $uploaded->getClientOriginalName());
                $filename = $safeOriginal;

                // Upload via Google Drive API
                $service = $this->driveService();
                $sopakademikFolderId = env('GOOGLE_DRIVE_SOP_FOLDER_ID');

                if (!$sopakademikFolderId) {
                    throw new \Exception('GOOGLE_DRIVE_SOP_FOLDER_ID tidak ditemukan di .env');
                }

                $fileMetadata = new \Google\Service\Drive\DriveFile([
                    'name' => $filename,
                    'parents' => [$sopakademikFolderId],
                ]);

                $content = File::get($uploaded->getRealPath());

                $file = $service->files->create($fileMetadata, [
                    'data' => $content,
                    'uploadType' => 'multipart',
                    'fields' => 'id',
                ]);

                $fileId = $file->getId();

                // Jadikan public
                $this->makeDriveFilePublic($fileId);

                // Simpan link (tanpa spasi!)
                $data['file'] = "https://drive.google.com/file/d/{$fileId}/view";
            }

            SopAkademik::create($data);

            Alert::success('Berhasil', 'SOP Akademik Berhasil Ditambahkan')
                ->autoclose(4000)
                ->toToast()
                ->timerProgressBar()
                ->iconHtml('<i class="fa fa-check-circle"></i>');

            return redirect()->route('sopakademik.index');
        } catch (\Throwable $th) {
            Log::error('Error storing SOP Akademik: ' . $th->getMessage());

            Alert::error('Gagal', 'Gagal menambahkan data: ' . $th->getMessage())
                ->autoclose(5000)
                ->toToast()
                ->timerProgressBar()
                ->iconHtml('<i class="fa fa-times-circle"></i>');

            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SopAkademik $sopAkademik)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SopAkademik $sopakademik)
    {
        $title = 'Form SOP Akademik';
        return view('pages.sopakademik.edit', compact('sopakademik', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SopAkademikRequest $request, SopAkademik $sopakademik)
    {
        try {
            $data = $request->validated();
            $data['users_id'] = Auth::id();

            // Set fakultas otomatis jika tidak ada di request
            if (!isset($data['fakultas']) || empty($data['fakultas'])) {
                $data['fakultas'] = Auth::user()->fakultas;
            }

            // Cek apakah SOP lain dengan nama dan fakultas yang sama sudah ada
            $existing = SopAkademik::where('nama_sop', $data['nama_sop'])
                ->where('fakultas', $data['fakultas'])
                ->where('id', '!=', $sopakademik->id)
                ->first();

            if ($existing) {
                Alert::warning('Peringatan', 'Data SOP Akademik dengan nama dan fakultas tersebut sudah ada.')
                    ->autoclose(5000)->toToast()->timerProgressBar();
                return redirect()->back()->withInput();
            }

            if ($request->hasFile('file')) {
                // Hapus file lama jika ada (Drive or Local)
                if ($sopakademik->file) {
                    if (str_contains($sopakademik->file, 'drive.google.com')) {
                        $this->deleteDriveFile($sopakademik->file);
                    } else {
                        $oldPath = public_path($sopakademik->file);
                        if (File::exists($oldPath)) {
                            File::delete($oldPath);
                        }
                    }
                }

                $uploaded = $request->file('file');
                $safeOriginal = preg_replace('/[^A-Za-z0-9\.\-_ ]/', '', $uploaded->getClientOriginalName());
                $filename = $safeOriginal;

                // Upload via Google Drive API
                $service = $this->driveService();
                $sopakademikFolderId = env('GOOGLE_DRIVE_SOP_FOLDER_ID');

                if (!$sopakademikFolderId) {
                    throw new \Exception('GOOGLE_DRIVE_SOP_FOLDER_ID tidak ditemukan di .env');
                }

                $fileMetadata = new \Google\Service\Drive\DriveFile([
                    'name' => $filename,
                    'parents' => [$sopakademikFolderId],
                ]);

                $content = File::get($uploaded->getRealPath());

                $file = $service->files->create($fileMetadata, [
                    'data' => $content,
                    'uploadType' => 'multipart',
                    'fields' => 'id',
                ]);

                $fileId = $file->getId();
                $this->makeDriveFilePublic($fileId);
                $data['file'] = "https://drive.google.com/file/d/{$fileId}/view";
            } else {
                unset($data['file']);
            }

            $sopakademik->update($data);

            Alert::success('Berhasil', 'Data Berhasil Diubah')
                ->autoclose(3000)
                ->toToast()
                ->timerProgressBar()
                ->iconHtml('<i class="fa fa-check-circle"></i>');

            return redirect()->route('sopakademik.index');
        } catch (\Throwable $th) {
            Log::error('Error updating SOP Akademik: ' . $th->getMessage());
            Alert::error('Gagal', 'Data Gagal Diubah: ' . $th->getMessage())
                ->autoclose(3000)
                ->toToast()
                ->timerProgressBar()
                ->iconHtml('<i class="fa fa-times-circle"></i>');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SopAkademik $sopakademik)
    {
        $sopakademik = SopAKademik::FindOrFail($sopakademik->id);

        if ($sopakademik->file) {
            if (str_contains($sopakademik->file, 'drive.google.com')) {
                $this->deleteDriveFile($sopakademik->file);
            } else {
                $fullPath = public_path($sopakademik->file);
                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }
        }

        $sopakademik->delete();

        Alert::success('Berhasil', 'Data Berhasil Dihapus')
            ->autoclose(3000)
            ->toToast()
            ->timerProgressBar()
            ->iconHtml('<i class="fa fa-check-circle"></i>');
        return redirect()->route('sopakademik.index');
    }

    public function toggleStatus(Request $request)
    {
        try {
            $sop = SopAkademik::findOrFail($request->id);
            $sop->is_active = $request->status;
            $sop->save();

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
