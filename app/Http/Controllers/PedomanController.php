<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pedoman;
use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;
use App\DataTables\PedomanDataTable;
use Google\Service\Drive\Permission;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PedomanRequest;
use Illuminate\Support\Facades\Storage;
use Google\Service\Drive as GoogleDrive;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\File;

use App\Traits\HandleGoogleDrive;

class PedomanController extends Controller
{
    use HandleGoogleDrive;

    /**
     * Display a listing of the resource.
     */
    public function index(PedomanDataTable $dataTable)
    {
        $title = 'Pedoman';
        return $dataTable->render('pages.pedoman.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Form Pedoman';
        $users = User::all();
        return view('pages.pedoman.create', compact('users', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PedomanRequest $request)
    {
        try {
            $data = $request->validated();
            $data['users_id'] = Auth::id();

            // Set fakultas otomatis jika tidak ada di request
            if (!isset($data['fakultas']) || empty($data['fakultas'])) {
                $data['fakultas'] = Auth::user()->fakultas;
            }

            // Cek apakah pedoman dengan nama, tahun, dan fakultas yang sama sudah ada
            $existing = Pedoman::where('nama_pedoman', $data['nama_pedoman'])
                ->where('tahun', $data['tahun'])
                ->where('fakultas', $data['fakultas'])
                ->first();

            if ($existing) {
                Alert::warning('Peringatan', 'Data pedoman dengan nama, tahun, dan fakultas tersebut sudah ada.')
                    ->autoclose(5000)->toToast()->timerProgressBar();
                return redirect()->back()->withInput();
            }

            if ($request->hasFile('file')) {
                $uploaded = $request->file('file');
                $safeOriginal = preg_replace('/[^A-Za-z0-9\.\-_ ]/', '', $uploaded->getClientOriginalName());
                $filename = $safeOriginal;

                // Upload via Google Drive API
                $service = $this->driveService();
                $pedomanFolderId = env('GOOGLE_DRIVE_PEDOMAN_FOLDER_ID');

                if (!$pedomanFolderId) {
                    throw new \Exception('GOOGLE_DRIVE_PEDOMAN_FOLDER_ID tidak ditemukan di .env');
                }

                $fileMetadata = new \Google\Service\Drive\DriveFile([
                    'name' => $filename,
                    'parents' => [$pedomanFolderId],
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

            Pedoman::create($data);

            Alert::success('Berhasil', 'Pedoman Berhasil Ditambahkan')
                ->autoclose(4000)
                ->toToast()
                ->timerProgressBar()
                ->iconHtml('<i class="fa fa-check-circle"></i>');

            return redirect()->route('pedoman.index');
        } catch (\Throwable $th) {
            Log::error('Error storing Pedoman: ' . $th->getMessage());

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
    public function show(Pedoman $pedoman)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pedoman $pedoman)
    {
        $title = 'Form Pedoman';
        $users = User::all();
        return view('pages.pedoman.edit', compact('users', 'pedoman', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PedomanRequest $request, Pedoman $pedoman)
    {
        try {
            $data = $request->validated();
            $data['users_id'] = Auth::id();

            // Set fakultas otomatis jika tidak ada di request
            if (!isset($data['fakultas']) || empty($data['fakultas'])) {
                $data['fakultas'] = Auth::user()->fakultas;
            }

            // Cek apakah pedoman lain dengan nama, tahun, dan fakultas yang sama sudah ada
            $existing = Pedoman::where('nama_pedoman', $data['nama_pedoman'])
                ->where('tahun', $data['tahun'])
                ->where('fakultas', $data['fakultas'])
                ->where('id', '!=', $pedoman->id)
                ->first();

            if ($existing) {
                Alert::warning('Peringatan', 'Data pedoman dengan nama, tahun, dan fakultas tersebut sudah ada.')
                    ->autoclose(5000)->toToast()->timerProgressBar();
                return redirect()->back()->withInput();
            }

            if ($request->hasFile('file')) {
                // Hapus file lama jika ada (Drive or Local)
                if ($pedoman->file) {
                    if (str_contains($pedoman->file, 'drive.google.com')) {
                        $this->deleteDriveFile($pedoman->file);
                    } else {
                        $oldPath = public_path($pedoman->file);
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
                $pedomanFolderId = env('GOOGLE_DRIVE_PEDOMAN_FOLDER_ID');

                if (!$pedomanFolderId) {
                    throw new \Exception('GOOGLE_DRIVE_PEDOMAN_FOLDER_ID tidak ditemukan di .env');
                }

                $fileMetadata = new \Google\Service\Drive\DriveFile([
                    'name' => $filename,
                    'parents' => [$pedomanFolderId],
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

            $pedoman->update($data);

            Alert::success('Berhasil', 'Data Berhasil Diubah')
                ->autoclose(3000)
                ->toToast()
                ->timerProgressBar()
                ->iconHtml('<i class="fa fa-check-circle"></i>');

            return redirect()->route('pedoman.index');
        } catch (\Throwable $th) {
            Log::error('Error updating Pedoman: ' . $th->getMessage());
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
    public function destroy(Pedoman $pedoman)
    {
        $pedoman = Pedoman::findOrFail($pedoman->id);

        if ($pedoman->file) {
            if (str_contains($pedoman->file, 'drive.google.com')) {
                $this->deleteDriveFile($pedoman->file);
            } else {
                $fullPath = public_path($pedoman->file);
                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }
        }

        $pedoman->delete();

        Alert::success('Success', 'Data and file deleted successfully')
            ->autoclose(3000)
            ->toToast()
            ->timerProgressBar();
        return redirect()->route('pedoman.index');
    }

    public function toggleStatus(Request $request)
    {
        try {
            $pedoman = Pedoman::findOrFail($request->id);
            $pedoman->is_active = $request->status;
            $pedoman->save();

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
