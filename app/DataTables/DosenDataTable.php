<?php

namespace App\DataTables;

use App\Models\Dosen;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class DosenDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Dosen> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
             ->addIndexColumn()
            ->addColumn('DT_RowIndex', '')
            ->addColumn('users_name', function ($item) {
                return $item->user ? $item->user->name : '-';
            })
            ->addColumn('programStudi.program_studi', function ($item) {
                return $item->programStudi ? $item->programStudi->program_studi : '-';
            })
            ->addColumn('action', function ($item) {
                $btn = '<div class="d-flex justify-content-center align-items-center" style="gap: 5px;">';
                if (Gate::check('dosen_view')) {
                    $btn .= '<a href="' . route('dosen.show', $item->id) . '" class="btn btn-sm btn-info text-white rounded shadow-sm d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;" title="Detail"><i class="fa-solid fa-eye" style="font-size: 11px;"></i></a>';
                }
                if (Gate::check('dosen_edit')) {
                    $btn .= '<a href="' . route('dosen.edit', $item->id) . '" class="btn btn-sm btn-warning text-white rounded shadow-sm d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;" title="Edit"><i class="fa-solid fa-pen-to-square" style="font-size: 11px;"></i></a>';
                }
                if (Gate::check('dosen_delete')) {
                    $btn .= '<form action="' . route('dosen.destroy', $item->id) . '" method="POST" class="m-0">' . csrf_field() . method_field('delete') . '<button type="submit" class="btn btn-danger btn-sm rounded shadow-sm d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;" title="Hapus" onclick="return confirm(\'Hapus data ini?\')"><i class="fa-solid fa-trash-can" style="font-size: 11px;"></i></button></form>';
                }
                $btn .= '</div>';
                
                if (Gate::check('dosen_view') || Gate::check('dosen_edit') || Gate::check('dosen_delete')) {
                    return $btn;
                }
                return '<span class="text-muted small">-</span>';
            })
            ->rawColumns(['action', 'programStudi.program_studi'])
            ->setRowId('id')
            ->filterColumn('programStudi.program_studi', function ($query, $keyword) {
                $query->whereHas('programStudi', function ($q) use ($keyword) {
                    $q->where('program_studi', 'like', '%' . $keyword . '%');
                });
            });
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Dosen>
     */
    public function query(Dosen $model): QueryBuilder
    {
        return $model->newQuery()->with('programStudi');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('dosen-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(1)
                    ->selectStyleSingle()
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
        return [
            Column::make('DT_RowIndex')
                ->title('NO')
                ->width('5%'),
            Column::make('nama_dosen')
                ->title('Nama Dosen'),
            Column::make('programStudi.program_studi')
                ->title('Program Studi'),
            Column::make('email')
                ->title('Email'),
            Column::make('nidn')
                ->addClass('text-center')
                ->title('NIDN'),
            Column::make('nup')
                ->addClass('text-center')
                ->title('NUP'),
            Column::make('nuptk')
                ->addClass('text-center')
                ->title('NUPTK'),
            Column::computed('action')
                  ->title('Aksi')
                  ->exportable(false)
                  ->printable(false)
                  ->width('15%')
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Dosen_' . date('YmdHis');
    }
}
