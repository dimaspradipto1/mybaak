@extends('layouts.dashboard.template')

@section('title', 'Backup Database & Storage')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-radius: 8px;">
        <i class="bi bi-check-circle-fill mr-2 me-2"></i>
        {{ session('success') }}
        <button type="button" class="close btn-close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close" style="outline: none;">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-radius: 8px;">
        <i class="bi bi-exclamation-triangle-fill mr-2 me-2"></i>
        {{ session('error') }}
        <button type="button" class="close btn-close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close" style="outline: none;">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if (isset($errors) && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-radius: 8px;">
        <i class="bi bi-exclamation-triangle-fill mr-2 me-2"></i>
        <strong>Periksa kembali berkas yang diunggah:</strong>
        <ul class="mb-0 mt-1 pl-3 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close btn-close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close" style="outline: none;">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="row align-items-stretch">

    {{-- Kolom Kiri: Ekspor & Impor Database --}}
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: #ffffff;">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-start mb-4" style="gap: 15px;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 48px; height: 48px; background-color: #2e6b34; color: #ffffff;">
                            <i class="bi bi-database-fill-gear" style="font-size: 20px;"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold mb-1" style="color: #1e293b; font-size: 17px;">Ekspor & Impor Database</h5>
                            <p class="text-muted small mb-0" style="line-height: 1.5;">
                                Unduh atau pulihkan struktur tabel dan data database aplikasi sebagai berkas SQL.
                            </p>
                        </div>
                    </div>

                    <div class="mb-4" style="font-size: 14px;">
                        <div class="row py-2 mb-1 align-items-center">
                            <div class="col-4 text-secondary font-weight-bold">Koneksi</div>
                            <div class="col-8">
                                <span class="badge badge-light bg-light text-dark border font-weight-bold px-2 py-1" style="font-size: 11.5px; letter-spacing: 0.4px;">
                                    {{ strtoupper($connection) }}
                                </span>
                            </div>
                        </div>
                        <div class="row py-2 mb-1 align-items-center">
                            <div class="col-4 text-secondary font-weight-bold">Database</div>
                            <div class="col-8">
                                <code style="color: #d63384; font-size: 13.5px; background: transparent; padding: 0;">{{ $databaseName }}</code>
                            </div>
                        </div>
                        <div class="row py-2 align-items-center">
                            <div class="col-4 text-secondary font-weight-bold">Isi backup</div>
                            <div class="col-8 text-dark">
                                Struktur dan data semua tabel MySQL ({{ $tableCount }} tabel)
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="alert border-0 mb-4 py-3 px-3" style="background-color: #e0f5ff; color: #055160; border-radius: 8px; font-size: 13px; line-height: 1.55;">
                        <i class="bi bi-shield-fill-check mr-1 me-1 text-primary"></i>
                        Hanya administrator yang dapat mengunduh dan memulihkan database. Simpan berkas SQL di lokasi aman karena berisi seluruh data aplikasi.
                    </div>

                    <div class="d-flex flex-wrap" style="gap: 10px;">
                        <a href="{{ route('backup.export-database') }}"
                           class="btn text-white font-weight-bold px-3 py-2"
                           style="background-color: #2e6b34; border-color: #2e6b34; font-size: 13.5px; border-radius: 6px;">
                            <i class="bi bi-download mr-1 me-1"></i> Unduh Backup SQL
                        </a>
                        <button type="button"
                                class="btn font-weight-bold px-3 py-2"
                                style="font-size: 13.5px; border-radius: 6px; color: #2e6b34; border: 1px solid #2e6b34; background: transparent;"
                                data-toggle="modal"
                                data-bs-toggle="modal"
                                data-target="#importDbModal"
                                data-bs-target="#importDbModal">
                            <i class="bi bi-upload mr-1 me-1"></i> Import / Pulihkan Database
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Backup & Impor Storage Public --}}
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: #ffffff;">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-start mb-4" style="gap: 15px;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 48px; height: 48px; background-color: #eaf3ec; color: #2e6b34;">
                            <i class="bi bi-folder-symlink" style="font-size: 20px;"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold mb-1" style="color: #1e293b; font-size: 17px;">Backup & Impor Storage Public</h5>
                            <p class="text-muted small mb-0" style="line-height: 1.5;">
                                Arsipkan atau pulihkan semua file pada folder <code style="color: #d63384;">storage/app/public</code> ke dalam berkas ZIP.
                            </p>
                        </div>
                    </div>

                    <div class="mb-4" style="font-size: 14px;">
                        <div class="row py-2 mb-1 align-items-center">
                            <div class="col-4 text-secondary font-weight-bold">Direktori</div>
                            <div class="col-8">
                                <code style="color: #d63384; font-size: 13.5px; background: transparent; padding: 0;">storage/app/public</code>
                            </div>
                        </div>
                        <div class="row py-2 mb-1 align-items-center">
                            <div class="col-4 text-secondary font-weight-bold">Total Berkas</div>
                            <div class="col-8 text-dark">
                                {{ number_format($storageFileCount, 0, ',', '.') }} berkas ({{ $storageSizeFormatted }})
                            </div>
                        </div>
                        <div class="row py-2 align-items-center">
                            <div class="col-4 text-secondary font-weight-bold">Format Arsip</div>
                            <div class="col-8 text-dark">
                                ZIP Archive (<code>.zip</code>)
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="alert border-0 mb-4 py-3 px-3" style="background-color: #fff3cd; color: #664d03; border-radius: 8px; font-size: 13px; line-height: 1.55;">
                        <i class="bi bi-exclamation-triangle-fill mr-1 me-1 text-warning"></i>
                        Backup storage memuat seluruh berkas lampiran, gambar, foto pimpinan, berita, dan dokumen publik aplikasi.
                    </div>

                    <div class="d-flex flex-wrap" style="gap: 10px;">
                        <a href="{{ route('backup.export-storage') }}"
                           class="btn text-white font-weight-bold px-3 py-2"
                           style="background-color: #2e6b34; border-color: #2e6b34; font-size: 13.5px; border-radius: 6px;">
                            <i class="bi bi-download mr-1 me-1"></i> Unduh Backup Storage ZIP
                        </a>
                        <button type="button"
                                class="btn font-weight-bold px-3 py-2"
                                style="font-size: 13.5px; border-radius: 6px; color: #2e6b34; border: 1px solid #2e6b34; background: transparent;"
                                data-toggle="modal"
                                data-bs-toggle="modal"
                                data-target="#importStorageModal"
                                data-bs-target="#importStorageModal">
                            <i class="bi bi-upload mr-1 me-1"></i> Import / Pulihkan Storage
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Modal Import / Pulihkan Database --}}
<div class="modal fade" id="importDbModal" tabindex="-1" role="dialog" aria-labelledby="importDbModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: #2e6b34;">
                <h5 class="modal-title font-weight-bold" id="importDbModalLabel" style="font-size: 16px;">
                    <i class="bi bi-database-up mr-2 me-2"></i>Import / Pulihkan Database SQL
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9; outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('backup.import-database') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0 small mb-3" style="border-radius: 8px;">
                        <i class="bi bi-exclamation-triangle-fill mr-1 me-1 text-warning"></i>
                        <strong>Perhatian:</strong> Memulihkan database akan menimpa tabel dan data yang ada saat ini dengan isi dari berkas backup SQL yang Anda unggah.
                    </div>
                    <div class="form-group mb-2">
                        <label for="sql_file" class="font-weight-bold text-dark">
                            Pilih Berkas Backup Database (<code>.sql</code> atau <code>.zip</code>) <span class="text-danger">*</span>
                        </label>
                        <input type="file"
                               id="sql_file"
                               name="sql_file"
                               class="form-control-file form-control"
                               accept=".sql,.zip,.txt"
                               required>
                        <small class="form-text text-muted mt-1">
                            Format yang didukung: <strong>.sql</strong> atau <strong>.zip</strong> (berisi file .sql). Maksimal ukuran berkas: 500 MB.
                        </small>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background-color: #2e6b34; border-color: #2e6b34;">
                        <i class="bi bi-upload mr-1 me-1"></i> Pulihkan Database Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Import / Pulihkan Storage Public --}}
