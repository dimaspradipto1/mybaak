<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\DataTables\MahasiswaDataTable;
use App\Http\Requests\MahasiswaRequest;
use RealRashid\SweetAlert\Facades\Alert;

use App\Imports\MahasiswaImport;
use App\Exports\MahasiswaTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class MahasiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(MahasiswaDataTable $dataTable)
    {
        $title = 'Mahasiswa';
        return $dataTable->render('pages.mahasiswa.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Form Mahasiswa';
        $isMahasiswa = Mahasiswa::where('users_id', Auth::id())->exists();
        $programStudi = ProgramStudi::all();
        $users = User::whereHas('role', function($query) {
            $query->where('nama_role', 'MAHASISWA');
        })->get();
        return view('pages.mahasiswa.create', compact('users', 'programStudi', 'isMahasiswa', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MahasiswaRequest $request)
    {
        $data = $request->validated();

        $userId = Auth::user()->is_mahasiswa ? Auth::id() : ($data['users_id'] ?? null);

        if (!$userId) {
            Alert::error('Gagal', 'User wajib dipilih!')->autoclose(4000)->toToast();
            return redirect()->back()->withInput();
        }

        $data['users_id'] = $userId;

        // Cek apakah user sudah memiliki data mahasiswa
        $existing = Mahasiswa::where('users_id', $userId)->first();
        if ($existing) {
            Alert::warning('Peringatan', 'Pengguna ini sudah terdaftar sebagai data mahasiswa.')
                ->autoclose(5000)->toToast()->timerProgressBar();
            return redirect()->back()->withInput();
        }

        Mahasiswa::create($data);
        Alert::success('Mahasiswa berhasil ditambahkan')
            ->autoclose(4000)
            ->toToast()
            ->timerProgressBar()
            ->iconHtml('<i class="fa-solid fa-thumbs-up"></i>');
        return redirect()->route('mahasiswa.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Mahasiswa $mahasiswa)
    {
        // Memuat relasi 'user' dan 'programStudi'
        $mahasiswa = Mahasiswa::with('user', 'programStudi')->findOrFail($mahasiswa->id);
        $programStudi = ProgramStudi::all();
        return view('pages.mahasiswa.show', compact('mahasiswa', 'programStudi'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mahasiswa $mahasiswa)
    {
        $title = 'Form Mahasiswa';
        $mahasiswa = Mahasiswa::with('user', 'programStudi')->findOrFail($mahasiswa->id);
        $users = User::whereHas('role', function($query) {
            $query->where('nama_role', 'MAHASISWA');
        })->get();
        $programStudi = ProgramStudi::all();
        return view('pages.mahasiswa.edit', compact('mahasiswa', 'programStudi', 'users', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        // Update name di tabel users melalui relasi
        if ($request->has('name') && $mahasiswa->user) {
            $mahasiswa->user->update(['name' => $request->name]);
        }

        // Update data mahasiswa (kecuali field 'name' yang ada di tabel users)
        $mahasiswa->update($request->except('name'));

        Alert::success('Mahasiswa berhasil diupdate')
            ->autoclose(4000)
            ->toToast()
            ->timerProgressBar()
            ->iconHtml('<i class="fa-solid fa-thumbs-up"></i>');
        return redirect()->route('mahasiswa.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mahasiswa $mahasiswa)
    {
        $mahasiswa->delete();
        Alert::success('Mahasiswa berhasil dihapus')
            ->autoclose(4000)
            ->toToast()
            ->timerProgressBar()
            ->iconHtml('<i class="fa-solid fa-thumbs-up"></i>');
        return redirect()->route('mahasiswa.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new MahasiswaImport, $request->file('file'));
            
            Alert::success('Sukses', 'Data Mahasiswa berhasil diimpor')
                ->autoclose(4000)
                ->toToast();
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMsg = 'Error pada baris ' . $failures[0]->row() . ': ' . $failures[0]->errors()[0];
            
            Alert::error('Gagal', $errorMsg)
                ->autoclose(5000)
                ->toToast();
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Terjadi kesalahan: ' . $e->getMessage())
                ->autoclose(5000)
                ->toToast();
        }

        return redirect()->back();
    }

    public function exportTemplate()
    {
        return Excel::download(new MahasiswaTemplateExport, 'format_import_mahasiswa.xlsx');
    }
}
