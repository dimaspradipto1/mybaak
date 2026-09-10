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

class SuratAkademikController extends Controller
{
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

        $data = [
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
}
