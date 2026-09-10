<?php

use App\Http\Controllers\ArsipUtamaController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\JenisSKController;
use App\Http\Controllers\KategoriArsipController;
use App\Http\Controllers\KepanitiaanController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LpjKepanitiaanController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PedomanController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramStudiController;
use App\Http\Controllers\RekapitulasiArsipController;
use App\Http\Controllers\RekapitulasiSuratAktifController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SkController;
use App\Http\Controllers\SkKepanitiaanController;
use App\Http\Controllers\SopAkademikController;
use App\Http\Controllers\SuratAkademikController;
use App\Http\Controllers\SuratAktifController;
use App\Http\Controllers\TahunAkademikController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGuideController;
use App\Http\Controllers\UserGuideMahasiswaController;
use App\Http\Controllers\UserGuidePenggunaMahasiswaController;
use App\Http\Controllers\UserGuidePenggunaTatausahaController;
use App\Http\Controllers\UserGuideTatausahaController;
use App\Http\Controllers\WasdalbinController;
use App\Http\Controllers\UnitKerjaController;
use App\Http\Controllers\SemanticArsipController;
use Illuminate\Support\Facades\Route;


// 1. HALAMAN UTAMA (Semantic Search)
Route::get('/', [SemanticArsipController::class, 'index'])->name('semantic.index');

// 2. AUTHENTICATION
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'index')->name('login');
    Route::post('/loginproses', 'loginproses')->name('loginproses');
    Route::get('/register', 'register')->name('register');
    Route::post('/registerproses', 'registerproses')->name('registerproses');
    Route::get('/logout', 'logout')->name('logout');
});

// redirect rute lama agar tetap aman
Route::get('/semantic-search', function() {
    return redirect()->route('semantic.index');
});

Route::post('/suratAktif/pengajuan', [SuratAktifController::class, 'pengajuan'])->name('suratAktif.pengajuan');
Route::post('/suratAkademik/pengajuan', [SuratAkademikController::class, 'pengajuan'])->name('suratAkademik.pengajuan');
Route::get('/suratAktif/{suratAktif}/validasi', [SuratAktifController::class, 'validasi'])->name('suratAktif.validasi');
Route::get('/suratAkademik/{suratAkademik}/validasi', [SuratAkademikController::class, 'validasi'])->name('suratAkademik.validasi');
Route::get('/suratAktif/{suratAktif}/preview', [SuratAktifController::class, 'preview'])->name('suratAktif.preview');
Route::get('/suratAktif/{suratAktif}/print', [SuratAktifController::class, 'print'])->name('suratAktif.print');

