<?php

namespace App\DataTables;

use App\Models\SuratAkademik;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class SuratAkademikDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<SuratAkademik> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('DT_RowIndex', '')
            ->addColumn('checkbox', function ($item) {
                return '<input type="checkbox" name="ids[]" class="row-checkbox" value="' . $item->id . '" style="cursor: pointer; transform: scale(1.2);">';
            })
            ->addColumn('no_surat', function ($item) {
                $noUrut = sprintf('%03d', (int) ($item->no_surat ?? $item->id));
                $tahun = $item->created_at ? $item->created_at->format('Y') : date('Y');
                return '<div class="text-nowrap"><span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 11px;">' . $noUrut . '</span><br><small class="text-muted" style="font-size: 10px;">' . $noUrut . '/UIS.B1/LA/.../' . $tahun . '</small></div>';
            })
            ->filterColumn('no_surat', function ($query, $keyword) {
                $query->where('no_surat', 'like', "%{$keyword}%");
            })
            ->addColumn('users_id', function ($item) {
                return $item->user ? $item->user->name : '-';
            })
            ->filterColumn('users_id', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('program_studi_id', function ($item) {
                return $item->programStudi ? $item->programStudi->program_studi : '-';
            })
            ->filterColumn('program_studi_id', function ($query, $keyword) {
                $query->whereHas('programStudi', function ($q) use ($keyword) {
                    $q->where('program_studi', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('file', function ($item) {
                if ($item->file) {
                    return '<a href="' . $item->file . '" target="_blank" class="btn btn-sm btn-success text-white px-2 py-1 rounded shadow-sm text-nowrap" title="Buka Berkas di Google Drive" style="font-size: 11px;">
                                <i class="fa-brands fa-google-drive mr-1"></i> Buka Berkas
                            </a>';
                }
                return '<span class="badge badge-secondary px-2 py-1 text-nowrap" style="font-size: 10px; opacity: 0.85;"><i class="fa-solid fa-file-circle-xmark mr-1"></i> Belum Ada</span>';
            })
            ->addColumn('created_at', function ($item) {
                return $item->created_at ? $item->created_at->locale('id')->isoFormat('dddd, DD/MM/YYYY - HH:mm') : '-';
            })
            ->filterColumn('created_at', function ($query, $keyword) {
                $query->where('created_at', 'like', "%{$keyword}%");
            })
            ->addColumn('status', function ($item) {
                return $item->status == 'pending' ? '<span class="badge badge-warning text-white px-3 py-2">Pending</span>' : ($item->status == 'diterima' ? '<span class="badge badge-success text-white px-3 py-2">Diterima</span>' : ($item->status == 'ditolak' ? '<span class="badge badge-danger text-white px-3 py-2">Ditolak</span>' : '-'));
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('status', 'like', "%{$keyword}%");
            })
            ->addColumn('action', function ($item) {
                $actions = '';

                // Tombol Arsip Google Drive (Upload / Generate)
                if (Gate::check('surat_akademik_edit') || Gate::check('surat_akademik_delete')) {
                    $actions .= '
                        <button type="button" class="btn btn-sm btn-info text-white py-2 px-2 px-md-3 mb-1 mr-1 mr-md-2 rounded btn-arsip-modal" 
                            data-id="' . $item->id . '" 
                            data-type="Surat Layanan Akademik"
                            data-nomor="' . sprintf('%03d', (int) ($item->no_surat ?? $item->id)) . '" 
                            data-name="' . htmlspecialchars($item->user->name ?? '-', ENT_QUOTES) . '" 
                            data-file="' . ($item->file ?? '') . '"
                            data-action-upload="' . route('suratAkademik.uploadArsip', $item->id) . '"
                            data-action-generate="' . route('suratAkademik.generateArsip', $item->id) . '"
                            title="Kelola Berkas Arsip Google Drive">
                            <i class="fa-brands fa-google-drive"></i><span class="d-none d-md-inline"> Arsip</span>
                        </button>
                    ';
                }

                if (Gate::check('surat_akademik_edit') || Gate::check('surat_akademik_delete')) {
                    $actions .= '<a href="' . route('suratAkademik.show', $item->id) . '" class="btn btn-sm btn-success text-white py-2 px-2 px-md-3 mb-1 mr-1 mr-md-2 rounded" title="Cetak Surat" target="_blank"><i class="fa-solid fa-print"></i><span class="d-none d-md-inline"> Cetak</span></a>';
                }
                if (Gate::check('surat_akademik_edit')) {
                    $actions .= '<a href="' . route('suratAkademik.edit', $item->id) . '" class="btn btn-sm btn-warning text-white py-2 px-2 px-md-3 mb-1 mr-1 mr-md-2 rounded" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>';
                }
                if (Gate::check('surat_akademik_delete')) {
                    $actions .= '
                        <form action="' . route('suratAkademik.destroy', $item->id) . '" method="POST" class="d-inline">
                            ' . csrf_field() . '
                            ' . method_field('delete') . '
                            <button type="submit" class="btn btn-danger btn-sm py-2 px-2 px-md-3 mb-1 mr-1 mr-md-2 rounded" title="Hapus" onclick="return confirmDelete(event, this)"><i class="fa-solid fa-trash-can"></i></button>
                        </form>
                    ';
                }

                if (Auth::user()->is_mahasiswa) {
                    if ($item->file) {
                        $actions .= '
                            <a href="' . $item->file . '" class="btn btn-sm btn-outline-success px-2 px-md-3 mr-1 mr-md-2 rounded" title="Buka Berkas di Google Drive" target="_blank">
                                <i class="fa-brands fa-google-drive mr-1"></i><span class="d-none d-md-inline"> Berkas</span>
                            </a>
                        ';
                    }
                    $actions .= '<a href="' . route('suratAkademik.show', $item->id) . '" class="btn btn-sm btn-success text-white py-2 px-2 px-md-3 mb-1 mr-1 mr-md-2 rounded" title="Cetak Surat" target="_blank"><i class="fa-solid fa-print"></i><span class="d-none d-md-inline"> Cetak</span></a>';
                }

                if (empty(trim($actions))) {
                    $actions = '<span class="text-muted small">-</span>';
                }
                return $actions;
            })
            ->setRowId('DT_RowIndex')
            ->rawColumns(['action', 'users_id', 'program_studi_id', 'created_at', 'status', 'checkbox', 'no_surat', 'file']);
    }


    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<SuratAkademik>
     */
    public function query(SuratAkademik $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['user', 'programStudi'])->latest('id');

        if ($status = request('status_filter')) {
            if (in_array($status, ['pending', 'diterima', 'ditolak'])) {
                $query->where('status', $status);
            }
        }

        $user = Auth::user();

        // Admin, Superadmin & siapa saja yang punya izin view → lihat semua
        if ($user->is_admin || $user->is_superadmin || $user->is_staffbaak
            || Gate::allows('surat_akademik_view')) {
            return $query;
        }

        if ($user->is_mahasiswa) {
            // Mahasiswa hanya lihat miliknya
            return $query->where('users_id', Auth::id());
        }

        return $query->whereRaw('1=0');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $user = Auth::user();
        $canBulk = $user && ($user->is_admin || $user->is_superadmin || $user->is_staffbaak || Gate::check('surat_akademik_edit') || Gate::check('surat_akademik_delete'));

        return $this->builder()
            ->setTableId('suratakademik-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', 'data.status_filter = window.selectedStatusFilter || "";')
            ->orderBy($canBulk ? 4 : 3, 'desc')
            ->selectStyleSingle()
            ->parameters([
                'autoWidth' => false,
            ])
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $columns = [];
        $user = Auth::user();
        if ($user && ($user->is_admin || $user->is_superadmin || $user->is_staffbaak || Gate::check('surat_akademik_edit') || Gate::check('surat_akademik_delete'))) {
            $columns[] = Column::computed('checkbox')
                ->title('<input type="checkbox" id="select-all" title="Pilih Semua" style="cursor: pointer; transform: scale(1.1);">')
                ->exportable(false)
                ->printable(false)
                ->width('1%')
                ->addClass('text-center align-middle col-checkbox')
                ->orderable(false)
                ->searchable(false);
        }

        $columns[] = Column::make('DT_RowIndex')
            ->title('NO')
            ->width('1%')
            ->addClass('text-center align-middle col-no')
            ->orderable(false)
            ->searchable(false);

        $columns[] = Column::make('no_surat')
            ->title('NO SURAT')
            ->width('12%')
            ->addClass('text-center align-middle');

        $columns[] = Column::make('users_id')
            ->title('NAMA MAHASISWA')
            ->width('20%')
            ->addClass('text-start align-middle');

        $columns[] = Column::make('program_studi_id')
            ->title('PROGRAM STUDI')
            ->width('16%')
            ->addClass('text-start align-middle');

        $columns[] = Column::make('file')
            ->title('BERKAS ARSIP')
            ->width('12%')
            ->addClass('text-center align-middle');

        $columns[] = Column::make('created_at')
            ->title('TANGGAL SUBMIT')
            ->width('14%')
            ->addClass('text-center align-middle');

        $columns[] = Column::make('status')
            ->title('STATUS')
            ->width('10%')
            ->addClass('text-center align-middle');

        $columns[] = Column::computed('action')
            ->title('AKSI')
            ->exportable(false)
            ->printable(false)
            ->width('15%')
            ->addClass('text-center align-middle');

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'SuratAkademik_' . date('YmdHis');
    }
}
