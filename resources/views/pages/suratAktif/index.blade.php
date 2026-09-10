@extends('layouts.dashboard.template')

@push('styles')
<style>
    #surataktif-table th.col-checkbox,
    #surataktif-table td.col-checkbox {
        width: 38px !important;
        min-width: 38px !important;
        max-width: 45px !important;
        padding: 8px 4px !important;
        text-align: center !important;
        white-space: nowrap !important;
    }
    #surataktif-table th.col-no,
    #surataktif-table td.col-no {
        width: 45px !important;
        min-width: 45px !important;
        max-width: 55px !important;
        padding-left: 6px !important;
        padding-right: 6px !important;
        text-align: center !important;
        white-space: nowrap !important;
    }

    /* Bulk Buttons: Never blurry / faded on hover or disabled */
    #btn-bulk-status {
        background-color: #f39c12 !important;
        border: none !important;
        color: #ffffff !important;
        opacity: 1 !important;
        filter: none !important;
    }
    #btn-bulk-status:hover,
    #btn-bulk-status:focus,
    #btn-bulk-status:active {
        background-color: #d68910 !important;
        border-color: #d68910 !important;
        color: #ffffff !important;
        opacity: 1 !important;
        filter: none !important;
        box-shadow: none !important;
    }
    #btn-bulk-status:disabled {
        background-color: #f39c12 !important;
        color: #ffffff !important;
        opacity: 1 !important;
        filter: none !important;
        cursor: not-allowed !important;
    }

    #btn-bulk-delete {
        background-color: #dc3545 !important;
        border: none !important;
        border-left: 1px solid rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
        opacity: 1 !important;
        filter: none !important;
    }
    #btn-bulk-delete:hover,
    #btn-bulk-delete:focus,
    #btn-bulk-delete:active {
        background-color: #bd2130 !important;
        border-color: #bd2130 !important;
        color: #ffffff !important;
        opacity: 1 !important;
        filter: none !important;
        box-shadow: none !important;
    }
    #btn-bulk-delete:disabled {
        background-color: #dc3545 !important;
        color: #ffffff !important;
        opacity: 1 !important;
        filter: none !important;
        cursor: not-allowed !important;
    }
    #btn-bulk-status .badge,
    #btn-bulk-delete .badge {
        background-color: #ffffff !important;
        opacity: 1 !important;
    }

    /* Status Filter Buttons */
    .btn-filter-status {
        background-color: #ffffff !important;
        color: #5a5c69 !important;
        font-weight: 600;
        cursor: pointer;
        opacity: 1 !important;
        filter: none !important;
        transition: all 0.2s ease-in-out;
    }
    .btn-filter-status:hover {
        background-color: #f1f3f9 !important;
        color: #2e384d !important;
        opacity: 1 !important;
        filter: none !important;
    }
    .btn-filter-status.active {
        color: #ffffff !important;
        opacity: 1 !important;
        filter: none !important;
    }
    .btn-filter-status.active i {
        color: #ffffff !important;
    }
    .btn-filter-status.active[data-status=""] {
        background-color: #34495e !important;
    }
    .btn-filter-status.active[data-status="pending"] {
        background-color: #f39c12 !important;
    }
    .btn-filter-status.active[data-status="diterima"] {
        background-color: #28a745 !important;
    }
    .btn-filter-status.active[data-status="ditolak"] {
        background-color: #dc3545 !important;
    }
</style>
@endpush

