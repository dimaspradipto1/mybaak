<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Layanan Akademik</title>
    <style>
        @page {
            margin: 1.8cm 2cm 1.8cm 2cm;
            size: A4 portrait;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            margin: 0;
            padding: 0;
            font-size: 11pt;
            line-height: 1.35;
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
            margin-bottom: 15px;
        }
        .title-section {
            text-align: center;
            margin-bottom: 15px;
        }
        .title-section h3 {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0 0 3px 0;
        }
        .title-section p {
            font-size: 10.5pt;
            margin: 0;
        }
        .content {
            text-align: justify;
            margin-bottom: 10px;
            font-size: 10.5pt;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-left: 15px;
            margin-bottom: 12px;
            font-size: 10.5pt;
        }
        .details-table td {
            padding: 2.5px 0;
            vertical-align: top;
        }
        .details-table td.label {
            width: 190px;
        }
        .details-table td.colon {
            width: 15px;
            text-align: center;
        }
        .lampiran-list {
            margin-top: 4px;
            margin-bottom: 10px;
            padding-left: 20px;
            font-size: 10.5pt;
        }
        .signature-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .signature-table td {
            vertical-align: top;
            text-align: center;
            font-size: 10.5pt;
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
        $tahun = $suratAkademik->created_at ? $suratAkademik->created_at->format('Y') : date('Y');
        $noSuratFormatted = sprintf('%03d', (int) ($suratAkademik->no_surat ?? $suratAkademik->id));
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
        <h3>FORMULIR LAYANAN AKADEMIK</h3>
        <p>No: {{ $noSuratFormatted }}/UIS.B1/LA/{{ $bulanRomawi }}/{{ $tahun }}</p>
    </div>

    <div class="content">
        Yang bertanda tangan di bawah ini:
    </div>

    <table class="details-table">
        <tr>
            <td class="label">Nama Mahasiswa</td>
            <td class="colon">:</td>
            <td><strong>{{ $suratAkademik->user->name ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td class="label">NPM</td>
            <td class="colon">:</td>
            <td>{{ $suratAkademik->npm }}</td>
        </tr>
        <tr>
            <td class="label">Semester</td>
            <td class="colon">:</td>
            <td>{{ $suratAkademik->semester }}</td>
        </tr>
        <tr>
            <td class="label">Program Studi</td>
            <td class="colon">:</td>
            <td>{{ $suratAkademik->programStudi->program_studi ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Fakultas</td>
            <td class="colon">:</td>
            <td>{{ $fakultas ?? ($suratAkademik->mahasiswa->fakultas ?? '-') }}</td>
        </tr>
        <tr>
            <td class="label">Status Cuti</td>
            <td class="colon">:</td>
            <td>{{ $suratAkademik->status_cuti ?? 'Belum Pernah Cuti' }}</td>
        </tr>
        <tr>
            <td class="label">Alamat / No. HP</td>
            <td class="colon">:</td>
            <td>{{ $suratAkademik->alamat ?? '-' }} / {{ $suratAkademik->no_wa ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Mengajukan Permohonan</td>
            <td class="colon">:</td>
            <td><strong>{{ $suratAkademik->permohonan }}</strong></td>
        </tr>
        <tr>
            <td class="label">Alasan Permohonan / Cuti</td>
            <td class="colon">:</td>
            <td>{{ $suratAkademik->alasan_cuti ?? '-' }}</td>
        </tr>
    </table>

    <div class="content">
        Sebagai bahan pertimbangan bersama ini kami lampirkan:
    </div>
    <ol class="lampiran-list">
        <li>Surat Pernyataan Mahasiswa Cuti / Pindah Prodi / Pindah Kelas</li>
        <li>KHS Terakhir</li>
        <li>Surat Keterangan lain yang relevan (Surat Keterangan Sakit, Bekerja, dll)</li>
    </ol>

    <div class="content">
        Atas perhatian dan kerja sama yang baik, kami ucapkan terima kasih.
    </div>

    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                Mengetahui/Menyetujui,<br>
                Pembimbing Akademik<br>
                <br><br><br>
                <strong><u>{{ $dosen->nama_dosen ?? 'Dosen Pembimbing' }}</u></strong><br>
                NIDN: {{ $dosen->nidn ?? '-' }}
            </td>
            <td style="width: 50%;">
                Batam, {{ \Carbon\Carbon::parse($suratAkademik->created_at)->translatedFormat('d F Y') }}<br>
                Pemohon,<br>
                <br><br><br>
                <strong><u>{{ $suratAkademik->user->name ?? '-' }}</u></strong><br>
                NPM: {{ $suratAkademik->npm }}
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding-top: 25px; text-align: center;">
                Mengetahui,<br>
                Ketua Program Studi<br>
                <br><br><br>
                <strong><u>{{ $kaprodi->nama_dosen ?? ($kaprodi->nama_staff ?? 'Ketua Program Studi') }}</u></strong><br>
                NIDN/NIP: {{ $kaprodi->nidn ?? ($kaprodi->nip ?? '-') }}
            </td>
        </tr>
    </table>
</body>
</html>

