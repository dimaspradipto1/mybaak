@extends('layouts.dashboard.template')

@section('content')
<div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden; border-top: 5px solid #046B26 !important;">
    <div class="card-header bg-white border-bottom py-2 px-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                @can('program_studi_create')
                <div class="d-inline-flex shadow-sm overflow-hidden" style="border: 1px solid #e3e6f0; border-radius: 30px;">
                    <a href="{{ route('programStudi.create') }}" class="btn btn-success btn-xs font-weight-bold px-3 py-1 border-0" style="background-color: #046B26; color: white; font-size: 10px; height: 32px; line-height: 24px; border-radius: 30px;">
                        <i class="fa-solid fa-plus mr-1"></i> TAMBAH
                    </a>
                </div>
                @endcan
            </div>
            <div class="col-md-4 text-right">
                <h5 class="m-0 font-weight-bold text-dark" style="font-size: 1rem;">Data Program Studi</h5>
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
        #programstudi-table thead th {
            background-color: #f8f9fc;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            color: #5a5c69;
            border-bottom: 2px solid #e3e6f0;
            padding: 15px 10px;
            vertical-align: middle;
        }
        #programstudi-table tbody td {
            vertical-align: middle;
            padding: 12px 10px;
            color: #5a5c69;
            font-size: 13px;
        }
    </style>
@endpush

@push('scripts')
    @if(app()->environment('production'))
        {!! str_replace('http:', 'https:', $dataTable->scripts()) !!}
    @else
        {!! $dataTable->scripts() !!}
    @endif
@endpush

