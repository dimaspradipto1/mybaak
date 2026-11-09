<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Surat Akademik</title>
    <style>
        /* Global Style */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(45deg, #087C39, #FFF742);
            padding: 20px;
            height: 100vh;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        /* Logo */
        .logo {
            width: 100px;
            height: auto;
            margin-bottom: 20px;
        }

        /* Title */
        h1 {
            color: #104819;
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .title {
            font-size: 18px;
            color: #019C24;
            margin-bottom: 30px;
            font-weight: normal;
        }

        /* Details */
        .details {
            font-size: 15px;
            text-align: left;
            color: #333;
            margin-bottom: 25px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .details table {
            width: 100%;
            border-collapse: collapse;
        }

        .details td {
            padding: 8px 4px;
            vertical-align: top;
        }

        .details td:first-child {
            width: 130px;
            font-weight: bold;
            color: #104819;
        }

        .details .colon {
            width: 10px;
            padding-right: 5px;
            color: #104819;
            font-weight: bold;
        }

        .details .value {
            color: #9F1717;
            font-weight: 500;
        }

        /* Button */
        .footer .btn {
            display: inline-block;
            background-color: #087C39;
            color: #fff;
            padding: 12px 30px;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            border-radius: 50px;
            transition: all 0.3s;
        }

        .footer .btn:hover {
            background-color: #104819;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="content">
            <!-- Logo UIS -->
            <img class="logo" src="{{ asset('assets/images/logouis.png') }}" alt="Logo UIS">

            <!-- Title -->
            <h1>UNIVERSITAS IBNU SINA BATAM</h1>

            <!-- Naskah Dinas Title -->
            <h2 class="title">SISTEM INFORMASI BAAK (MYBAAK)</h2>

            <!-- Informasi Surat -->
            <div class="details">
                <div
                    style="text-align: left; border-bottom: 2px solid #019C24; margin-bottom: 15px; padding-bottom: 8px;">
                    <strong style="font-size: 16px;">Naskah telah ditandatangani oleh:</strong>
                </div>
                <table>
                    <tr>
                        <td>Nama</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $kabaak->nama_staff ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Jabatan</td>
                        <td class="colon">:</td>
                        <td class="value">Kepala Biro Administrasi Akademik Kemahasiswaan (BAAK)</td>
                    </tr>
                    <tr>
                        <td>Unit Kerja</td>
                        <td class="colon">:</td>
                        <td class="value">Biro Administrasi Akademik dan Kemahasiswaan</td>
                    </tr>
                    <tr>
                        <td>Instansi</td>
                        <td class="colon">:</td>
                        <td class="value">Rektorat Universitas Ibnu Sina Batam</td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td class="colon">:</td>
                        <td class="value">
                            {{ \Carbon\Carbon::parse($suratAkademik->created_at)->locale('id')->translatedFormat('d F Y') }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Informasi BAUK -->
            <div class="details">
                <div
                    style="text-align: left; border-bottom: 2px solid #019C24; margin-bottom: 15px; padding-bottom: 8px;">
                    <strong style="font-size: 16px;">Juga disetujui oleh:</strong>
                </div>
                <table>
                    <tr>
                        <td>Nama</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $kabauk->nama_staff ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Jabatan</td>
                        <td class="colon">:</td>
                        <td class="value">Kepala Biro Administrasi Umum dan Keuangan</td>
                    </tr>
                </table>
            </div>

            <!-- Button untuk melihat detail -->
            <div class="footer">
                <button onclick="window.location.href='{{ route('suratAkademik.show', $suratAkademik) }}'" class="btn">LIHAT DOKUMEN ASLI</button>
            </div>
        </div>
    </div>
</body>

</html>
