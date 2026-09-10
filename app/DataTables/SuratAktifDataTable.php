<?php

namespace App\DataTables;

use App\Models\SuratAktif;
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

class SuratAktifDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<SuratAktif> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('DT_RowIndex', '')
            ->addColumn('checkbox', function ($item) {
                return '<input type="checkbox" name="ids[]" class="row-checkbox" value="' . $item->id . '" style="cursor: pointer; transform: scale(1.2);">';
            })
            ->addColumn('users_id', function ($item) {
                $name = $item->users ? $item->users->name : '-';
                if (Auth::user()->is_admin || Auth::user()->is_superadmin || Auth::user()->is_staffbaak) {
                    return '<a href="' . route('suratAktif.edit', $item) . '" class="text-success">' . $name . '</a>';
                }
                return $name;
            })
            ->filterColumn('users_id', function ($query, $keyword) {
                $query->whereHas('users', function ($q) use ($keyword) {
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
            ->addColumn('status', function ($item) {
                return $item->status == 'pending' ? '<span class="badge badge-warning text-white px-3 py-2">Pending</span>' : ($item->status == 'diterima' ? '<span class="badge badge-success text-white px-3 py-2">Diterima</span>' : ($item->status == 'ditolak' ? '<span class="badge badge-danger text-white px-3 py-2">Ditolak</span>' : '-'));
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('status', 'like', "%{$keyword}%");
            })
            ->addColumn('created_at', function ($item) {
                return $item->created_at ? $item->created_at->locale('id')->isoFormat('dddd, DD/MM/YYYY - HH:mm') : '-';
            })
            ->filterColumn('created_at', function ($query, $keyword) {
                $query->where('created_at', 'like', "%{$keyword}%");
            })
            ->addColumn('action', function ($item) {
                $actions = '';

                // Tombol Cetak & aksi admin: gated by permission
                if (Gate::check('surat_aktif_edit') || Gate::check('surat_aktif_delete')) {
                    // Print/Cetak hanya untuk yang punya akses edit (staff/admin)
                    $actions .= '
                        <a href="' . route('suratAktif.show', $item) . '" class="btn btn-sm btn-success text-white py-2 px-2 px-md-3 mb-1 mr-1 mr-md-2 rounded" title="Cetak Surat" target="_blank">
                            <i class="fa-solid fa-print"></i><span class="d-none d-md-inline"> Cetak Surat</span>
                        </a>
                    ';
                }
                if (Gate::check('surat_aktif_edit')) {
                    $actions .= '
                        <a href="' . route('suratAktif.edit', $item) . '" class="btn btn-sm btn-warning text-white py-2 px-2 px-md-3 mb-1 mr-1 mr-md-2 rounded" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    ';
                }
                if (Gate::check('surat_aktif_delete')) {
                    $actions .= '
                        <form action="' . route('suratAktif.destroy', $item) . '" method="POST" class="d-inline">
                            ' . csrf_field() . '
                            ' . method_field('delete') . '
                            <button type="submit" class="btn btn-danger btn-sm py-2 px-2 px-md-3 mb-1 mr-1 mr-md-2 rounded" title="Hapus" onclick="return confirmDelete(event, this)"><i class="fa-solid fa-trash-can"></i></button>
                        </form>
                    ';
                }

                // Tombol aksi untuk mahasiswa
                if (Auth::user()->is_mahasiswa) {
                    if ($item->status == 'pending') {
                        $actions .= '<span class="text-info small font-italic"><i class="fa-solid fa-clock mr-1"></i>Estimasi surat diproses 3 hari kerja</span>';
                    } else {
                        $actions .= '
                            <a href="' . route('suratAktif.validasi', $item) . '" class="btn btn-sm btn-info text-white px-2 px-md-3 mr-1 mr-md-2 rounded" title="Lihat Detail">
                                <i class="fa-solid fa-eye"></i><span class="d-none d-md-inline"> Detail</span>
                            </a>
                        ';
                        
                        if ($item->status == 'diterima') {
                            $actions .= '
                                <a href="' . route('suratAktif.show', $item) . '" class="btn btn-sm btn-success text-white px-2 px-md-3 mr-1 mr-md-2 rounded" title="Cetak Surat" target="_blank">
                                    <i class="fa-solid fa-print"></i><span class="d-none d-md-inline"> Cetak Surat</span>
                                </a>
                            ';
                        }
                    }
                }

                if (empty(trim($actions))) {
                    $actions = '<span class="text-muted small">-</span>';
                }

                return $actions;
            })
            ->setRowId('DT_RowIndex')
            ->rawColumns(['action', 'users_id', 'program_studi_id', 'status', 'created_at', 'checkbox']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<SuratAktif>
     */
    public function query(SuratAktif $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['users', 'programStudi'])
            ->latest('id'); // eager load biar cepat & urutkan terbaru (DESC)

        if ($status = request('status_filter')) {
            if (in_array($status, ['pending', 'diterima', 'ditolak'])) {
                $query->where('status', $status);
            }
        }

        $user = Auth::user();

        // Admin, Superadmin & siapa saja yang punya izin view → lihat semua
        if ($user->is_admin || $user->is_superadmin || $user->is_staffbaak
            || Gate::allows('surat_aktif_view')) {
            return $query;
        }

        if ($user->is_mahasiswa) {
            // Mahasiswa hanya lihat miliknya
            return $query->where('users_id', Auth::id());
        }

        // Role lain tanpa permission: kosong
        return $query->whereRaw('1=0');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $user = Auth::user();
        $canBulk = $user && ($user->is_admin || $user->is_superadmin || $user->is_staffbaak || Gate::check('surat_aktif_edit') || Gate::check('surat_aktif_delete'));

        return $this->builder()
            ->setTableId('surataktif-table')
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
        if ($user && ($user->is_admin || $user->is_superadmin || $user->is_staffbaak || Gate::check('surat_aktif_edit') || Gate::check('surat_aktif_delete'))) {
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

        $columns[] = Column::make('users_id')
            ->title('NAMA MAHASISWA')
            ->width('25%')
            ->addClass('text-start');

        $columns[] = Column::make('program_studi_id')
            ->title('PROGRAM STUDI')
            ->width('20%')
            ->addClass('text-start');

        $columns[] = Column::make('created_at')
            ->title('TANGGAL SUBMIT')
            ->width('18%')
            ->addClass('text-center');

        $columns[] = Column::make('status')
            ->title('STATUS')
            ->width('10%')
            ->addClass('text-center');

        $columns[] = Column::computed('action')
            ->title('AKSI')
            ->exportable(false)
            ->printable(false)
            ->width('15%')
            ->addClass('text-center');

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'SuratAktif_' . date('YmdHis');
    }
}
