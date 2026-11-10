<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Aktif</title>
    <style>
        @page {
            margin: 2cm 2cm 2cm 2cm;
            size: A4 portrait;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            margin: 0;
            padding: 0;
            font-size: 12pt;
            line-height: 1.4;
            color: #000;
        }
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .kop-table td {
            vertical-align: middle;
        }
        .kop-logo {
            width: 85px;
            text-align: center;
        }
        .kop-logo img {
            width: 80px;
            height: auto;
        }
        .kop-text {
            text-align: center;
        }
        .kop-text h1 {
            font-size: 13pt;
            font-weight: bold;
            margin: 0;
            color: #006600;
        }
        .kop-text h2 {
            font-size: 18pt;
            font-weight: bold;
            margin: 2px 0;
            color: #006600;
        }
        .kop-text p {
            font-size: 9pt;
            margin: 1px 0;
            color: #000;
        }
        .line-thick {
            border-top: 3px solid #006600;
            margin-top: 4px;
            margin-bottom: 2px;
        }
        .line-thin {
            border-top: 1px solid #006600;
            margin-bottom: 20px;
        }
        .title-section {
            text-align: center;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .title-section h3 {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0 0 4px 0;
        }
        .title-section p {
            font-size: 11pt;
            margin: 0;
        }
        .content {
            text-align: justify;
            margin-bottom: 12px;
            font-size: 11pt;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-left: 20px;
            margin-bottom: 15px;
            font-size: 11pt;
        }
        .details-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .details-table td.label {
            width: 180px;
        }
        .details-table td.colon {
            width: 15px;
            text-align: center;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .signature-table td {
            vertical-align: top;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('assets/images/logouis.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }
        $tahun = $suratAktif->created_at ? $suratAktif->created_at->format('Y') : date('Y');
    @endphp

    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo UIS">
                @endif
            </td>
            <td class="kop-text">
                <h1>YAYASAN PENDIDIKAN IBNU SINA BATAM (YAPISTA)</h1>
                <h2>UNIVERSITAS IBNU SINA (UIS)</h2>
                <p>Jalan Teuku Umar, Lubuk Baja Kota Batam Indonesia Telp. 0778 425391</p>
                <p>Email: info@uis.ac.id/ibnusina@gmail.com | Website: uis.ac.id</p>
            </td>
        </tr>
    </table>
    <div class="line-thick"></div>
    <div class="line-thin"></div>

    <div class="title-section">
        <h3>SURAT KETERANGAN</h3>
        <p>No: {{ str_pad($no_surat, 3, '0', STR_PAD_LEFT) }}/UIS.B1/LL/{{ $bulanRomawi }}/{{ $tahun }}</p>
    </div>

    <div class="content">
        Yang bertanda tangan di bawah ini:
    </div>

    <table class="details-table">
        <tr>
            <td class="label">Nama</td>
            <td class="colon">:</td>
            <td><strong>{{ $pegawai->nama_staff ?? 'Kepala BAAK' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td class="colon">:</td>
            <td>Kepala Biro Administrasi Akademik dan Kemahasiswaan</td>
        </tr>
    </table>

    <div class="content">
        Dengan ini menerangkan bahwa:
    </div>

    <table class="details-table">
        <tr>
            <td class="label">Nama Mahasiswa</td>
            <td class="colon">:</td>
            <td><strong>{{ $suratAktif->users->name ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Tempat/Tgl Lahir</td>
            <td class="colon">:</td>
            <td>{{ $suratAktif->tempat_lahir ?? '-' }}, {{ $suratAktif->tgl_lahir ? \Carbon\Carbon::parse($suratAktif->tgl_lahir)->translatedFormat('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">NPM</td>
            <td class="colon">:</td>
            <td>{{ $suratAktif->npm }}</td>
        </tr>
        <tr>
            <td class="label">Program Studi</td>
            <td class="colon">:</td>
            <td>{{ $program_studi }}</td>
        </tr>
        <tr>
            <td class="label">Jenjang Pendidikan</td>
            <td class="colon">:</td>
            <td>{{ $suratAktif->jenjang_pendidikan ?? 'S-1' }}</td>
        </tr>
    </table>

    <div class="content">
        Adalah benar mahasiswa {{ $suratAktif->fakultas ?? 'Universitas Ibnu Sina' }} yang aktif dalam perkuliahan dan kegiatan akademik pada Semester {{ $suratAktif->status_semester ?? ($suratAktif->semester ?? '-') }} Tahun Akademik {{ $suratAktif->tahun_akademik ?? '-' }}.
    </div>

    <div class="content">
        Demikian surat keterangan ini dibuat dengan sesungguhnya untuk dipergunakan sebagaimana mestinya.
    </div>

    <table class="signature-table">
        <tr>
            <td style="width: 55%;"></td>
            <td style="width: 45%; text-align: center;">
                Batam, {{ \Carbon\Carbon::parse($suratAktif->created_at)->translatedFormat('d F Y') }}<br>
                Universitas Ibnu Sina<br>
                Ka. BAAK,<br>
                <br><br><br><br>
                <strong><u>{{ $pegawai->nama_staff ?? 'Kepala BAAK' }}</u></strong><br>
                NIDN/NIP: {{ $pegawai->nip ?? ($pegawai->nidn ?? '-') }}
            </td>
        </tr>
    </table>
</body>
</html>

