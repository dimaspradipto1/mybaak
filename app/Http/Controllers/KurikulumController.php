<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\DataTables\KurikulumDataTable;
use App\Http\Requests\KurikulumRequest;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\Permission;
use Illuminate\Support\Facades\File;

use App\Traits\HandleGoogleDrive;

class KurikulumController extends Controller
{
    use HandleGoogleDrive;

    /**
     * Display a listing of the resource.
     */
    public function index(KurikulumDataTable $dataTable)
    {
        $title = 'Kurikulum';
        return $dataTable->render('pages.kurikulum.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Form Kurikulum';
        $users = User::all();
        return view('pages.kurikulum.create', compact('users', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KurikulumRequest $request)
    {
        try {
            $data = $request->validated();
            $data['users_id'] = Auth::id();

            // Set fakultas otomatis jika tidak ada di request
            if (!isset($data['fakultas']) || empty($data['fakultas'])) {
                $data['fakultas'] = Auth::user()->fakultas;
            }

            // Cek apakah kurikulum dengan nama, tahun, dan fakultas yang sama sudah ada
            $existing = Kurikulum::where('nama_kurikulum', $data['nama_kurikulum'])
                ->where('tahun', $data['tahun'])
                ->where('fakultas', $data['fakultas'])
                ->first();

            if ($existing) {
                Alert::warning('Peringatan', 'Data kurikulum dengan nama, tahun, dan fakultas tersebut sudah ada.')
                    ->autoclose(5000)->toToast()->timerProgressBar();
                return redirect()->back()->withInput();
            }

            if ($request->hasFile('file')) {
                $uploaded = $request->file('file');
                $safeOriginal = preg_replace('/[^A-Za-z0-9\.\-_ ]/', '', $uploaded->getClientOriginalName());
                $filename = $safeOriginal;

                // Upload via Google Drive API
                $service = $this->driveService();
                $kurikulumFolderId = env('GOOGLE_DRIVE_KURIKULUM_FOLDER_ID');

                if (!$kurikulumFolderId) {
                    throw new \Exception('GOOGLE_DRIVE_KURIKULUM_FOLDER_ID tidak ditemukan di .env');
                }

                $fileMetadata = new \Google\Service\Drive\DriveFile([
                    'name' => $filename,
                    'parents' => [$kurikulumFolderId],
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

            Kurikulum::create($data);

            Alert::success('Berhasil', 'Kurikulum Berhasil Ditambahkan')
                ->autoclose(4000)
                ->toToast()
                ->timerProgressBar()
                ->iconHtml('<i class="fa fa-check-circle"></i>');

            return redirect()->route('kurikulum.index');
        } catch (\Throwable $th) {
            Log::error('Error storing Kurikulum: ' . $th->getMessage());

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
    public function show(Kurikulum $kurikulum)
    {
        // $title = 'Detail Kurikulum';
        // return view('pages.kurikulum.show', compact('kurikulum', 'title'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kurikulum $kurikulum)
    {
        $title = 'Form Kurikulum';
        return view('pages.kurikulum.edit', compact('kurikulum', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(KurikulumRequest $request, Kurikulum $kurikulum)
    {
        try {
            $data = $request->validated();
            $data['users_id'] = Auth::id();

            // Set fakultas otomatis jika tidak ada di request
            if (!isset($data['fakultas']) || empty($data['fakultas'])) {
                $data['fakultas'] = Auth::user()->fakultas;
            }

            // Cek apakah kurikulum lain dengan nama, tahun, dan fakultas yang sama sudah ada
            $existing = Kurikulum::where('nama_kurikulum', $data['nama_kurikulum'])
                ->where('tahun', $data['tahun'])
                ->where('fakultas', $data['fakultas'])
                ->where('id', '!=', $kurikulum->id)
                ->first();

            if ($existing) {
                Alert::warning('Peringatan', 'Data kurikulum dengan nama, tahun, dan fakultas tersebut sudah ada.')
                    ->autoclose(5000)->toToast()->timerProgressBar();
                return redirect()->back()->withInput();
            }

            if ($request->hasFile('file')) {
                // Hapus file lama jika ada (Drive or Local)
                if ($kurikulum->file) {
                    if (str_contains($kurikulum->file, 'drive.google.com')) {
                        $this->deleteDriveFile($kurikulum->file);
                    } else {
                        $oldPath = public_path($kurikulum->file);
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
                $kurikulumFolderId = env('GOOGLE_DRIVE_KURIKULUM_FOLDER_ID');

                if (!$kurikulumFolderId) {
                    throw new \Exception('GOOGLE_DRIVE_KURIKULUM_FOLDER_ID tidak ditemukan di .env');
                }

                $fileMetadata = new \Google\Service\Drive\DriveFile([
                    'name' => $filename,
                    'parents' => [$kurikulumFolderId],
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

            $kurikulum->update($data);

            Alert::success('Berhasil', 'Data Berhasil Diubah')
                ->autoclose(3000)
                ->toToast()
                ->timerProgressBar()
                ->iconHtml('<i class="fa fa-check-circle"></i>');

            return redirect()->route('kurikulum.index');
        } catch (\Throwable $th) {
            Log::error('Error updating Kurikulum: ' . $th->getMessage());
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
    public function destroy(Kurikulum $kurikulum)
    {
        $kurikulum = Kurikulum::findOrFail($kurikulum->id);

        if ($kurikulum->file) {
            if (str_contains($kurikulum->file, 'drive.google.com')) {
                $this->deleteDriveFile($kurikulum->file);
            } else {
                $fullPath = public_path($kurikulum->file);
                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }
        }

        $kurikulum->delete();

        Alert::success('Success', 'Data and file deleted successfully')
            ->autoclose(3000)
            ->toToast()
            ->timerProgressBar();
        return redirect()->route('kurikulum.index');
    }

    public function toggleStatus(Request $request)
    {
        try {
            $kurikulum = Kurikulum::findOrFail($request->id);
            $kurikulum->is_active = $request->status;
            $kurikulum->save();

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