@section('content')
<div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; border-top: 5px solid #046B26 !important;">
    <div class="card-header bg-white border-bottom py-2 px-3">
        <div class="row align-items-center">
            <div class="col-lg-9 col-md-8 d-flex align-items-center flex-wrap" style="gap: 8px;">
                @can('surat_aktif_create')
                <div class="d-inline-flex shadow-sm overflow-hidden" style="border: 1px solid #e3e6f0; border-radius: 30px;">
                    <a href="{{ route('suratAktif.create') }}" class="btn btn-success btn-xs font-weight-bold px-3 py-1 border-0" style="background-color: #046B26; color: white; font-size: 10px; height: 32px; line-height: 24px; border-top-left-radius: 30px; border-bottom-left-radius: 30px;">
                        <i class="fa-solid fa-plus mr-1"></i> TAMBAH
                    </a>
                    <button type="button" class="btn btn-white btn-xs px-3 py-1 border-0 border-left rounded-0 text-primary" data-toggle="modal" data-target="#importModal" style="font-size: 10px; height: 32px; line-height: 24px;">
                        <i class="fa-solid fa-file-import mr-1"></i> IMPOR EXCEL
                    </button>
                    <a href="{{ route('suratAktif.export-template') }}" class="btn btn-white btn-xs px-3 py-1 border-0 border-left font-weight-bold" style="font-size: 10px; height: 32px; line-height: 24px; border-top-right-radius: 30px; border-bottom-right-radius: 30px; color: #28a745;">
                        <i class="fa-solid fa-file-download mr-1"></i> DOWNLOAD FORMAT
                    </a>
                </div>
                @endcan

                @if(auth()->user()->is_admin || auth()->user()->is_superadmin || auth()->user()->is_staffbaak || auth()->user()->can('surat_aktif_edit') || auth()->user()->can('surat_aktif_delete'))
                <div class="d-inline-flex shadow-sm overflow-hidden" style="border: 1px solid #e3e6f0; border-radius: 30px;">
                    <button type="button" class="btn btn-warning btn-xs font-weight-bold px-3 py-1 border-0 text-white d-inline-flex align-items-center" id="btn-bulk-status" data-toggle="modal" data-target="#modalBulkStatusAktif" disabled style="font-size: 10px; height: 32px; line-height: 24px; border-top-left-radius: 30px; border-bottom-left-radius: 30px;">
                        <i class="fa-solid fa-pen-to-square mr-1"></i> UPDATE STATUS
                        <span class="badge badge-light text-dark font-weight-bold ml-1 selected-count" style="font-size: 10px; padding: 2px 6px; border-radius: 10px;">0</span>
                    </button>
                    <button type="button" class="btn btn-danger btn-xs font-weight-bold px-3 py-1 border-0 border-left text-white d-inline-flex align-items-center" id="btn-bulk-delete" disabled style="font-size: 10px; height: 32px; line-height: 24px; border-top-right-radius: 30px; border-bottom-right-radius: 30px;">
                        <i class="fa-solid fa-trash-can mr-1"></i> HAPUS TERPILIH
                        <span class="badge badge-light text-danger font-weight-bold ml-1 selected-count" style="font-size: 10px; padding: 2px 6px; border-radius: 10px;">0</span>
                    </button>
                </div>
                @endif

                @if(auth()->user()->is_mahasiswa)
                    @php
                        $hasPendingSuratAktif = \App\Models\SuratAktif::where('users_id', auth()->id())->where('status', 'pending')->exists();
                    @endphp
                    <div class="d-inline-flex">
                        @if($hasPendingSuratAktif)
                            <button type="button" class="btn btn-warning btn-xs font-weight-bold px-3 py-1 shadow-sm border-0 text-white" disabled style="border-radius: 30px; font-size: 10px; height: 32px; line-height: 24px;" title="Pengajuan sedang diproses">
                                <i class="fa-solid fa-clock mr-1"></i> PENGAJUAN SEDANG DIPROSES
                            </button>
                        @else
                            <form action="{{ route('suratAktif.pengajuan') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-success btn-xs font-weight-bold px-3 py-1 shadow-sm border-0" style="background-color: #046B26; border-radius: 30px; font-size: 10px; height: 32px; line-height: 24px;">
                                    <i class="fa-solid fa-plus mr-1"></i> PENGAJUAN
                                </button>
                            </form>
                        @endif
                    </div>
                @endif

                <!-- Filter Status Pill Group -->
                <div class="d-inline-flex shadow-sm overflow-hidden" style="border: 1px solid #e3e6f0; border-radius: 30px;">
                    <button type="button" class="btn btn-white btn-xs px-3 py-1 border-0 btn-filter-status active" data-status="" style="font-size: 10px; height: 32px; line-height: 24px; border-top-left-radius: 30px; border-bottom-left-radius: 30px;" title="Tampilkan Semua Status">
                        <i class="fa-solid fa-list mr-1"></i> SEMUA
                    </button>
                    <button type="button" class="btn btn-white btn-xs px-3 py-1 border-0 border-left btn-filter-status" data-status="pending" style="font-size: 10px; height: 32px; line-height: 24px;" title="Filter Status Pending">
                        <i class="fa-solid fa-clock mr-1 text-warning"></i> PENDING
                    </button>
                    <button type="button" class="btn btn-white btn-xs px-3 py-1 border-0 border-left btn-filter-status" data-status="diterima" style="font-size: 10px; height: 32px; line-height: 24px;" title="Filter Status Diterima">
                        <i class="fa-solid fa-circle-check mr-1 text-success"></i> DITERIMA
                    </button>
                    <button type="button" class="btn btn-white btn-xs px-3 py-1 border-0 border-left btn-filter-status" data-status="ditolak" style="font-size: 10px; height: 32px; line-height: 24px; border-top-right-radius: 30px; border-bottom-right-radius: 30px;" title="Filter Status Ditolak">
                        <i class="fa-solid fa-circle-xmark mr-1 text-danger"></i> DITOLAK
                    </button>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 text-right">
                <h5 class="m-0 font-weight-bold text-dark" style="font-size: 1rem;">Data Surat Aktif</h5>
            </div>
        </div>
    </div>
      

    <div class="card-block table-border-style">
        <div class="table-responsive">
            {{ $dataTable->table([
                'class' => 'table table-striped table-bordered',
                'style' => 'width: 100%;',
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

<!-- Modal Bulk Update Status -->
<div class="modal fade" id="modalBulkStatusAktif" tabindex="-1" role="dialog" aria-labelledby="modalBulkStatusAktifLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-bulk-status" action="{{ route('suratAktif.bulkUpdateStatus') }}" method="POST">
            @csrf
            <div id="bulk-status-inputs"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="modalBulkStatusAktifLabel">
                        <i class="fa-solid fa-pen-to-square text-warning mr-1"></i> Update Status Surat Aktif Terpilih
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2">
                        <i class="fa-solid fa-info-circle mr-1"></i> Jumlah data terpilih: <strong><span class="selected-count">0</span> data</strong>
                    </div>
                    <div class="form-group">
                        <label for="bulk_status" class="font-weight-bold">Pilih Status Baru <span class="text-danger">*</span></label>
                        <select name="status" id="bulk_status" class="form-control" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="pending">Pending</option>
                            <option value="diterima">Diterima</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-check mr-1"></i> Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Form Bulk Delete (Hidden) -->
<form id="form-bulk-delete" action="{{ route('suratAktif.bulkDelete') }}" method="POST" style="display: none;">
    @csrf
    <div id="bulk-delete-inputs"></div>
</form>
@endsection

@push('scripts')
    <script>
        window.selectedStatusFilter = '';
    </script>
    @if(app()->environment('production'))
        {!! str_replace('http:', 'https:', $dataTable->scripts()) !!}
    @else
        {!! $dataTable->scripts() !!}
    @endif

    <script>
        $(document).ready(function() {
            var selectedIds = new Set();

            // Status filter button click handler
            $('.btn-filter-status').on('click', function(e) {
                e.preventDefault();
                var status = $(this).data('status');
                if (window.selectedStatusFilter === status) {
                    return;
                }
                window.selectedStatusFilter = status;

                $('.btn-filter-status').removeClass('active');
                $(this).addClass('active');

                // Reset seleksi checkbox saat filter berganti
                selectedIds.clear();
                updateBulkButtons();
                syncSelectAllCheckbox();

                // Reload data di tabel otomatis sesuai status yang dipilih
                if (window.LaravelDataTables && window.LaravelDataTables['surataktif-table']) {
                    window.LaravelDataTables['surataktif-table'].draw();
                } else {
                    $('#surataktif-table').DataTable().draw();
                }
            });

            function updateBulkButtons() {
                var count = selectedIds.size;
                $('.selected-count').text(count);
                if (count > 0) {
                    $('#btn-bulk-status').prop('disabled', false);
                    $('#btn-bulk-delete').prop('disabled', false);
                } else {
                    $('#btn-bulk-status').prop('disabled', true);
                    $('#btn-bulk-delete').prop('disabled', true);
                }

                // Sync hidden inputs
                var deleteInputs = '';
                var statusInputs = '';
                selectedIds.forEach(function(id) {
                    deleteInputs += '<input type="hidden" name="ids[]" value="' + id + '">';
                    statusInputs += '<input type="hidden" name="ids[]" value="' + id + '">';
                });
                $('#bulk-delete-inputs').html(deleteInputs);
                $('#bulk-status-inputs').html(statusInputs);
            }

            function syncSelectAllCheckbox() {
                var totalRows = $('.row-checkbox').length;
                var checkedRows = $('.row-checkbox:checked').length;
                if (totalRows > 0 && totalRows === checkedRows) {
                    $('#select-all').prop('checked', true);
                } else {
                    $('#select-all').prop('checked', false);
                }
            }

            // Sync checkboxes when DataTable renders or redraws (pagination, search, sort)
            $(document).on('draw.dt', function() {
                $('.row-checkbox').each(function() {
                    var val = $(this).val();
                    if (selectedIds.has(val)) {
                        $(this).prop('checked', true);
                    } else {
                        $(this).prop('checked', false);
                    }
                });
                syncSelectAllCheckbox();
                updateBulkButtons();
            });

            // Select All Header Checkbox
            $(document).on('change', '#select-all', function() {
                var isChecked = $(this).is(':checked');
                $('.row-checkbox').each(function() {
                    $(this).prop('checked', isChecked);
                    var val = $(this).val();
                    if (isChecked) {
                        selectedIds.add(val);
                    } else {
                        selectedIds.delete(val);
                    }
                });
                updateBulkButtons();
            });

            // Individual Row Checkbox
            $(document).on('change', '.row-checkbox', function() {
                var val = $(this).val();
                if ($(this).is(':checked')) {
                    selectedIds.add(val);
                } else {
                    selectedIds.delete(val);
                }
                syncSelectAllCheckbox();
                updateBulkButtons();
            });

            // Bulk Delete Click Handler
            $('#btn-bulk-delete').on('click', function(e) {
                e.preventDefault();
                var count = selectedIds.size;
                if (count === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Silakan pilih data yang ingin dihapus terlebih dahulu.'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi Hapus Massal',
                    text: 'Apakah Anda yakin ingin menghapus ' + count + ' data surat aktif terpilih?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus Semua!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#form-bulk-delete').submit();
                    }
                });
            });

            // Bulk Status Button Click Handler
            $('#btn-bulk-status').on('click', function(e) {
                var count = selectedIds.size;
                if (count === 0) {
                    e.preventDefault();
                    e.stopPropagation();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Silakan pilih data yang ingin diubah statusnya terlebih dahulu.'
                    });
                    return false;
                }
            });
        });
    </script>
@endpush
