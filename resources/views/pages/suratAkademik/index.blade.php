@extends('layouts.dashboard.template')

@push('styles')
<style>
    #suratakademik-table th.col-checkbox,
    #suratakademik-table td.col-checkbox {
        width: 38px !important;
        min-width: 38px !important;
        max-width: 45px !important;
        padding: 8px 4px !important;
        text-align: center !important;
        white-space: nowrap !important;
    }
    #suratakademik-table th.col-no,
    #suratakademik-table td.col-no {
        width: 45px !important;
        min-width: 45px !important;
        max-width: 55px !important;
        padding-left: 6px !important;
        padding-right: 6px !important;
        text-align: center !important;
        white-space: nowrap !important;
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
            @can('surat_akademik_create')
                <a href="{{ route('suratAkademik.create') }}" class="btn btn-primary rounded btn-sm d-inline-flex align-items-center text-nowrap" style="white-space: nowrap;">
                    <i class="fa-solid fa-plus mr-1"></i> Tambah
                </a>
                <button type="button" class="btn btn-info rounded btn-sm text-white d-inline-flex align-items-center text-nowrap" data-toggle="modal" data-target="#importModalAkademik" style="white-space: nowrap;">
                    <i class="fa-solid fa-file-excel mr-1"></i> Import
                </button>
            @endcan

            @if(auth()->user()->is_admin || auth()->user()->is_superadmin || auth()->user()->is_staffbaak || auth()->user()->can('surat_akademik_edit') || auth()->user()->can('surat_akademik_delete'))
                <button type="button" class="btn btn-warning rounded btn-sm text-white d-inline-flex align-items-center text-nowrap" id="btn-bulk-status" data-toggle="modal" data-target="#modalBulkStatusAkademik" disabled style="white-space: nowrap;">
                    <i class="fa-solid fa-pen-to-square mr-1"></i> Update Status
                    <span class="badge badge-light text-dark font-weight-bold ml-1 selected-count" style="font-size: 11px; padding: 2px 7px; border-radius: 10px;">0</span>
                </button>
                <button type="button" class="btn btn-danger rounded btn-sm text-white d-inline-flex align-items-center text-nowrap" id="btn-bulk-delete" disabled style="white-space: nowrap;">
                    <i class="fa-solid fa-trash-can mr-1"></i> Hapus Terpilih
                    <span class="badge badge-light text-danger font-weight-bold ml-1 selected-count" style="font-size: 11px; padding: 2px 7px; border-radius: 10px;">0</span>
                </button>
            @endif
        </div>

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
                    'style' => 'width:100%; overflow-x: auto',
                ]) }}
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModalAkademik" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('suratAkademik.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Data Surat Akademik</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Download Template</label><br>
                        <a href="{{ route('suratAkademik.export-template') }}" class="btn btn-link p-0 text-primary">
                            <i class="fa-solid fa-download"></i> Klik untuk download template Excel
                        </a>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Pilih File Excel</label>
                        <input type="file" name="file" class="form-control" required accept=".xlsx, .xls">
                        <small class="text-muted">Format kolom: nama_mahasiswa, program_studi, npm, permohonan, alamat, no_wa, semester, status_cuti, alasan_cuti.</small>
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
<div class="modal fade" id="modalBulkStatusAkademik" tabindex="-1" role="dialog" aria-labelledby="modalBulkStatusAkademikLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-bulk-status" action="{{ route('suratAkademik.bulkUpdateStatus') }}" method="POST">
            @csrf
            <div id="bulk-status-inputs"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="modalBulkStatusAkademikLabel">
                        <i class="fa-solid fa-pen-to-square text-warning mr-1"></i> Update Status Surat Akademik Terpilih
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
                        <label for="bulk_status_akademik" class="font-weight-bold">Pilih Status Baru <span class="text-danger">*</span></label>
                        <select name="status" id="bulk_status_akademik" class="form-control" required>
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
<form id="form-bulk-delete" action="{{ route('suratAkademik.bulkDelete') }}" method="POST" style="display: none;">
    @csrf
    <div id="bulk-delete-inputs"></div>
</form>
@endsection

@push('scripts')
    @if(app()->environment('production'))
        {!! str_replace('http:', 'https:', $dataTable->scripts()) !!}
    @else
        {!! $dataTable->scripts() !!}
    @endif

    <script>
        $(document).ready(function() {
            var selectedIds = new Set();

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
                    text: 'Apakah Anda yakin ingin menghapus ' + count + ' data surat akademik terpilih?',
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