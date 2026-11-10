<nav class="pcoded-navbar">
    <div class="sidebar_toggle"><a href="javascript:void(0)"><i class="icon-close icons"></i></a></div>
    <div class="pcoded-inner-navbar main-menu">
        <div class="">
            <div class="main-menu-header">
                @auth
                <img class="img-80 img-radius"
                    src="{{ Auth::user()->profile && Auth::user()->profile->foto ? asset('storage/' . Auth::user()->profile->foto) : asset('assets/images/user.png') }}"
                    alt="User-Profile-Image" style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%;">
                <div class="user-details">
                    <span id="more-details">{{ Auth::user()->name }}<i class="fa fa-caret-down"></i></span>
                </div>
                @else
                <img class="img-80 img-radius" src="{{ asset('assets/images/user.png') }}" alt="User-Profile-Image">
                <div class="user-details">
                    <span id="more-details">Guest User<i class="fa fa-caret-down"></i></span>
                </div>
                @endauth
            </div>
            <div class="main-menu-content">
                <ul>
                    <li class="more-details">
                        <a href="{{ route('semantic.index') }}"><i class="ti-search"></i>Semantic Search</a>
                        @auth
                        <a href="{{ route('profile.index') }}"><i class="ti-user"></i>View Profile</a>
                        <a href="{{ route('settings.index') }}"><i class="ti-settings"></i>Settings</a>
                        <a href="{{ route('logout') }}"><i class="ti-layout-sidebar-left"></i>Logout</a>
                        @else
                        <a href="{{ route('login') }}"><i class="ti-layout-sidebar-left"></i>Login</a>
                        @endauth
                    </li>
                </ul>
            </div>
        </div>
        {{-- <div class="p-15 p-b-0">
            <form class="form-material">
                <div class="form-group form-primary">
                    <input type="text" name="footer-email" class="form-control">
                    <span class="form-bar"></span>
                    <label class="float-label"><i class="fa fa-search m-r-10"></i>Search
                        Friend</label>
                </div>
            </form>
        </div> --}}
        {{-- <div class="pcoded-navigation-label">Navigation</div> --}}
        <ul class="pcoded-item pcoded-left-item">
            <li class="active">
                <a href="{{ Auth::check() ? route('dashboard') : route('login') }}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-home"></i><b>D</b></span>
                    <span class="pcoded-mtext">Dashboard</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
        </ul>


        @if(Auth::check() && (Auth::user()->is_mahasiswa || Gate::any(['surat_aktif_view', 'surat_akademik_view'])))
            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="fa-solid fa-users"></i></span>
                        <span class="pcoded-mtext text-capitalize">layanan mahasiswa</span>
                        <span class="pcoded-mcaret"></span>
                    </a>
                    <ul class="pcoded-submenu">
                        @if(Auth::user()->is_mahasiswa || Gate::check('surat_aktif_view'))
                        <li class=" ">
                            <a href="{{ route('suratAktif.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                <span class="pcoded-mtext text-capitalize">surat keterangan aktif</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endif
                        @if(Auth::user()->is_mahasiswa || Gate::check('surat_akademik_view'))
                        <li class=" ">
                            <a href="{{ route('suratAkademik.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                <span class="pcoded-mtext text-capitalize">surat layanan akademik</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
            </ul>
        @endif

        @canany(['arsip_utama_view', 'sk_kepanitiaan_view', 'lpj_kepanitiaan_view', 'kurikulum_view', 'pedoman_view', 'sop_akademik_view', 'wasdalbin_view'])
            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="fa-solid fa-folder-open"></i></span>
                        <span class="pcoded-mtext text-capitalize">arsip</span>
                        <span class="pcoded-mcaret"></span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class=" ">
                            <a href="{{ route('semantic.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                <span class="pcoded-mtext text-capitalize font-weight-bold text-primary">Arsip UIS</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @can('arsip_utama_view')
                        <li class=" ">
                            <a href="{{ route('arsiputama.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                <span class="pcoded-mtext text-capitalize">Arsip Utama</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                        @can('sk_kepanitiaan_view')
                        <li class=" ">
                            <a href="{{ route('skkepanitiaan.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                <span class="pcoded-mtext text-capitalize">SK Kepanitiaan</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                        @can('lpj_kepanitiaan_view')
                        <li class=" ">
                            <a href="{{ route('lpjkepanitiaan.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                <span class="pcoded-mtext text-capitalize">LPJ kepanitiaan</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                        @can('kurikulum_view')
                        <li class=" ">
                            <a href="{{ route('kurikulum.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                <span class="pcoded-mtext text-capitalize">kurikulum prodi</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                        @can('pedoman_view')
                        <li class=" ">
                            <a href="{{ route('pedoman.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                <span class="pcoded-mtext text-capitalize">pedoman</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                        @can('sop_akademik_view')
                        <li class=" ">
                            <a href="{{ route('sopakademik.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                <span class="pcoded-mtext text-capitalize">SOP akademik</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                        @can('wasdalbin_view')
                        <li class=" ">
                            <a href="{{ route('wasdalbin.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                <span class="pcoded-mtext text-capitalize">wasdalbin</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
            </ul>
        @endcanany

        @can('artikel_view')
        <ul class="pcoded-item pcoded-left-item">
            <li class="">
                <a href="{{ route('artikel.index') }}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="fa-solid fa-newspaper text-info"></i></span>
                    <span class="pcoded-mtext text-capitalize">portal artikel</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
        </ul>
        @endcan

        @canany(['role_view', 'users_view', 'pegawai_view', 'dosen_view', 'mahasiswa_view', 'jenis_sk_view', 'kategori_arsip_view', 'tahun_akademik_view', 'program_studi_view', 'unit_kerja_view', 'backup_view'])
            <div class="pcoded-navigation-label">Master</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu ">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="fa-solid fa-folder-open"></i><b>A</b></span>
                        <span class="pcoded-mtext">Master Data</span>
                        <span class="pcoded-mcaret"></span>
                    </a>
                    <ul class="pcoded-submenu">
                        @can('role_view')
                        <li class="">
                            <a href="{{ route('role.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="fa-solid fa-users-gear"></i></span>
                                <span class="pcoded-mtext text-capitalize">role akses</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                        @can('users_view')
                         <li class="">
                            <a href="{{ route('users.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="fa-solid fa-users-gear"></i></span>
                                <span class="pcoded-mtext text-capitalize">pengguna</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                        @can('pegawai_view')
                        <li class="">
                            <a href="{{ route('pegawai.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="fa-solid fa-user-gear"></i></span>
                                <span class="pcoded-mtext text-capitalize">pegawai</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                        @can('dosen_view')
                        <li class="">
                            <a href="{{ route('dosen.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="fa-solid fa-user-gear"></i></span>
                                <span class="pcoded-mtext text-capitalize">dosen</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                        @can('mahasiswa_view')
                        <li class="">
                            <a href="{{ route('mahasiswa.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="fa-solid fa-users-gear"></i></span>
                                <span class="pcoded-mtext text-capitalize">mahasiswa</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                        @can('jenis_sk_view')
                        <li class="">
                            <a href="{{ route('jenissk.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="fa-solid fa-users-gear"></i></span>
                                <span class="pcoded-mtext text-capitalize">jenis SK</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                        @can('kategori_arsip_view')
                         <li class="">
                            <a href="{{ route('kategoriarsip.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="fa-solid fa-users-gear"></i></span>
                                <span class="pcoded-mtext text-capitalize">kategori arsip</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                        @can('tahun_akademik_view')
                        <li class="">
                            <a href="{{ route('tahunAkademik.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="fa-solid fa-calendar-days"></i></span>
                                <span class="pcoded-mtext">tahun akademik</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                        @can('program_studi_view')
                        <li class="">
                            <a href="{{ route('programStudi.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="fa-solid fa-folder-closed"></i></span>
                                <span class="pcoded-mtext text-capitalize">program studi</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                        @can('unit_kerja_view')
                        <li class="">
                            <a href="{{ route('unitkerja.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="fa-solid fa-sitemap"></i></span>
                                <span class="pcoded-mtext text-capitalize">unit kerja</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                        @can('backup_view')
                        <li class="">
                            <a href="{{ route('backup.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="fa-solid fa-server"></i></span>
                                <span class="pcoded-mtext text-capitalize">backup database & storage</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
            </ul>
        @endcanany

        @canany(['rekapitulasi_arsip_view', 'rekapitulasi_surat_aktif_view'])
            <div class="pcoded-navigation-label">Rekapitulasi</div>
            <ul class="pcoded-item pcoded-left-item">
                @can('rekapitulasi_arsip_view')
                <li class="">
                    <a href="{{ route('rekapitulasiarsip.index') }}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="fa-regular fa-file-lines"></i></span>
                        <span class="pcoded-mtext text-capitalize">Rekapitulasi Arsip</span>
                        <span class="pcoded-mcaret"></span>
                    </a>
                </li>
                @endcan
                @can('rekapitulasi_surat_aktif_view')
                <li class="">
                    <a href="{{ route('rekapitulasisurataktif.index') }}" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="fa-regular fa-file-lines"></i></span>
                        <span class="pcoded-mtext text-capitalize">Rekapitulasi Surat Aktif</span>
                        <span class="pcoded-mcaret"></span>
                    </a>
                </li>
                @endcan
            </ul>
        @endcanany

        @canany(['users_view', 'role_view'])
            <div class="pcoded-navigation-label">User Guide Admin</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="fa-solid fa-user-gear"></i></span>
                        <span class="pcoded-mtext text-capitalize">Panduan Admin</span>
                        <span class="pcoded-mcaret"></span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="">
                            <a href="{{ route('userGuideTatausaha.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                <span class="pcoded-mtext text-capitalize">Tata Usaha</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('userGuideMahasiswa.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                <span class="pcoded-mtext text-capitalize">Mahasiswa</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('faq.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                <span class="pcoded-mtext text-capitalize">FAQ</span>
                                <span class="pcoded-mcaret"></span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        @endcanany

        <div class="pcoded-navigation-label">User Guide</div>
        <ul class="pcoded-item pcoded-left-item">
            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="fa-solid fa-book"></i></span>
                    <span class="pcoded-mtext text-capitalize">Panduan Pengguna</span>
                    <span class="pcoded-mcaret"></span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="">
                        <a href="{{ route('userGuidePenggunaTatausaha') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext text-capitalize">Tata Usaha</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ route('userGuidePenggunaMahasiswa') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext text-capitalize">Mahasiswa</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ route('userguidepengguna') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext text-capitalize">FAQ</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>

        {{-- <div class="pcoded-navigation-label">Chart And Maps</div> --}}
        {{-- <ul class="pcoded-item pcoded-left-item">
            <li class="">
                <a href="chart-morris.html" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-bar-chart-alt"></i><b>C</b></span>
                    <span class="pcoded-mtext">Charts</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
            <li class="">
                <a href="map-google.html" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-map-alt"></i><b>M</b></span>
                    <span class="pcoded-mtext">Maps</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
        </ul> --}}
        {{-- <div class="pcoded-navigation-label">Pages</div> --}}
        {{-- <ul class="pcoded-item pcoded-left-item">
            <li class="pcoded-hasmenu ">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-id-badge"></i><b>A</b></span>
                    <span class="pcoded-mtext">Pages</span>
                    <span class="pcoded-mcaret"></span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="">
                        <a href="auth-normal-sign-in.html" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Login</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class="">
                        <a href="auth-sign-up.html" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Registration</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class="">
                        <a href="sample-page.html" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-layout-sidebar-left"></i><b>S</b></span>
                            <span class="pcoded-mtext">Sample Page</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul> --}}
    </div>
</nav>
