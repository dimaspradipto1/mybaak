<?php

namespace App\Http\Controllers;

use App\Models\ArsipUtama;
use App\Models\Artikel;
use App\Models\Kurikulum;
use App\Models\LpjKepanitiaan;
use App\Models\Pedoman;
use App\Models\SkKepanitiaan;
use App\Models\SopAkademik;
use App\Models\Wasdalbin;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\User;
use App\Models\SuratAktif;
use App\Models\SuratAkademik;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class SemanticArsipController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Semantic Search Arsip';
        $query = $request->input('q');
        $tab = $request->input('tab', 'semua');
        $results = collect();
        $top_result = null;

        if ($query) {
            // Pecah query menjadi kata-kata (minimal 2 karakter)
            $words = array_filter(explode(' ', trim($query)), function ($w) {
                return strlen(trim($w)) >= 2;
            });

            // Buat suku kata (substring 3+ karakter)
            $syllables = [];
            foreach ($words as $word) {
                $len = strlen($word);
                for ($i = 0; $i < $len - 2; $i++) {
                    $suku = substr($word, $i, 3);
                    if (strlen($suku) == 3) {
                        $syllables[] = $suku;
                    }
                }
            }
            $syllables = array_unique($syllables);

            // Helper: bangun closure pencarian fleksibel (kata + suku kata)
            $buildSearch = function ($q, $fields) use ($query, $words, $syllables) {
                // Full query match
                foreach ($fields as $field) {
                    $q->orWhere($field, 'like', "%{$query}%");
                }
                // Per kata
                foreach ($words as $word) {
                    if (strlen($word) >= 2) {
                        foreach ($fields as $field) {
                            $q->orWhere($field, 'like', "%{$word}%");
                        }
                    }
                }
                // Per suku kata (hanya untuk 3+ karakter)
                foreach ($syllables as $suku) {
                    if (strlen($suku) >= 3) {
                        foreach ($fields as $field) {
                            $q->orWhere($field, 'like', "%{$suku}%");
                        }
                    }
                }
                return $q;
            };

            $flexibleQuery = '%' . str_replace(' ', '%', $query) . '%';

            // Helper to get Google Drive Thumbnail if available
            $getThumbnail = function ($url) {
                if ($url && preg_match('/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
                    return "https://drive.google.com/thumbnail?id=" . $matches[1] . "&sz=w600";
                }
                return null;
            };

            // 1. Search in Artikel
            $artikelItems = Artikel::with(['user.role'])
                ->where(function ($q) use ($buildSearch, $query, $flexibleQuery) {
                    $buildSearch($q, ['judul', 'tipe', 'konten', 'keyword']);
                    $q->orWhere('judul', 'like', $flexibleQuery);
                    $q->orWhereHas('user', function ($sq) use ($query) {
                        $sq->where('name', 'like', "%{$query}%");
                    });
                })
                ->get()
                ->map(function ($item) {
                    $item->type = 'Artikel';
                    $item->title = $item->judul;

                    $plain_text = trim(strip_tags($item->konten));
                    if (empty($plain_text)) {
                        $source = 'portal berita';
                        if ($item->is_youtube) {
                            $source = 'saluran YouTube';
                        }
                        if ($item->is_facebook) {
                            $source = 'halaman Facebook';
                        }
                        $item->description = "Lihat informasi lengkap mengenai {$item->title} yang tersedia di {$source} resmi Universitas Ibnu Sina. Akses arsip digital untuk melihat detail publikasi ini.";
                    } else {
                        $item->description = Str::limit($plain_text, 170, '...');
                    }

                    $item->link = route('artikel.show', $item->id);
                    $item->icon = 'fa-newspaper';
                    $item->color = 'text-info';
                    $item->thumbnail = $item->gambar ? asset('storage/' . $item->gambar) : null;

                    $item->is_youtube = false;
                    $item->is_facebook = false;

                    if ($item->media_url && preg_match('/(youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $item->media_url, $matches)) {
                        $item->is_youtube = true;
                        $item->youtube_id = $matches[2];
                        $item->thumbnail = "https://img.youtube.com/vi/{$item->youtube_id}/mqdefault.jpg";
                        $item->link = $item->media_url;
                        $item->icon = 'fa-brands fa-youtube';
                        $item->color = 'text-danger';
                    } elseif ($item->media_url && str_contains($item->media_url, 'facebook.com')) {
                        $item->is_facebook = true;
                        $item->link = $item->media_url;
                        $item->icon = 'fa-brands fa-facebook';
                        $item->color = 'text-primary';
                    }

                    $item->is_map = strpos($item->media_url ?? '', 'maps') !== false || strpos($item->media_url ?? '', 'goo.gl/maps') !== false;
                    $item->external_link = $item->media_url;

                    return $item;
                });

            // 2. Search in Arsip Utama
            $arsipUtama = ArsipUtama::with(['user.role', 'kategoriArsip'])
                ->where(function ($q) use ($buildSearch, $query, $words) {
                    $buildSearch($q, ['nama_arsip', 'tahun_arsip', 'file_arsip']);
                    $q->orWhereHas('kategoriArsip', function ($sq) use ($query, $words) {
                        $sq->where('kategori_arsip', 'like', "%{$query}%");
                        foreach ($words as $word) {
                            $sq->orWhere('kategori_arsip', 'like', "%{$word}%");
                        }
                    });
                    $q->orWhereHas('user', function ($sq) use ($query) {
                        $sq->where('name', 'like', "%{$query}%");
                    });
                })
                ->get()
                ->map(function ($item) use ($getThumbnail) {
                    $item->type = 'Arsip Utama';
                    $item->title = $item->nama_arsip;
                    $item->description = 'Dokumen ini merupakan arsip digital yang terdaftar dalam kategori ' . ($item->kategoriArsip->kategori_arsip ?? 'Umum') . '. Berkas ini disimpan untuk keperluan administrasi dan akreditasi internal UIS.';
                    $item->link = $item->file_arsip;
                    $item->icon = 'fa-file-pdf';
                    $item->color = 'text-danger';
                    $item->thumbnail = $getThumbnail($item->file_arsip);
                    return $item;
                });

            // 3. Search in SK Kepanitiaan
            $sk = SkKepanitiaan::with(['users.role', 'jenissk'])
                ->where(function ($q) use ($buildSearch, $query, $words) {
                    $buildSearch($q, ['nama_dokumen', 'nomor_sk', 'semester', 'fakultas', 'file']);
                    $q->orWhereHas('jenissk', function ($sq) use ($query, $words) {
                        $sq->where('jenis_sk', 'like', "%{$query}%");
                        foreach ($words as $word) {
                            $sq->orWhere('jenis_sk', 'like', "%{$word}%");
                        }
                    });
                    $q->orWhereHas('users', function ($sq) use ($query) {
                        $sq->where('name', 'like', "%{$query}%");
                    });
                })
                ->get()
                ->map(function ($item) use ($getThumbnail) {
                    $item->type = 'SK Kepanitiaan';
                    $item->title = $item->nama_dokumen . ($item->nomor_sk ? ' (' . $item->nomor_sk . ')' : '');
                    $item->description = 'Salinan resmi Dokumen SK Kepanitiaan' . ($item->nomor_sk ? ' dengan nomor registrasi ' . $item->nomor_sk : '') . '. Dokumen ini telah diverifikasi dan tersimpan dalam pangkalan data arsip digital UIS.';
                    $item->link = $item->file;
                    $item->icon = 'fa-file-contract';
                    $item->color = 'text-primary';
                    $item->thumbnail = $getThumbnail($item->file);
                    return $item;
                });

            // 4. Search in LPJ Kepanitiaan
            $lpj = LpjKepanitiaan::with(['users.role'])
                ->where(function ($q) use ($buildSearch, $query) {
                    $buildSearch($q, ['nama_dokumen', 'ketua', 'sekretaris', 'semester', 'fakultas', 'file']);
                    $q->orWhereHas('users', function ($sq) use ($query) {
                        $sq->where('name', 'like', "%{$query}%");
                    });
                })
                ->get()
                ->map(function ($item) use ($getThumbnail) {
                    $item->type = 'LPJ Kepanitiaan';
                    $item->title = $item->nama_dokumen;
                    $item->description = 'Laporan Pertanggungjawaban (LPJ) kegiatan' . ($item->ketua ? '. Ketua pelaksana: ' . $item->ketua : '') . '.';
                    $item->link = $item->file;
                    $item->icon = 'fa-file-invoice';
                    $item->color = 'text-success';
                    $item->thumbnail = $getThumbnail($item->file);
                    return $item;
                });

            // 5. Search in Kurikulum
            $kurikulum = Kurikulum::with(['user.role'])
                ->where(function ($q) use ($buildSearch, $query) {
                    $buildSearch($q, ['nama_kurikulum', 'tahun', 'fakultas', 'file']);
                    $q->orWhereHas('user', function ($sq) use ($query) {
                        $sq->where('name', 'like', "%{$query}%");
                    });
                })
                ->get()
                ->map(function ($item) use ($getThumbnail) {
                    $item->type = 'Kurikulum';
                    $item->title = $item->nama_kurikulum;
                    $item->description = 'Dokumen Kurikulum Prodi ' . ($item->fakultas ? ' - ' . $item->fakultas : '') . ($item->tahun ? ' (' . $item->tahun . ')' : '') . '. Tersimpan dalam pangkalan data kurikulum akademik UIS.';
                    $item->link = $item->file;
                    $item->icon = 'fa-book';
                    $item->color = 'text-info';
                    $item->thumbnail = $getThumbnail($item->file);
                    return $item;
                });

            // 6. Search in Pedoman
            $pedoman = Pedoman::with(['users.role'])
                ->where(function ($q) use ($buildSearch, $query) {
                    $buildSearch($q, ['nama_pedoman', 'tahun', 'fakultas', 'file']);
                    $q->orWhereHas('users', function ($sq) use ($query) {
                        $sq->where('name', 'like', "%{$query}%");
                    });
                })
                ->get()
                ->map(function ($item) use ($getThumbnail) {
                    $item->type = 'Pedoman';
                    $item->title = $item->nama_pedoman;
                    $item->description = 'Dokumen Pedoman' . ($item->fakultas ? ' ' . $item->fakultas : '') . ($item->tahun ? ' tahun ' . $item->tahun : '') . '. Tersimpan resmi dalam arsip pedoman akademis dan operasional UIS.';
                    $item->link = $item->file;
                    $item->icon = 'fa-book-open';
                    $item->color = 'text-warning';
                    $item->thumbnail = $getThumbnail($item->file);
                    return $item;
                });

            // 7. Search in SOP
            $sop = SopAkademik::with(['users.role'])
                ->where(function ($q) use ($buildSearch, $query) {
                    $buildSearch($q, ['nama_sop', 'fakultas', 'file']);
                    $q->orWhereHas('users', function ($sq) use ($query) {
                        $sq->where('name', 'like', "%{$query}%");
                    });
                })
                ->get()
                ->map(function ($item) use ($getThumbnail) {
                    $item->type = 'SOP Akademik';
                    $item->title = $item->nama_sop;
                    $item->description = 'Standar Operasional Prosedur (SOP) Akademik' . ($item->fakultas ? ' ' . $item->fakultas : '') . '. Panduan acuan standar prosedur operasional di lingkungan UIS.';
                    $item->link = $item->file;
                    $item->icon = 'fa-file-code';
                    $item->color = 'text-secondary';
                    $item->thumbnail = $getThumbnail($item->file);
                    return $item;
                });

            // 8. Search in Wasdalbin
            $wasdalbin = Wasdalbin::with(['users.role'])
                ->where(function ($q) use ($buildSearch, $query) {
                    $buildSearch($q, ['nama_wasdalbin', 'tahun', 'fakultas', 'file']);
                    $q->orWhereHas('users', function ($sq) use ($query) {
                        $sq->where('name', 'like', "%{$query}%");
                    });
                })
                ->get()
                ->map(function ($item) use ($getThumbnail) {
                    $item->type = 'Wasdalbin';
                    $item->title = $item->nama_wasdalbin;
                    $item->description = 'Dokumen Wasdalbin (Pengawasan, Pengendalian, dan Pembinaan)' . ($item->fakultas ? ' ' . $item->fakultas : '') . ($item->tahun ? ' tahun ' . $item->tahun : '') . '.';
                    $item->link = $item->file;
                    $item->icon = 'fa-shield-halved';
                    $item->color = 'text-dark';
                    $item->thumbnail = $getThumbnail($item->file);
                    return $item;
                });

            // 9. Search in Surat Aktif
            $suratAktif = SuratAktif::with(['users', 'programStudi'])
                ->where(function ($q) use ($buildSearch, $query) {
                    $buildSearch($q, ['no_surat', 'npm', 'semester', 'status_semester', 'tahun_akademik', 'tempat_lahir', 'jenjang_pendidikan', 'fakultas']);
                    $q->orWhereHas('users', function ($sq) use ($query) {
                        $sq->where('name', 'like', "%{$query}%");
                    });
                    $q->orWhereHas('programStudi', function ($sq) use ($query) {
                        $sq->where('program_studi', 'like', "%{$query}%");
                    });
                })
                ->get()
                ->map(function ($item) use ($getThumbnail) {
                    $item->type = 'Surat Aktif';
                    $noUrut = sprintf('%03d', (int) ($item->no_surat ?? $item->id));
                    $item->title = "Surat Keterangan Aktif (No. {$noUrut})";
                    $item->description = "Layanan surat keterangan aktif kuliah untuk mahasiswa " . ($item->users->name ?? '-') . ". NPM: " . $item->npm . ($item->file ? ' (Arsip Digital Tersedia di Google Drive)' : '');
                    $item->link = $item->file ?: route('suratAktif.index', ['q' => $item->npm]);
                    $item->icon = 'fa-file-signature';
                    $item->color = 'text-success';
                    $item->thumbnail = $item->file ? $getThumbnail($item->file) : null;
                    return $item;
                });

            // 10. Search in Surat Akademik
            $suratAkademik = SuratAkademik::with(['user', 'programStudi'])
                ->where(function ($q) use ($buildSearch, $query) {
                    $buildSearch($q, ['no_surat', 'npm', 'permohonan', 'semester', 'status_cuti', 'alasan_cuti', 'alamat', 'no_wa']);
                    $q->orWhereHas('user', function ($sq) use ($query) {
                        $sq->where('name', 'like', "%{$query}%");
                    });
                    $q->orWhereHas('programStudi', function ($sq) use ($query) {
                        $sq->where('program_studi', 'like', "%{$query}%");
                    });
                })
                ->get()
                ->map(function ($item) use ($getThumbnail) {
                    $item->type = 'Surat Akademik';
                    $noUrut = sprintf('%03d', (int) ($item->no_surat ?? $item->id));
                    $item->title = "Surat Layanan Akademik (No. {$noUrut} - " . ($item->permohonan ?? 'Pengajuan') . ")";
                    $item->description = "Dokumen layanan akademik mahasiswa " . ($item->user->name ?? '-') . ". NPM: " . $item->npm . ($item->file ? ' (Arsip Digital Tersedia di Google Drive)' : '');
                    $item->link = $item->file ?: route('suratAkademik.index', ['q' => $item->npm]);
                    $item->icon = 'fa-file-lines';
                    $item->color = 'text-primary';
                    $item->thumbnail = $item->file ? $getThumbnail($item->file) : null;
                    return $item;
                });

            $results = $results->concat($artikelItems)
                ->concat($arsipUtama)
                ->concat($sk)
                ->concat($lpj)
                ->concat($kurikulum)
                ->concat($pedoman)
                ->concat($sop)
                ->concat($wasdalbin)
                ->concat($suratAktif)
                ->concat($suratAkademik);

            // Relevance scoring function
            $calculateScore = function ($item) use ($query, $words) {
                $score = 0;
                $title = strtolower($item->title ?? '');
                $desc = strtolower($item->description ?? '');
                $type = strtolower($item->type ?? '');
                $queryLower = strtolower(trim($query));

                // Title exact match
                if ($title === $queryLower) {
                    $score += 200;
                }

                // Title contains full query
                if (str_contains($title, $queryLower)) {
                    $score += 100;
                }

                // Title contains individual query words
                foreach ($words as $w) {
                    $wLower = strtolower($w);
                    if (!empty($wLower)) {
                        if (str_contains($title, $wLower)) {
                            $score += 30;
                        }
                        if (str_contains($desc, $wLower)) {
                            $score += 10;
                        }
                    }
                }

                // Type match with query words (e.g. searching 'pedoman' boosts type 'Pedoman')
                if (!empty($type) && str_contains($queryLower, $type)) {
                    $score += 60;
                }

                // Substring in description/file
                if (str_contains($desc, $queryLower)) {
                    $score += 40;
                }

                return $score;
            };

            // Calculate relevance score and sort descending
            $results = $results->map(function ($item) use ($calculateScore) {
                $item->score = $calculateScore($item);
                return $item;
            })->sortByDesc('score')->values();

            // Set top_result to highest scoring article or document if score > 0
            if ($results->count() > 0 && ($results->first()->score ?? 0) > 0) {
                $top_result = $results->first();
            }

            // Filter logic based on tab
            if ($tab == 'terbaru') {
                $results = $results->sortByDesc('created_at');
            } elseif ($tab == 'gambar') {
                $results = $results->whereNotNull('thumbnail')->where('thumbnail', '!=', '');
            } elseif ($tab == 'video') {
                $results = $results->filter(function ($item) {
                    return (isset($item->is_youtube) && $item->is_youtube) || (isset($item->is_facebook) && $item->is_facebook);
                });
            } elseif ($tab == 'berita') {
                $results = $results->filter(function ($item) {
                    return $item->type == 'Artikel' && in_array($item->kategori, ['Berita', 'Informasi', 'Pengumuman', 'Tutorial']);
                });
            }

            // PAGINATION LOGIC
            $perPage = 10;
            $currentPage = request()->input('page', 1);
            $currentItems = $results->slice(($currentPage - 1) * $perPage, $perPage)->all();

            $results = new LengthAwarePaginator(
                $currentItems,
                $results->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        return view('pages.semantic.index', compact('title', 'results', 'query', 'tab', 'top_result'));
    }
}
