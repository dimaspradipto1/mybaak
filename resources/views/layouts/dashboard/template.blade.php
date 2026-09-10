<!DOCTYPE html>
<html lang="en">

<head>
    <title>Dashboard - {{ env('APP_NAME') }}</title>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="keywords" content="bootstrap, bootstrap admin template, admin theme, admin dashboard, dashboard template, admin template, responsive" />
    <meta name="author" content="Codedthemes" />
    <!-- Favicon icon -->
    <link rel="icon" href="{{ asset('assets/images/logouis.png') }}" type="image/x-icon">
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet text-css">

    <!-- waves.css -->
    <link rel="stylesheet" href="{{ asset('assets/pages/waves/css/waves.min.css') }}" type="text/css" media="all">
    <!-- Required Framework -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap/css/bootstrap.min.css') }}">
    <!-- themify icon -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/icon/themify-icons/themify-icons.css') }}">
    <!-- font-awesome -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/font-awesome-n.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <!-- scrollbar.css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/jquery.mCustomScrollbar.css') }}">
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }}">

    {{-- datatables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.6/css/dataTables.bootstrap5.css">

    {{-- select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    {{-- Dashboard layout styles --}}
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-layout.css') }}">

    {{-- SweetAlert & Modal positioning adjustment so they appear below top navbar --}}
    <style>
        .swal2-container {
            z-index: 999999 !important;
        }
        .swal2-container.swal2-top,
        .swal2-container.swal2-top-start,
        .swal2-container.swal2-top-end,
        .swal2-top-end {
            top: 75px !important;
            padding-top: 10px !important;
        }

        /* Bootstrap Modal adjustment so modal dialog starts below top navbar */
        .modal {
            z-index: 10550 !important;
            padding-top: 75px !important;
        }
        .modal-backdrop {
            z-index: 10540 !important;
        }
        .modal-dialog {
            margin-top: 10px !important;
        }
    </style>

    @stack('style')
    @stack('styles')
</head>

<body>
    <!-- Pre-loader start -->
    <div class="theme-loader">
        <div class="loader-track">
            <div class="preloader-wrapper">
                <div class="spinner-layer spinner-blue">
                    <div class="circle-clipper left"><div class="circle"></div></div>
                    <div class="gap-patch"><div class="circle"></div></div>
                    <div class="circle-clipper right"><div class="circle"></div></div>
                </div>
                <div class="spinner-layer spinner-red">
                    <div class="circle-clipper left"><div class="circle"></div></div>
                    <div class="gap-patch"><div class="circle"></div></div>
                    <div class="circle-clipper right"><div class="circle"></div></div>
                </div>
                <div class="spinner-layer spinner-yellow">
                    <div class="circle-clipper left"><div class="circle"></div></div>
                    <div class="gap-patch"><div class="circle"></div></div>
                    <div class="circle-clipper right"><div class="circle"></div></div>
                </div>
                <div class="spinner-layer spinner-green">
                    <div class="circle-clipper left"><div class="circle"></div></div>
                    <div class="gap-patch"><div class="circle"></div></div>
                    <div class="circle-clipper right"><div class="circle"></div></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Pre-loader end -->

    {{-- ============================================================
         DESKTOP TOPNAV — INDEPENDENT, OUTSIDE #pcoded
         Rendered directly in body so framework JS cannot interfere.
         ============================================================ --}}
    @php $currentRoute = (request()->route() ? request()->route()->getName() : ''); @endphp
    <nav id="uis-topnav">
        <ul>
            {{-- Dashboard --}}
            <li class="{{ Str::startsWith($currentRoute, 'dashboard') ? 'active' : '' }}">
                <a href="{{ Auth::check() ? route('dashboard') : route('login') }}">
                    <i class="ti-home"></i> Dashboard
                </a>
            </li>

            {{-- Layanan Mahasiswa --}}
            @if(Auth::check() && (Auth::user()->is_mahasiswa || Gate::any(['surat_aktif_view', 'surat_akademik_view'])))
            <li class="has-sub {{ Str::startsWith($currentRoute, ['suratAktif', 'suratAkademik']) ? 'active' : '' }}">
                <a href="javascript:void(0)"><i class="fa-solid fa-users"></i> Layanan Mahasiswa</a>
                <ul class="uis-submenu">
                    @if(Auth::user()->is_mahasiswa || Gate::check('surat_aktif_view'))
                    <li><a href="{{ route('suratAktif.index') }}"><i class="ti-angle-right"></i> Surat Keterangan Aktif</a></li>
                    @endif
                    @if(Auth::user()->is_mahasiswa || Gate::check('surat_akademik_view'))
                    <li><a href="{{ route('suratAkademik.index') }}"><i class="ti-angle-right"></i> Surat Layanan Akademik</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- Arsip --}}
            @canany(['arsip_utama_view', 'sk_kepanitiaan_view', 'lpj_kepanitiaan_view', 'kurikulum_view', 'pedoman_view', 'sop_akademik_view', 'wasdalbin_view'])
            <li class="has-sub {{ Str::startsWith($currentRoute, ['arsiputama', 'skkepanitiaan', 'lpjkepanitiaan', 'kurikulum', 'pedoman', 'sopakademik', 'wasdalbin', 'semantic']) ? 'active' : '' }}">
                <a href="javascript:void(0)"><i class="fa-solid fa-folder-open"></i> Arsip</a>
                <ul class="uis-submenu">
                    <li><a href="{{ route('semantic.index') }}"><i class="ti-angle-right"></i> Arsip UIS</a></li>
                    @can('arsip_utama_view')<li><a href="{{ route('arsiputama.index') }}"><i class="ti-angle-right"></i> Arsip Utama</a></li>@endcan
                    @can('sk_kepanitiaan_view')<li><a href="{{ route('skkepanitiaan.index') }}"><i class="ti-angle-right"></i> SK Kepanitiaan</a></li>@endcan
                    @can('lpj_kepanitiaan_view')<li><a href="{{ route('lpjkepanitiaan.index') }}"><i class="ti-angle-right"></i> LPJ Kepanitiaan</a></li>@endcan
                    @can('kurikulum_view')<li><a href="{{ route('kurikulum.index') }}"><i class="ti-angle-right"></i> Kurikulum Prodi</a></li>@endcan
                    @can('pedoman_view')<li><a href="{{ route('pedoman.index') }}"><i class="ti-angle-right"></i> Pedoman</a></li>@endcan
                    @can('sop_akademik_view')<li><a href="{{ route('sopakademik.index') }}"><i class="ti-angle-right"></i> SOP Akademik</a></li>@endcan
                    @can('wasdalbin_view')<li><a href="{{ route('wasdalbin.index') }}"><i class="ti-angle-right"></i> Wasdalbin</a></li>@endcan
                </ul>
            </li>
            @endcanany

            {{-- Portal Artikel --}}
            @can('artikel_view')
            <li class="{{ Str::startsWith($currentRoute, 'artikel') ? 'active' : '' }}">
                <a href="{{ route('artikel.index') }}"><i class="fa-solid fa-newspaper"></i> Portal Artikel</a>
            </li>
            @endcan

            {{-- Laporan --}}
            @canany(['rekapitulasi_arsip_view', 'rekapitulasi_surat_aktif_view'])
            <li class="has-sub {{ Str::startsWith($currentRoute, 'rekapitulasi') ? 'active' : '' }}">
                <a href="javascript:void(0)"><i class="fa-regular fa-file-lines"></i> Laporan</a>
                <ul class="uis-submenu">
                    @can('rekapitulasi_arsip_view')<li><a href="{{ route('rekapitulasiarsip.index') }}"><i class="ti-angle-right"></i> Rekapitulasi Arsip</a></li>@endcan
                    @can('rekapitulasi_surat_aktif_view')<li><a href="{{ route('rekapitulasisurataktif.index') }}"><i class="ti-angle-right"></i> Rekapitulasi Surat Aktif</a></li>@endcan
                </ul>
            </li>
            @endcanany

            {{-- Master Data --}}
            @canany(['role_view', 'users_view', 'pegawai_view', 'dosen_view', 'mahasiswa_view', 'jenis_sk_view', 'kategori_arsip_view', 'tahun_akademik_view', 'program_studi_view', 'unit_kerja_view'])
            <li class="has-sub {{ Str::startsWith($currentRoute, ['role', 'users', 'pegawai', 'dosen', 'mahasiswa', 'jenissk', 'kategoriarsip', 'tahunAkademik', 'programStudi', 'unitkerja']) ? 'active' : '' }}">
                <a href="javascript:void(0)"><i class="fa-solid fa-database"></i> Master Data</a>
                <ul class="uis-submenu">
                    @can('role_view')<li><a href="{{ route('role.index') }}"><i class="fa-solid fa-users-gear"></i> Role Akses</a></li>@endcan
                    @can('users_view')<li><a href="{{ route('users.index') }}"><i class="fa-solid fa-users"></i> Pengguna</a></li>@endcan
                    @can('pegawai_view')<li><a href="{{ route('pegawai.index') }}"><i class="fa-solid fa-user-gear"></i> Pegawai</a></li>@endcan
                    @can('dosen_view')<li><a href="{{ route('dosen.index') }}"><i class="fa-solid fa-user-gear"></i> Dosen</a></li>@endcan
                    @can('mahasiswa_view')<li><a href="{{ route('mahasiswa.index') }}"><i class="fa-solid fa-users"></i> Mahasiswa</a></li>@endcan
                    @can('jenis_sk_view')<li><a href="{{ route('jenissk.index') }}"><i class="fa-solid fa-list"></i> Jenis SK</a></li>@endcan
                    @can('kategori_arsip_view')<li><a href="{{ route('kategoriarsip.index') }}"><i class="fa-solid fa-tags"></i> Kategori Arsip</a></li>@endcan
                    @can('tahun_akademik_view')<li><a href="{{ route('tahunAkademik.index') }}"><i class="fa-solid fa-calendar-days"></i> Tahun Akademik</a></li>@endcan
                    @can('program_studi_view')<li><a href="{{ route('programStudi.index') }}"><i class="fa-solid fa-folder-closed"></i> Program Studi</a></li>@endcan
                    @can('unit_kerja_view')<li><a href="{{ route('unitkerja.index') }}"><i class="fa-solid fa-sitemap"></i> Unit Kerja</a></li>@endcan
                </ul>
            </li>
            @endcanany

            {{-- Panduan --}}
            <li class="has-sub {{ Str::startsWith($currentRoute, ['userGuide', 'faq', 'userguide']) ? 'active' : '' }}">
                <a href="javascript:void(0)"><i class="fa-solid fa-book"></i> Panduan</a>
                <ul class="uis-submenu">
                    @canany(['users_view', 'role_view'])
                    <li><a href="{{ route('userGuideTatausaha.index') }}"><i class="ti-angle-right"></i> Panduan Admin - Tata Usaha</a></li>
                    <li><a href="{{ route('userGuideMahasiswa.index') }}"><i class="ti-angle-right"></i> Panduan Admin - Mahasiswa</a></li>
                    <li><a href="{{ route('faq.index') }}"><i class="ti-angle-right"></i> FAQ Admin</a></li>
                    @endcanany
                    <li><a href="{{ route('userGuidePenggunaTatausaha') }}"><i class="ti-angle-right"></i> Panduan Tata Usaha</a></li>
                    <li><a href="{{ route('userGuidePenggunaMahasiswa') }}"><i class="ti-angle-right"></i> Panduan Mahasiswa</a></li>
                    <li><a href="{{ route('userguidepengguna') }}"><i class="ti-angle-right"></i> FAQ</a></li>
                </ul>
            </li>

            {{-- User Menu (right-aligned, desktop only) --}}
            @auth
            <li class="has-sub uis-user-menu">
                <a href="javascript:void(0)">
                    <img src="{{ Auth::user()->profile && Auth::user()->profile->foto ? asset('storage/' . Auth::user()->profile->foto) : asset('assets/images/user.png') }}"
                         style="width:26px;height:26px;object-fit:cover;border-radius:50%;vertical-align:middle;margin-right:6px;">
                    {{ Auth::user()->name }}
                </a>
                <ul class="uis-submenu">
                    <li><a href="{{ route('settings.index') }}"><i class="ti-settings"></i> Settings</a></li>
                    <li><a href="{{ route('profile.index') }}"><i class="ti-user"></i> Profile</a></li>
                    <li><a href="{{ route('userGuide.index') }}"><i class="fa-regular fa-file-lines"></i> User Guide</a></li>
                    <li><a href="https://docs.google.com/spreadsheets/d/1BMulYA5yhLsgtMXJ4fMs55jG2d-CT86kSI43tGqMSQE/edit?usp=sharing" target="_blank"><i class="ti-comment"></i> Feedback</a></li>
                    <li><a href="{{ route('logout') }}"><i class="ti-power-off"></i> Logout</a></li>
                </ul>
            </li>
            @endauth
        </ul>
    </nav>
    {{-- END DESKTOP TOPNAV --}}

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            @include('layouts.dashboard.navbar')

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">

                    @include('layouts.dashboard.sidebar')

                    <div class="pcoded-content">
                        <!-- Page-header start -->
                        <div class="page-header">
                            <div class="page-block">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="page-header-title">
                                            <h5 class="m-b-10">Dashboard</h5>
                                            <p class="m-b-0">Welcome to Sistem Informasi {{ env('APP_NAME') }} </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <ul class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a href="{{ route('dashboard') }}"><i class="fa fa-home"></i> Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item"><a href="javascript:void(0)">{{ $title ?? 'Dashboard' }}</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Page-header end -->

                        <div class="pcoded-inner-content">
                            <!-- Main-body start -->
                            <div class="main-body">
                                <div class="page-wrapper">
                                    <!-- Page-body start -->
                                    <div class="page-body">
                                        @include('sweetalert::alert')
                                        @yield('content')
                                    </div>
                                    <!-- Page-body end -->
                                </div>
                                <div id="styleSelector"> </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Required Jquery -->
    <script type="text/javascript" src="{{ asset('assets/js/jquery/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/jquery-ui/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/popper.js/popper.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/bootstrap/js/bootstrap.min.js') }}"></script>
    <!-- waves js -->
    <script src="{{ asset('assets/pages/waves/js/waves.min.js') }}"></script>
    <!-- jquery slimscroll js -->
    <script type="text/javascript" src="{{ asset('assets/js/jquery-slimscroll/jquery.slimscroll.js') }}"></script>
    <!-- slimscroll js -->
    <script src="{{ asset('assets/js/jquery.mCustomScrollbar.concat.min.js') }}"></script>
    <!-- menu js -->
    <script src="{{ asset('assets/js/pcoded.min.js') }}"></script>
    <script src="{{ asset('assets/js/vertical/vertical-layout.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/script.js') }}"></script>

    {{-- fontawesome --}}
    <script src="https://kit.fontawesome.com/63b8672806.js" crossorigin="anonymous"></script>

    {{-- datatables --}}
    <script src="https://cdn.datatables.net/2.3.6/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.6/js/dataTables.bootstrap5.js"></script>

    {{-- select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- ckeditor --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.16.2/ckeditor.js"></script>

    {{-- SweetAlert2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- SweetAlert2 Helper & Global Confirm Handler --}}
    <script>
        function confirmDelete(event, element) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            var form = element ? (element.form || element.closest('form')) : null;
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus data ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed && form) {
                    form.submit();
                }
            });
            return false;
        }

        $(document).ready(function() {
            // Global Double-Submit Prevention for All Forms
            $(document).on('submit', 'form', function(e) {
                var $form = $(this);

                if ($form.hasClass('no-prevent-double-submit')) {
                    return true;
                }

                if ($form.data('is-submitting') === true) {
                    e.preventDefault();
                    return false;
                }

                $form.data('is-submitting', true);
                var $btn = $form.find('button[type="submit"], input[type="submit"]');

                setTimeout(function() {
                    $btn.prop('disabled', true);
                    if ($btn.is('button')) {
                        $btn.data('orig-text', $btn.html());
                        $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
                    }
                }, 10);
            });

            // Re-enable button if HTML5 validation stops submission
            $(document).on('invalid', 'input, select, textarea', function() {
                var $form = $(this).closest('form');
                if ($form.length) {
                    $form.data('is-submitting', false);
                    var $btn = $form.find('button[type="submit"], input[type="submit"]');
                    $btn.prop('disabled', false);
                    if ($btn.is('button') && $btn.data('orig-text')) {
                        $btn.html($btn.data('orig-text'));
                    }
                }
            });

            $(document).on('click', 'button[onclick*="confirm"], a[onclick*="confirm"]', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var btn = this;
                var form = btn.closest('form');
                
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: 'Apakah Anda yakin ingin menghapus data ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (form) {
                            form.submit();
                        } else if (btn.tagName === 'A' && btn.href) {
                            window.location.href = btn.href;
                        }
                    }
                });
                return false;
            });
        });
    </script>

    @stack('script')
    @stack('scripts')

    <script>
    /* Auto-mark active topnav menu based on current URL */
    document.addEventListener('DOMContentLoaded', function () {
        var currentPath = window.location.pathname;

        // Mark active submenu item
        document.querySelectorAll('#uis-topnav .uis-submenu > li > a').forEach(function (link) {
            if (link.getAttribute('href') && link.getAttribute('href') !== 'javascript:void(0)') {
                var linkPath = new URL(link.href, window.location.origin).pathname;
                if (currentPath.startsWith(linkPath) && linkPath !== '/') {
                    link.closest('li').classList.add('active');
                    // Also keep parent menu open/active visually
                    var parentLi = link.closest('#uis-topnav > ul > li');
                    if (parentLi) parentLi.classList.add('active');
                }
            }
        });

        // Mark active top-level items (no submenu)
        document.querySelectorAll('#uis-topnav > ul > li:not(.has-sub) > a').forEach(function (link) {
            if (link.getAttribute('href') && link.getAttribute('href') !== 'javascript:void(0)') {
                var linkPath = new URL(link.href, window.location.origin).pathname;
                if (currentPath.startsWith(linkPath) && linkPath !== '/') {
                    link.closest('li').classList.add('active');
                }
            }
        });
    });
    </script>
</body>

</html>
