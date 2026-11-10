@extends('layouts.dashboard.template')

@section('title', 'Dashboard')

@section('content')


    {{-- Section for Special Staff/Archive Manager (Non Admin, Non Mahasiswa) --}}
    @php
        $isMahasiswa =
            (Auth::user()->role && Auth::user()->role->nama_role == 'MAHASISWA') ||
            (isset(Auth::user()->is_mahasiswa) && Auth::user()->is_mahasiswa);
        $isAdmin = Gate::check('admin_view') || str_contains($roleName, 'ADMIN');
        $isSpecialStaff = !$isMahasiswa && !$isAdmin;
    @endphp

    @if ($isSpecialStaff)
        <div class="row">
            <!-- Header Welcome -->
            <div class="col-md-12 mb-4">
                <div class="card bg-gradient-uis-green text-white p-4 shadow-sm border-0" style="border-radius: 15px;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="font-weight-bold mb-1">Halo, {{ auth()->user()->name }}! 👋</h3>
                            <p class="mb-0 opacity-75">Selamat datang di Panel Pengelolaan Arsip MYBAAK. Berikut adalah
                                ringkasan data yang Anda kelola hari ini.</p>
                        </div>
                        <div class="col-md-4 text-right d-none d-md-block">
                            <i class="fas fa-folder-tree fa-4x opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Archive Stats Cards -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card-premium border-0 shadow-sm"
                    style="background: #ffffff; border-left: 5px solid #00A551 !important;">
                    <div class="card-block p-4">
                        <h6 class="text-muted text-uppercase small font-weight-bold">SK Kepanitiaan</h6>
                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <h3 class="font-weight-bold mb-0">{{ $skKepanitiaanCount }}</h3>
                            <div class="bg-soft-green p-3 rounded-circle">
                                <i class="fas fa-file-signature text-success f-20"></i>
                            </div>
                        </div>
                        <a href="{{ route('skkepanitiaan.index') }}"
                            class="small text-success font-weight-bold mt-3 d-block">Lihat Detail <i
                                class="fas fa-arrow-right ml-1"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card-premium border-0 shadow-sm"
                    style="background: #ffffff; border-left: 5px solid #4099ff !important;">
                    <div class="card-block p-4">
                        <h6 class="text-muted text-uppercase small font-weight-bold">LPJ Kepanitiaan</h6>
                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <h3 class="font-weight-bold mb-0">{{ $lpjKepanitiaanCount }}</h3>
                            <div class="bg-soft-blue p-3 rounded-circle">
                                <i class="fas fa-file-invoice-dollar text-primary f-20"></i>
                            </div>
                        </div>
                        <a href="{{ route('lpjkepanitiaan.index') }}"
                            class="small text-primary font-weight-bold mt-3 d-block">Lihat Detail <i
                                class="fas fa-arrow-right ml-1"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card-premium border-0 shadow-sm"
                    style="background: #ffffff; border-left: 5px solid #fe8c00 !important;">
                    <div class="card-block p-4">
                        <h6 class="text-muted text-uppercase small font-weight-bold">Kurikulum Prodi</h6>
                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <h3 class="font-weight-bold mb-0">{{ $kurikulumCount }}</h3>
                            <div class="bg-soft-orange p-3 rounded-circle">
                                <i class="fas fa-graduation-cap text-warning f-20"></i>
                            </div>
                        </div>
                        <a href="{{ route('kurikulum.index') }}"
                            class="small text-warning font-weight-bold mt-3 d-block">Lihat Detail <i
                                class="fas fa-arrow-right ml-1"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card-premium border-0 shadow-sm"
                    style="background: #ffffff; border-left: 5px solid #667eea !important;">
                    <div class="card-block p-4">
                        <h6 class="text-muted text-uppercase small font-weight-bold">Wasdalbin</h6>
                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <h3 class="font-weight-bold mb-0">{{ $wasdalbinCount }}</h3>
                            <div class="bg-soft-purple p-3 rounded-circle">
                                <i class="fas fa-clipboard-check text-purple f-20"></i>
                            </div>
                        </div>
                        <a href="{{ route('wasdalbin.index') }}"
                            class="small text-purple font-weight-bold mt-3 d-block">Lihat Detail <i
                                class="fas fa-arrow-right ml-1"></i></a>
                    </div>
                </div>
            </div>

            <!-- Lower Section: Quick Actions & Recent Info -->
            <div class="col-xl-8 col-md-12">
                <div class="card shadow-sm border-0" style="border-radius: 12px; height: 100%;">
                    <div class="card-header bg-white border-bottom p-4">
                        <h5 class="font-weight-bold mb-0">Informasi Arsip Tambahan</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="p-3 bg-light rounded d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-1 font-weight-bold">Pedoman Akademik</h6>
                                        <p class="small text-muted mb-0">Total: {{ $pedomanCount }} file</p>
                                    </div>
                                    <a href="{{ route('pedoman.index') }}"
                                        class="btn btn-sm btn-outline-dark rounded-pill">Buka</a>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="p-3 bg-light rounded d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-1 font-weight-bold">SOP Akademik</h6>
                                        <p class="small text-muted mb-0">Total: {{ $sopAkademikCount }} file</p>
                                    </div>
                                    <a href="{{ route('sopakademik.index') }}"
                                        class="btn btn-sm btn-outline-dark rounded-pill">Buka</a>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="text-center py-3">
                            <h6 class="font-weight-bold mb-3">Butuh Dokumen Tertentu?</h6>
                            <a href="{{ route('semantic.index') }}"
                                class="btn btn-primary btn-lg rounded-pill px-5 shadow">
                                <i class="fas fa-search mr-2"></i> Gunakan Semantic Search
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-12">
                <div class="card shadow-sm border-0 bg-info text-white" style="border-radius: 12px; height: 100%;">
                    <div class="card-body p-4">
                        <h5 class="font-weight-bold mb-4"><i class="fas fa-info-circle mr-2"></i> Peran Anda</h5>
                        <p>Sebagai Pengelola Arsip, Anda memiliki kewenangan untuk:</p>
                        <ul class="pl-3 mb-4">
                            <li class="mb-2">Menambah/Unggah dokumen baru ke Google Drive.</li>
                            <li class="mb-2">Memperbarui informasi dokumen yang tersedia.</li>
                            <li class="mb-2">Melakukan pencarian berbasis AI (Semantic).</li>
                        </ul>
                        <div class="text-center mt-auto">
                            <img src="{{ asset('assets/images/user.png') }}" class="img-fluid opacity-50 mb-3"
                                style="max-height: 120px;">
                            <p class="small italic">MYBAAK System Version 2.0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Section for Admin/Superadmin --}}
    @if ($isAdmin)
        <div class="row">
            <!-- Premium Stats Cards -->
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card-premium bg-gradient-uis-green">
                    <div class="card-block">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-white">{{ $totalUser }}</h4>
                                <h6 class="text-white m-b-0">Total Pengguna</h6>
                            </div>
                            <div class="col-4 text-right">
                                <i class="fas fa-users f-28 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card-premium bg-gradient-blue">
                    <div class="card-block">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-white">{{ $mahasiswa }}</h4>
                                <h6 class="text-white m-b-0">Mahasiswa</h6>
                            </div>
                            <div class="col-4 text-right">
                                <i class="fas fa-user-graduate f-28 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card-premium bg-gradient-green">
                    <div class="card-block">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-white">{{ $suratAktifDiterima }}</h4>
                                <h6 class="text-white m-b-0">Surat Selesai</h6>
                            </div>
                            <div class="col-4 text-right">
                                <i class="fas fa-check-circle f-28 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card-premium bg-gradient-orange">
                    <div class="card-block">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-white">{{ $suratAktifpending }}</h4>
                                <h6 class="text-white m-b-0">Surat Pending</h6>
                            </div>
                            <div class="col-4 text-right">
                                <i class="fas fa-clock f-28 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="col-xl-8 col-md-12">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="font-weight-bold"><i class="fas fa-chart-line text-primary mr-2"></i> Grafik Pengajuan
                            Surat</h5>
                        <p class="text-muted small">Statistik 5 Tahun Terakhir</p>
                    </div>
                    <div class="card-block pt-0">
                        <div id="morris-bar-chart" style="height: 320px;"></div>
                    </div>
                </div>
            </div>

            <!-- Status Arsip -->
            <div class="col-xl-4 col-md-12">
                <div class="card shadow-sm border-0 mb-4"
                    style="border-radius: 12px; background: linear-gradient(45deg, #1d976c, #93f9b9);">
                    <div class="card-block p-4 text-white">
                        <h5 class="font-weight-bold mb-3">Layanan Cepat</h5>
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('suratAktif.create') }}"
                                class="btn btn-light btn-block text-success font-weight-bold rounded-pill mb-2 shadow-sm">
                                <i class="fas fa-plus-circle mr-1"></i> Baru: Surat Aktif
                            </a>
                            <a href="{{ route('suratAkademik.create') }}"
                                class="btn btn-light btn-block text-primary font-weight-bold rounded-pill shadow-sm">
                                <i class="fas fa-plus-circle mr-1"></i> Baru: Surat Akademik
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="font-weight-bold">Status Arsip</h5>
                    </div>
                    <div class="card-block p-0">
                        <div class="table-responsive">
                            <table class="table table-custom mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 font-weight-bold small text-muted">JENIS</th>
                                        <th class="border-0 font-weight-bold small text-muted text-center">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr onclick="window.location='{{ route('skkepanitiaan.index') }}'"
                                        style="cursor: pointer;">
                                        <td class="py-3 px-4 small font-weight-bold text-dark">SK Kepanitiaan</td>
                                        <td class="text-center py-3"><span
                                                class="badge badge-pill badge-primary">{{ $skKepanitiaanCount }}</span>
                                        </td>
                                    </tr>
                                    <tr onclick="window.location='{{ route('lpjkepanitiaan.index') }}'"
                                        style="cursor: pointer;">
                                        <td class="py-3 px-4 small font-weight-bold text-dark">LPJ Kepanitiaan</td>
                                        <td class="text-center py-3"><span
                                                class="badge badge-pill badge-success">{{ $lpjKepanitiaanCount }}</span>
                                        </td>
                                    </tr>
                                    <tr onclick="window.location='{{ route('kurikulum.index') }}'"
                                        style="cursor: pointer;">
                                        <td class="py-3 px-4 small font-weight-bold text-dark">Kurikulum Prodi</td>
                                        <td class="text-center py-3"><span
                                                class="badge badge-pill badge-info">{{ $kurikulumCount }}</span></td>
                                    </tr>
                                    <tr onclick="window.location='{{ route('wasdalbin.index') }}'"
                                        style="cursor: pointer;">
                                        <td class="py-3 px-4 small font-weight-bold text-dark">Wasdalbin</td>
                                        <td class="text-center py-3"><span
                                                class="badge badge-pill badge-dark">{{ $wasdalbinCount }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- New Rekapitulasi per Semester Table -->
            <div class="col-xl-12 col-md-12">
                <div class="card shadow-sm border-0" style="border-radius: 12px; border-top: 5px solid #00A551 !important;">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="font-weight-bold"><i class="fas fa-table text-success mr-2"></i> Rekapitulasi Pengajuan Surat per Semester</h5>
                    </div>
                    <div class="card-block p-0">
                        <div class="table-responsive">
                            <table class="table table-custom mb-0">
                                <thead class="text-center">
                                    <tr>
                                        <th rowspan="2" class="align-middle font-weight-bold">SEMESTER</th>
                                        <th colspan="2" class="font-weight-bold">SURAT AKTIF</th>
                                        <th colspan="2" class="font-weight-bold">SURAT AKADEMIK</th>
                                    </tr>
                                    <tr>
                                        <th class="font-weight-bold">PENDING</th>
                                        <th class="font-weight-bold">DITERIMA</th>
                                        <th class="font-weight-bold">PENDING</th>
                                        <th class="font-weight-bold">DITERIMA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rekapSemester as $sem => $counts)
                                        <tr>
                                            <td class="py-3 px-4 small font-weight-bold text-dark text-center">
                                                {{ $sem }}
                                            </td>
                                            <td class="text-center py-3">
                                                <span class="badge badge-pill badge-warning">{{ $counts['aktif_pending'] }}</span>
                                            </td>
                                            <td class="text-center py-3">
                                                <span class="badge badge-pill badge-success">{{ $counts['aktif_diterima'] }}</span>
                                            </td>
                                            <td class="text-center py-3">
                                                <span class="badge badge-pill badge-warning">{{ $counts['akademik_pending'] }}</span>
                                            </td>
                                            <td class="text-center py-3">
                                                <span class="badge badge-pill badge-success">{{ $counts['akademik_diterima'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($yearsPaginated->hasPages())
                        <div class="card-footer bg-white border-0 pb-4 px-4 d-flex justify-content-center">
                            {{ $yearsPaginated->appends(request()->except('rekap_page'))->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        </div>

        @push('scripts')
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>

            <script>
                $(document).ready(function() {
                    Morris.Bar({
                        element: 'morris-bar-chart',
                        data: [
                            @foreach ($chartData as $data)
                                {
                                    y: '{{ $data['year'] }}',
                                    pending: {{ $data['pending'] }},
                                    diterima: {{ $data['diterima'] }},
                                    ditolak: {{ $data['ditolak'] }},
                                    akademik: {{ $data['akademik'] }}
                                },
                            @endforeach
                        ],
                        xkey: 'y',
                        ykeys: ['pending', 'diterima', 'ditolak', 'akademik'],
                        labels: ['Pending', 'Diterima', 'Ditolak', 'Akademik'],
                        barColors: ['#FFB64D', '#2ed8b6', '#FF5370', '#4099ff'],
                        hideHover: 'auto',
                        gridLineColor: '#f1f1f1',
                        resize: true,
                        barSizeRatio: 0.4,
                        gridTextSize: 12,
                        gridTextColor: '#999'
                    });
                });
            </script>
        @endpush
    @endif

    {{-- Section for Mahasiswa --}}
    @if ($isMahasiswa)
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 15px;">
                    <div class="card-header bg-gradient-blue text-white p-4">
                        <h5 class="mb-0 font-weight-bold text-white"><i class="fas fa-paper-plane mr-2"></i> Ajukan Surat
                            Baru</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="{{ route('suratAktif.create') }}" class="text-decoration-none">
                                    <div
                                        class="quick-access-card bg-soft-green p-4 text-center rounded shadow-sm transition-hover">
                                        <i class="fas fa-file-pdf text-success fa-3x mb-3"></i>
                                        <h6 class="font-weight-bold text-dark">Surat Keterangan Aktif</h6>
                                        <p class="text-muted small mb-0">Untuk keperluan beasiswa, BPJS, dll.</p>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="{{ route('suratAkademik.create') }}" class="text-decoration-none">
                                    <div
                                        class="quick-access-card bg-soft-yellow p-4 text-center rounded shadow-sm transition-hover">
                                        <i class="fas fa-file-signature text-warning fa-3x mb-3"></i>
                                        <h6 class="font-weight-bold text-dark">Surat Akademik</h6>
                                        <p class="text-muted small mb-0">Cuti, Pindah Kelas, atau Keluar.</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center p-4" style="border-radius: 15px; min-height: 100%;">
                    <div class="mb-4">
                        <div class="status-icon-circle mx-auto bg-light">
                            <i class="fas fa-history text-primary fa-2x"></i>
                        </div>
                    </div>
                    <h5 class="font-weight-bold">Status Pengajuan Terakhir</h5>
                    <hr>

                    @if ($latestSuratAktif)
                        <div class="status-result py-3">
                            @if ($latestSuratAktif->status == 'pending')
                                <div class="badge badge-warning p-3 rounded-pill mb-3 px-4">
                                    <i class="fas fa-clock mr-1"></i> PENDING
                                </div>
                                <h6 class="font-weight-bold">Sedang Diproses</h6>
                                <p class="text-muted small">Estimasi 3 hari kerja sejak pengajuan.</p>
                            @elseif($latestSuratAktif->status == 'diterima')
                                <div class="badge badge-success p-3 rounded-pill mb-3 px-4">
                                    <i class="fas fa-check-circle mr-1"></i> SELESAI
                                </div>
                                <h6 class="font-weight-bold">Surat Sudah Tersedia</h6>
                                <a href="{{ route('suratAktif.show', $latestSuratAktif->id) }}"
                                    class="btn btn-primary btn-sm rounded-pill mt-2">
                                    <i class="fas fa-download mr-1"></i> Unduh Surat
                                </a>
                            @elseif($latestSuratAktif->status == 'ditolak')
                                <div class="badge badge-danger p-3 rounded-pill mb-3 px-4">
                                    <i class="fas fa-times-circle mr-1"></i> DITOLAK
                                </div>
                                <h6 class="font-weight-bold">Pengajuan Ditolak</h6>
                                <p class="text-muted small">Silakan periksa catatan dan ajukan kembali.</p>
                            @endif
                        </div>
                    @else
                        <div class="py-4">
                            <p class="text-muted italic">Belum ada pengajuan surat aktif perkuliahan.</p>
                            <a href="{{ route('suratAktif.create') }}"
                                class="btn btn-outline-primary btn-sm rounded-pill">Ajukan Sekarang</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif


@push('style')
    <style>
        .stat-card-premium {
            border: none;
            border-radius: 12px;
            transition: transform 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .stat-card-premium:hover {
            transform: translateY(-5px);
        }

        .bg-gradient-uis-green {
            background: linear-gradient(135deg, #00A551 0%, #008240 100%);
        }

        .bg-gradient-uis-yellow {
            background: linear-gradient(135deg, #FFF742 0%, #FFD700 100%);
            color: #333 !important;
        }

        .bg-gradient-uis-yellow h4,
        .bg-gradient-uis-yellow h6,
        .bg-gradient-uis-yellow i {
            color: #333 !important;
        }

        .bg-gradient-blue {
            background: linear-gradient(135deg, #4099ff 0%, #73b4ff 100%);
        }

        .bg-gradient-green {
            background: linear-gradient(135deg, #00A551 0%, #008240 100%);
        }

        .bg-gradient-orange {
            background: linear-gradient(135deg, #fe8c00 0%, #f83600 100%);
        }

        .bg-gradient-purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .opacity-50 {
            opacity: 0.5;
        }

        .f-28 {
            font-size: 28px;
        }

        .bg-soft-green {
            background-color: rgba(0, 165, 81, 0.1);
        }

        .bg-soft-yellow {
            background-color: rgba(255, 247, 66, 0.2);
        }

        .quick-access-card {
            border: 1px solid transparent;
            height: 100%;
            border-radius: 12px;
        }

        .quick-access-card:hover {
            border-color: #00A551;
        }

        .status-icon-circle {
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: rgba(0, 165, 81, 0.1) !important;
        }

        .status-icon-circle i {
            color: #00A551 !important;
        }

        .transition-hover {
            transition: all 0.3s ease;
        }

        .transition-hover:hover {
            /* Removed scale to prevent vibration on scrollable tables */
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }

    </style>
@endpush
@endsection
