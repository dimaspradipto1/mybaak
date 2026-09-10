@extends('layouts.dashboard.template')

@section('content')
<div class="card">
    <div class="card-header">
        @can('surat_aktif_create')
            <a href="{{ route('suratAktif.create') }}" class="btn btn-primary rounded btn-sm"><i class="fa-solid fa-plus"></i> Tambah</a>
            <button type="button" class="btn btn-info rounded btn-sm" data-toggle="modal" data-target="#importModal">
                <i class="fa-solid fa-file-excel"></i> Import
            </button>
        @endcan

        @if(auth()->user()->is_mahasiswa)
            @php
                $hasPendingSuratAktif = \App\Models\SuratAktif::where('users_id', auth()->id())->where('status', 'pending')->exists();
            @endphp
            @if($hasPendingSuratAktif)
                <button type="button" class="btn btn-warning rounded btn-sm text-white" disabled title="Pengajuan Anda sedang diproses">
                    <i class="fa-solid fa-clock"></i> Pengajuan Sedang Diproses
                </button>
            @else
                <!-- Tombol hanya untuk mahasiswa: Pengajuan -->
                <form action="{{ route('suratAktif.pengajuan') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-success rounded btn-sm">
                        <i class="fa-solid fa-plus"></i> Pengajuan
                    </button>
                </form>
            @endif
        @endif

        <div class="card-header-right">
            <ul class="list-unstyled card-option">
                <li><i class="fa fa fa-wrench open-card-option"></i></li>
                <li><i class="fa fa-window-maximize full-card"></i></li>
                <li><i class="fa fa-minus minimize-card"></i></li>
                <li><i class="fa fa-refresh reload-card"></i></li>
                <li><i class="fa fa-trash close-card"></i></li>
            </ul>
        </div>
    </div>
      

    <div class="card-block table-border-style">
        <div class="table-responsive">
            {{ $dataTable->table([
                'class' => 'table table-striped table-bordered',
                'style' => 'width: 100%; overflow-x: auto;',
            ]) }}
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('suratAktif.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Data Surat Aktif</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Download Template</label><br>
                        <a href="{{ route('suratAktif.export-template') }}" class="btn btn-link p-0 text-primary">
                            <i class="fa-solid fa-download"></i> Klik untuk download template Excel
                        </a>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Pilih File Excel</label>
                        <input type="file" name="file" class="form-control" required accept=".xlsx, .xls">
                        <small class="text-muted">Format kolom: nama_mahasiswa, program_studi, no_surat, tempat_lahir, tanggal_lahir, npm, semester, tahun_akademik.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Upload & Import</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    @if(app()->environment('production'))
        {!! str_replace('http:', 'https:', $dataTable->scripts()) !!}
    @else
        {!! $dataTable->scripts() !!}
    @endif
@endpush
