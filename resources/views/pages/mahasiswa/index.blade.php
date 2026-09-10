@extends('layouts.dashboard.template')

@section('content')
    @php
        use App\Models\Mahasiswa;
    @endphp


    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; border-top: 5px solid #046B26 !important;">
        <div class="card-header bg-white border-bottom py-2 px-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-inline-flex shadow-sm overflow-hidden" style="border: 1px solid #e3e6f0; border-radius: 30px;">
                        @can('mahasiswa_create')
                        <a href="{{ route('mahasiswa.create') }}" class="btn btn-success btn-xs font-weight-bold px-3 py-1 border-0" style="background-color: #046B26; color: white; font-size: 10px; height: 32px; line-height: 24px; border-top-left-radius: 30px; border-bottom-left-radius: 30px;">
                            <i class="fa-solid fa-plus mr-1"></i> TAMBAH
                        </a>
                        <button type="button" class="btn btn-white btn-xs px-3 py-1 border-0 border-left rounded-0 text-primary" data-toggle="modal" data-target="#importMahasiswaModal" style="font-size: 10px; height: 32px; line-height: 24px;">
                            <i class="fa-solid fa-file-import mr-1"></i> IMPOR EXCEL
                        </button>
                        <a href="{{ route('mahasiswa.export-template') }}" class="btn btn-white btn-xs px-3 py-1 border-0 border-left font-weight-bold" style="font-size: 10px; height: 32px; line-height: 24px; border-top-right-radius: 30px; border-bottom-right-radius: 30px; color: #28a745;">
                            <i class="fa-solid fa-file-download mr-1"></i> DOWNLOAD FORMAT
                        </a>
                        @endcan
                    </div>

                    @if (auth()->user()->is_mahasiswa)
                        @php
                            $hasPendingSuratAktif = \App\Models\SuratAktif::where('users_id', auth()->id())->where('status', 'pending')->exists();
                            $hasPendingSuratAkademik = \App\Models\SuratAkademik::where('users_id', auth()->id())->where('status', 'pending')->exists();
                        @endphp
                        <div class="d-inline-flex ml-2">
                            @if($hasPendingSuratAktif)
                                <button type="button" class="btn btn-warning btn-xs font-weight-bold px-3 py-1 shadow-sm border-0 mr-1 text-white" disabled style="border-radius: 20px; font-size: 10px; height: 32px;" title="Pengajuan sedang diproses">
                                    <i class="fa-solid fa-clock mr-1"></i> SURAT AKTIF SEDANG DIPROSES
                                </button>
                            @else
                                <form action="{{ route('suratAktif.pengajuan') }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-xs font-weight-bold px-3 py-1 shadow-sm border-0 mr-1" style="background-color: #046B26; border-radius: 20px; font-size: 10px; height: 32px;">
                                        <i class="fa-solid fa-plus mr-1"></i> PENGAJUAN SURAT AKTIF
                                    </button>
                                </form>
                            @endif

                            @if($hasPendingSuratAkademik)
                                <button type="button" class="btn btn-warning btn-xs font-weight-bold px-3 py-1 shadow-sm border-0 text-white" disabled style="border-radius: 20px; font-size: 10px; height: 32px;" title="Pengajuan sedang diproses">
                                    <i class="fa-solid fa-clock mr-1"></i> SURAT AKADEMIK SEDANG DIPROSES
                                </button>
                            @else
                                <a href="{{ route('suratAkademik.create') }}" class="btn btn-secondary btn-xs font-weight-bold px-3 py-1 shadow-sm border-0" style="border-radius: 20px; font-size: 10px; height: 32px; line-height: 24px;">
                                    <i class="fa-solid fa-plus mr-1"></i> PENGAJUAN SURAT AKADEMIK
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="col-md-4 text-right">
                    <h5 class="m-0 font-weight-bold text-dark" style="font-size: 1rem;">Data Mahasiswa</h5>
                </div>
            </div>
        </div>




        <!-- Import Modal -->
        <div class="modal fade" id="importMahasiswaModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="importModalLabel">Impor Data Mahasiswa</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('mahasiswa.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="alert alert-info border-0 shadow-none mb-4">
                                <i class="fa-solid fa-circle-info mr-2"></i> 
                                Sistem akan otomatis menghubungkan data mahasiswa dengan akun pengguna berdasarkan <strong>Nama Pengguna</strong> yang ada di sistem.
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Pilih Berkas Excel (.xlsx / .csv)</label>
                                <div class="custom-file">
                                    <input type="file" name="file" class="custom-file-input" id="importFile" required>
                                    <label class="custom-file-label" for="importFile">Pilih file...</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-secondary btn-round btn-sm px-4" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success btn-round btn-sm px-4 shadow-sm">
                                <i class="fa-solid fa-cloud-upload mr-1"></i> Mulai Impor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                {{ $dataTable->table([
                    'class' => 'table table-striped table-bordered',
                    'style' => 'width:100%; overflow-x: auto',
                ]) }}
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .custom-file-label::after {
            content: "Browse" !important;
        }
        .custom-file.drag-over .custom-file-label {
            border: 2px dashed #046B26 !important;
            background-color: rgba(4, 107, 38, 0.05) !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Update custom file input label
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            // Drag and Drop Logic
            let dropZone = $('.custom-file');

            dropZone.on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('drag-over');
            });

            dropZone.on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
            });

            dropZone.on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');

                let files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    let fileInput = $(this).find('input[type="file"]')[0];
                    fileInput.files = files;
                    
                    // Trigger change to update label
                    $(fileInput).trigger('change');
                }
            });
        });
    </script>
    @if (app()->environment('production'))
        {!! str_replace('http:', 'https:', $dataTable->scripts()) !!}
    @else
        {!! $dataTable->scripts() !!}
    @endif
@endpush
