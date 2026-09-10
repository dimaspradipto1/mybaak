<?php

namespace App\Http\Controllers;

use App\Models\Wasdalbin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\DataTables\WasdalbinDataTable;
use App\Http\Requests\WasdalbinRequest;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\Permission;

use App\Traits\HandleGoogleDrive;

class WasdalbinController extends Controller
{
    use HandleGoogleDrive;

    /**
     * Display a listing of the resource.
     */
    public function index(WasdalbinDataTable $dataTable)
    {
        $title = 'Wasdalbin';
        return $dataTable->render('pages.wasdalbin.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Form Wasdalbin';
        return view('pages.wasdalbin.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WasdalbinRequest $request)
    {
        try {
            $data = $request->validated();
            $data['users_id'] = Auth::id();

            // Set fakultas otomatis jika tidak ada di request
            if (!isset($data['fakultas']) || empty($data['fakultas'])) {
                $data['fakultas'] = Auth::user()->fakultas;
            }

            // Cek apakah wasdalbin dengan nama, tahun, dan fakultas yang sama sudah ada
            $existing = Wasdalbin::where('nama_wasdalbin', $data['nama_wasdalbin'])
                ->where('tahun', $data['tahun'])
                ->where('fakultas', $data['fakultas'])
                ->first();

            if ($existing) {
                Alert::warning('Peringatan', 'Data Wasdalbin dengan nama, tahun, dan fakultas tersebut sudah ada.')
                    ->autoclose(5000)->toToast()->timerProgressBar();
                return redirect()->back()->withInput();
            }

            if ($request->hasFile('file')) {
                $uploaded = $request->file('file');
                $safeOriginal = preg_replace('/[^A-Za-z0-9\.\-_ ]/', '', $uploaded->getClientOriginalName());
                $filename = $safeOriginal;

                // Upload via Google Drive API
                $service = $this->driveService();
                $wasdalbinFolderId = env('GOOGLE_DRIVE_WASDALBIN_FOLDER_ID');

                if (!$wasdalbinFolderId) {
                    throw new \Exception('GOOGLE_DRIVE_WASDALBIN_FOLDER_ID tidak ditemukan di .env');
                }

                $fileMetadata = new \Google\Service\Drive\DriveFile([
                    'name' => $filename,
                    'parents' => [$wasdalbinFolderId],
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

            Wasdalbin::create($data);

            Alert::success('Berhasil', 'Wasdalbin Berhasil Ditambahkan')
                ->autoclose(4000)
                ->toToast()
                ->timerProgressBar()
                ->iconHtml('<i class="fa fa-check-circle"></i>');

            return redirect()->route('wasdalbin.index');
        } catch (\Throwable $th) {
            Log::error('Error storing Wasdalbin: ' . $th->getMessage());

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
    public function show(Wasdalbin $wasdalbin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Wasdalbin $wasdalbin)
    {
        $title = 'Form Wasdalbin';
        return view('pages.wasdalbin.edit', compact('wasdalbin', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(WasdalbinRequest $request, Wasdalbin $wasdalbin)
    {
        try {
            $data = $request->validated();
            $data['users_id'] = Auth::id();

            // Set fakultas otomatis jika tidak ada di request
            if (!isset($data['fakultas']) || empty($data['fakultas'])) {
                $data['fakultas'] = Auth::user()->fakultas;
            }

            // Cek apakah wasdalbin lain dengan nama, tahun, dan fakultas yang sama sudah ada
            $existing = Wasdalbin::where('nama_wasdalbin', $data['nama_wasdalbin'])
                ->where('tahun', $data['tahun'])
                ->where('fakultas', $data['fakultas'])
                ->where('id', '!=', $wasdalbin->id)
                ->first();

            if ($existing) {
                Alert::warning('Peringatan', 'Data Wasdalbin dengan nama, tahun, dan fakultas tersebut sudah ada.')
                    ->autoclose(5000)->toToast()->timerProgressBar();
                return redirect()->back()->withInput();
            }

            if ($request->hasFile('file')) {
                // Hapus file lama jika ada (Drive or Local)
                if ($wasdalbin->file) {
                    if (str_contains($wasdalbin->file, 'drive.google.com')) {
                        $this->deleteDriveFile($wasdalbin->file);
                    } else {
                        $oldPath = public_path($wasdalbin->file);
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
                $wasdalbinFolderId = env('GOOGLE_DRIVE_WASDALBIN_FOLDER_ID');

                if (!$wasdalbinFolderId) {
                    throw new \Exception('GOOGLE_DRIVE_WASDALBIN_FOLDER_ID tidak ditemukan di .env');
                }

                $fileMetadata = new \Google\Service\Drive\DriveFile([
                    'name' => $filename,
                    'parents' => [$wasdalbinFolderId],
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

            $wasdalbin->update($data);

            Alert::success('Berhasil', 'Data Berhasil Diubah')
                ->autoclose(3000)
                ->toToast()
                ->timerProgressBar()
                ->iconHtml('<i class="fa fa-check-circle"></i>');

            return redirect()->route('wasdalbin.index');
        } catch (\Throwable $th) {
            Log::error('Error updating Wasdalbin: ' . $th->getMessage());
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
    public function destroy(Wasdalbin $wasdalbin)
    {
        $wasdalbin = Wasdalbin::findOrFail($wasdalbin->id);
        if ($wasdalbin->file) {
            if (str_contains($wasdalbin->file, 'drive.google.com')) {
                $this->deleteDriveFile($wasdalbin->file);
            } else {
                $fullPath = public_path($wasdalbin->file);
                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }
        }
        $wasdalbin->delete();

        Alert::success('Berhasil', 'Data Berhasil Dihapus')
            ->autoclose(3000)
            ->toToast()
            ->timerProgressBar()
            ->iconHtml('<i class="fa fa-check-circle"></i>');
        return redirect()->route('wasdalbin.index');
    }

    public function toggleStatus(Request $request)
    {
        try {
            $wasdalbin = Wasdalbin::findOrFail($request->id);
            $wasdalbin->is_active = $request->status;
            $wasdalbin->save();

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
