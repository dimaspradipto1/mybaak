<?php

namespace App\Http\Controllers;

use App\Models\KategoriArsip;
use Illuminate\Http\Request;
use App\DataTables\KategoriArsipDataTable;
use App\Http\Requests\KategoriArsipRequest;
use RealRashid\SweetAlert\Facades\Alert;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KategoriArsipImport;
use App\Exports\KategoriArsipTemplateExport;

class KategoriArsipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(KategoriArsipDataTable $dataTable)
    {
        $title = 'Kategori Arsip';
        return $dataTable->render('pages.kategoriarsip.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Form Kategori Arsip';
        return view('pages.kategoriarsip.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KategoriArsipRequest $request)
    {
        $validated = $request->validated();
        KategoriArsip::create($validated);
        Alert::success('Data berhasil ditambahkan')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar()
            ->iconHtml('<i class="fa fa-check-circle"></i>');
        return redirect()->route('kategoriarsip.index');
    }

    public function storeAjax(Request $request)
    {
        $request->validate([
            'kategori_arsip' => 'required|string|max:255',
        ]);

        $kategori = KategoriArsip::firstOrCreate([
            'kategori_arsip' => trim($request->kategori_arsip)
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diproses',
            'data'    => $kategori
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(KategoriArsip $kategoriarsip)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KategoriArsip $kategoriarsip)
    {
        $title = 'Form Kategori Arsip';
        return view('pages.kategoriarsip.edit', compact('kategoriarsip', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(KategoriArsipRequest $request, KategoriArsip $kategoriarsip)
    {
        $kategoriarsip->update($request->validated());
        Alert::success('Data berhasil diupdate')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar()
            ->iconHtml('<i class="fa fa-check-circle"></i>');
        return redirect()->route('kategoriarsip.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KategoriArsip $kategoriarsip)
    {
        $kategoriarsip->delete();
        Alert::success('Data berhasil dihapus')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar()
            ->iconHtml('<i class="fa fa-check-circle"></i>');
        return redirect()->route('kategoriarsip.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new KategoriArsipImport, $request->file('file'));
            
            Alert::success('Sukses', 'Data Kategori Arsip berhasil diimpor')
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

        return redirect()->route('kategoriarsip.index');
    }

    public function exportTemplate()
    {
        return Excel::download(new KategoriArsipTemplateExport, 'template_kategori_arsip.xlsx');
    }
}