Route::middleware(['auth', 'checkrole'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
    Route::get('/users/export-template', [UserController::class, 'exportTemplate'])->name('users.export-template');
    Route::resource('users', UserController::class);
    Route::get('/user/{id}/update-password', [UserController::class, 'showUpdatePasswordForm'])->name('users.showUpdatePasswordForm');
    Route::put('/user/{id}/update-password', [UserController::class, 'updatePassword'])->name('users.updatePassword');
    Route::resource('tahunAkademik', TahunAkademikController::class);
    Route::post('/mahasiswa/import', [MahasiswaController::class, 'import'])->name('mahasiswa.import');
    Route::get('/mahasiswa/export-template', [MahasiswaController::class, 'exportTemplate'])->name('mahasiswa.export-template');
    Route::resource('mahasiswa', MahasiswaController::class);
    Route::resource('programStudi', ProgramStudiController::class);
    Route::resource('jenissk', JenisSKController::class);
    Route::resource('pegawai', PegawaiController::class);
    Route::post('/dosen/import', [DosenController::class, 'import'])->name('dosen.import');
    Route::get('/dosen/export-template', [DosenController::class, 'exportTemplate'])->name('dosen.export-template');
    Route::resource('dosen', DosenController::class);
    Route::get('/suratAktif/export-template', [SuratAktifController::class, 'exportTemplate'])->name('suratAktif.export-template');
    Route::post('/suratAktif/import', [SuratAktifController::class, 'import'])->name('suratAktif.import');
    Route::post('/suratAktif/bulk-delete', [SuratAktifController::class, 'bulkDelete'])->name('suratAktif.bulkDelete');
    Route::post('/suratAktif/bulk-update-status', [SuratAktifController::class, 'bulkUpdateStatus'])->name('suratAktif.bulkUpdateStatus');
    Route::resource('suratAktif', SuratAktifController::class);
    Route::get('/suratAkademik/export-template', [SuratAkademikController::class, 'exportTemplate'])->name('suratAkademik.export-template');
    Route::post('/suratAkademik/import', [SuratAkademikController::class, 'import'])->name('suratAkademik.import');
    Route::post('/suratAkademik/bulk-delete', [SuratAkademikController::class, 'bulkDelete'])->name('suratAkademik.bulkDelete');
    Route::post('/suratAkademik/bulk-update-status', [SuratAkademikController::class, 'bulkUpdateStatus'])->name('suratAkademik.bulkUpdateStatus');
    Route::resource('suratAkademik', SuratAkademikController::class);
    Route::get('/suratAkademik/{suratAkademik}/editStatus', [SuratAkademikController::class, 'editStatus'])->name('suratAkademik.editStatus');
    Route::put('/suratAkademik/{suratAkademik}/updateStatus', [SuratAkademikController::class, 'updateStatus'])->name('suratAkademik.updateStatus');
    Route::resource('sopakademik', SopAkademikController::class);
    Route::resource('lpjkepanitiaan', LpjKepanitiaanController::class);
    Route::resource('pedoman', PedomanController::class);
    Route::resource('skkepanitiaan', SkKepanitiaanController::class);
    Route::resource('kurikulum', KurikulumController::class);
    Route::resource('wasdalbin', WasdalbinController::class);
    Route::resource('userGuide', UserGuideController::class);
    Route::resource('faq', FAQController::class);
    Route::get('/userguidepengguna', [FAQController::class, 'userguidepengguna'])->name('userguidepengguna');
    Route::resource('userGuideMahasiswa', UserGuideMahasiswaController::class);
    Route::get('userGuidePenggunaMahasiswa', [UserGuidePenggunaMahasiswaController::class, 'index'])->name('userGuidePenggunaMahasiswa');
    Route::resource('userGuideTatausaha', UserGuideTatausahaController::class);
    Route::get('userGuidePenggunaTatausaha', [UserGuidePenggunaTatausahaController::class, 'index'])->name('userGuidePenggunaTatausaha');
    Route::get('rekapitulasiarsip/export', [RekapitulasiArsipController::class, 'export'])->name('rekapitulasiarsip.export');
    Route::resource('rekapitulasiarsip', RekapitulasiArsipController::class);
    Route::get('rekapitulasisurataktif/export', [RekapitulasiSuratAktifController::class, 'export'])->name('rekapitulasisurataktif.export');
    Route::resource('rekapitulasisurataktif', RekapitulasiSuratAktifController::class);
    Route::resource('profile', ProfileController::class);
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.updatePassword');
    Route::post('/kategoriarsip/store-ajax', [KategoriArsipController::class, 'storeAjax'])->name('kategoriarsip.store-ajax');
    Route::post('/kategoriarsip/import', [KategoriArsipController::class, 'import'])->name('kategoriarsip.import');
    Route::get('/kategoriarsip/export-template', [KategoriArsipController::class, 'exportTemplate'])->name('kategoriarsip.export-template');
    Route::resource('kategoriarsip', KategoriArsipController::class);
    Route::post('/arsiputama/import', [ArsipUtamaController::class, 'import'])->name('arsiputama.import');
    Route::get('/arsiputama/export-template', [ArsipUtamaController::class, 'exportTemplate'])->name('arsiputama.export-template');
    Route::post('/arsiputama/toggle-status', [ArsipUtamaController::class, 'toggleStatus'])->name('arsiputama.toggle-status');
    Route::post('/skkepanitiaan/toggle-status', [SkKepanitiaanController::class, 'toggleStatus'])->name('skkepanitiaan.toggle-status');
    Route::post('/lpjkepanitiaan/toggle-status', [LpjKepanitiaanController::class, 'toggleStatus'])->name('lpjkepanitiaan.toggle-status');
    Route::post('/kurikulum/toggle-status', [KurikulumController::class, 'toggleStatus'])->name('kurikulum.toggle-status');
    Route::post('/pedoman/toggle-status', [PedomanController::class, 'toggleStatus'])->name('pedoman.toggle-status');
    Route::post('/sopakademik/toggle-status', [SopAkademikController::class, 'toggleStatus'])->name('sopakademik.toggle-status');
    Route::post('/wasdalbin/toggle-status', [WasdalbinController::class, 'toggleStatus'])->name('wasdalbin.toggle-status');
    Route::resource('arsiputama', ArsipUtamaController::class);
    Route::resource('unitkerja', UnitKerjaController::class);
    Route::resource('artikel', ArtikelController::class);
    Route::resource('role', RoleController::class);
    Route::get('/role/{role}/permission', [RoleController::class, 'editPermission'])->name('role.edit-permission');
    Route::put('/role/{role}/permission', [RoleController::class, 'updatePermission'])->name('role.update-permission');
});