<div class="modal fade" id="importStorageModal" tabindex="-1" role="dialog" aria-labelledby="importStorageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: #2e6b34;">
                <h5 class="modal-title font-weight-bold" id="importStorageModalLabel" style="font-size: 16px;">
                    <i class="bi bi-folder-symlink mr-2 me-2"></i>Import / Pulihkan Storage Public (ZIP)
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9; outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('backup.import-storage') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 small mb-3" style="border-radius: 8px;">
                        <i class="bi bi-info-circle-fill mr-1 me-1 text-primary"></i>
                        Seluruh isi berkas di dalam arsip <strong>.zip</strong> akan diekstrak kembali ke dalam direktori <code>storage/app/public</code>.
                    </div>
                    <div class="form-group mb-2">
                        <label for="storage_zip" class="font-weight-bold text-dark">
                            Pilih Berkas Backup Storage (<code>.zip</code>) <span class="text-danger">*</span>
                        </label>
                        <input type="file"
                               id="storage_zip"
                               name="storage_zip"
                               class="form-control-file form-control"
                               accept=".zip,application/zip,application/x-zip-compressed"
                               required>
                        <small class="form-text text-muted mt-1">
                            Format yang didukung: <strong>.zip</strong> hasil backup storage. Maksimal ukuran berkas: 500 MB.
                        </small>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background-color: #2e6b34; border-color: #2e6b34;">
                        <i class="bi bi-upload mr-1 me-1"></i> Pulihkan Storage Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

