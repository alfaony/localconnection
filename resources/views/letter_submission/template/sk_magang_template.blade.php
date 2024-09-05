<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perjanjian Kerja Magang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }


        .header, .sub-header {
            text-align: center;
        }

        .header {
            font-size: 13px;
            font-weight: bold;
        }

        .sub-header {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .info-table th, .info-table td {
            padding: 10px;
        }

        .section-title {
            font-weight: bold;
            margin-top: 20px;
            font-size: 13px;
        }

        .info-section ul {
            padding-left: 20px;
        }

        .info-section ul li {
            font-size: 12px;
        }
        .signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-block {
            width: 40%;
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .container {
                border: none;
                box-shadow: none;
            }

            .print-hide {
                display: none;
            }
            
            p {
                font-size: 12px;
            }

            li {
                font-size: 12px;
            }

            .body-container {
                padding-left: 30px;
                padding-right: 30px;
            }

            .th
            {
                font-size: 12px;
                font-weight: bold;
            }
            .td
            {
                font-size: 12px;
            }
        }
    </style>
    <!-- Header Section -->
    <style>
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
<div class="body-container">
    <!-- Header -->
    <div class="header">
        PERJANJIAN KERJA MAGANG<br>
        PT. GEMA TEKNOLOGI CAHAYA GEMILANG
    </div>

    <p>Pada Hari Senin, 12 Agustus 2024 bertempat di Jakarta, telah ditandatangani perjanjian internship antara :</p>


    <!-- First Party Information --> 
    <table class="table border">
        <tr>
            <th>Nama</th>
            <td>{{ $company['name'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Penanggung Jawab</th>
            <td>{{ $company['director'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Alamat</th>
            <td>{{ $company['address'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Nama</th>
            <td>{{ $letterSubmission->user->name ?? ''}}</td>
        </tr>
        <tr>
            <th>No KTP</th>
            <td>{{ $letterSubmission->user->id_card ?? ''}}</td>
        </tr>
        <tr>
            <th>Alamat</th>
            <td>
                {{ $letterSubmission->user->address ?? ''}}
            </td>
        </tr>
    </table>

    <!-- Agreement Sections -->
    <div class="section-title">PASAL 1. MAKSUD DAN TUJUAN</div>
    <p>PARA PIHAK sepakat untuk menjalin hubungan kerja magang, dimana PIHAK PERTAMA memberikan kesempatan magang kepada PIHAK KEDUA sebagai UI/UX Designer.</p>

    <div class="section-title">PASAL 2. RUANG LINGKUP</div>
    <p>PIHAK PERTAMA menunjuk PIHAK KEDUA untuk melakukan pekerjaan magang sesuai kebutuhan PIHAK PERTAMA dalam melakukan operasional dan fungsi perusahaan terkait desain antarmuka pengguna dan pengalaman pengguna.</p>

    <div class="section-title">PASAL 3. HAK & KEWAJIBAN PARA PIHAK</div>
    <p>PARA PIHAK sepakat dalam menjalankan hubungan kerja ini dengan hak dan kewajiban sebagai berikut ini:</p>
    <ul>
        <li>PIHAK KEDUA berhak menerima kompensasi atau tunjangan magang sesuai kesepakatan.</li>
        <li>PIHAK KEDUA berhak mendapatkan bimbingan dan pelatihan dari PIHAK PERTAMA.</li>
        <li>PIHAK KEDUA wajib mematuhi seluruh peraturan yang ditetapkan PIHAK PERTAMA.</li>
        <li>PIHAK KEDUA wajib menjalankan tugas dan pekerjaan dengan bertanggung jawab, dan memberikan hasil yang maksimal.</li>
        <li>PIHAK KEDUA wajib merahasiakan seluruh informasi perusahaan yang diterima maupun diketahui, yang dapat merugikan PIHAK PERTAMA apabila diketahui pihak lain.</li>
        <li>PIHAK PERTAMA berhak menetapkan peraturan perusahaan, strategi perusahaan, tugas dan tanggung jawab yang diberikan kepada PIHAK KEDUA.</li>
        <li>PIHAK PERTAMA berhak mendapatkan perlindungan dan jaminan dari tindakan kecurangan, pencurian, dan penipuan.</li>
    </ul>

    <div class="section-title">PASAL 4. TANGGUNG JAWAB HUKUM</div>
    <p>PARA PIHAK sepakat bahwa segala tindakan yang menyebabkan kerugian pada perusahaan harus dipertanggungjawabkan walaupun hubungan magang telah berakhir.</p>

    <div class="section-title">PASAL 5. JAM KERJA & CARA BEKERJA</div>
    <ul>
        <li>PIHAK KEDUA wajib bekerja 5 hari dalam seminggu dari pukul 08.00 – 17.00.</li>
        <li>Pelaporan aktivitas magang dilakukan dalam bentuk tertulis dan terdokumentasi.</li>
    </ul>

    <div class="section-title">PASAL 6. JANGKA WAKTU</div>
    <p>Perjanjian ini berlaku selama 3 bulan dan dapat diperpanjang sesuai kesepakatan kedua belah pihak.</p>

    <!-- Signatures -->
    <div class="signatures">
        <div class="signature-block">
            <p><strong>PIHAK PERTAMA</strong></p>
            @if($letterSubmission->is_approved == 1)
            <img src="{{ asset('logo/paraf.png') }}" class="img-fluid" alt="Signature" style="with:auto; height:150px">
            <p>{{ $company['director'] ?? "" }}</p>
            @endif
        </div>
        <div class="signature-block">
            <p><strong>PIHAK KEDUA</strong></p>
            @if($letterSubmission->is_approved == 1)
            <img src="{{ Storage::url($letterSubmission->user->signature) }}" class="img-fluid" alt="Signature" style="with:auto; height:150px">
            <p>{{ $letterSubmission->user->name }}</p>
            @endif
        </div>
    </div>
    <div class="page-break"></div>
    <div class="header">
        SURAT KEPUTUSAN MANAJEMEN<br>

    </div>
    <div class="sub-header">
        Perihal: Penerimaan Magang
    </div>
    
    <!-- Information Table -->
    <table class="info-table">
        <tr>
            <th>Nama Lengkap</th>
            <td>Raihan Alfianto</td>
        </tr>
        <tr>
            <th>Jabatan / Fungsi / Keahlian</th>
            <td>UI/UX Designer Magang</td>
        </tr>
        <tr>
            <th>Kompensasi Magang</th>
            <td>Rp 1.000.000 /bulan</td>
        </tr>
        <tr>
            <th>Tanggal Pembayaran Kompensasi Magang</th>
            <td>Tanggal 20 Cut Off</td>
        </tr>
        <tr>
            <th>Jam Kerja</th>
            <td>Full Time - 5 Hari Kerja - 40 Jam Seminggu</td>
        </tr>
        <tr>
            <th>Penempatan</th>
            <td>PT. Gema Teknologi Cahaya Gemilang</td>
        </tr>
    </table>
    
    <p>
        Perjanjian ini berlaku efektif sejak ditandatangani, dan peserta magang akan menjalankan peran sebagai UI/UX Designer selama masa magang. Peserta magang tunduk pada ketentuan yang berlaku dalam perusahaan serta aturan dan tanggung jawab yang telah ditetapkan.
    </p>
    
    <p><strong>Fungsi Manajemen: UI/UX Designer</strong></p>
    <p><strong>Tanggung Jawab Pekerjaan:</strong></p>
    <ol>
        <li>Mendesain dan mengembangkan mockup dan prototype UI/UX.</li>
        <li>Melakukan uji coba usability dan mengumpulkan feedback pengguna.</li>
        <li>Berkolaborasi dengan tim pengembangan untuk mengimplementasikan desain.</li>
        <li>Memastikan desain konsisten dengan panduan merek perusahaan.</li>
        <li>Membantu membuat asset image di website yang dibangun.</li>
    </ol>
    <!-- Footer -->
    <div class="text-left mb-">
        <p>Jakarta, {{ $date ?? "" }}</p>
    </div>

    <!-- KTP and Signature Section -->
    <div class="row photo-ktp">
        <div class="col">
            <strong>Eddy Yansen</strong><br>
        </div>
        <div class="col">
            <strong>Raihan Alfianto</strong>
        </div>
    </div>

    <!-- No KTP and NPWP -->
    <div class="mt-3">
        <table class="table table-bordered info-table">
            <tr>
                <td>
                    Foto KTP
                </td>
                <td>
                    <img src="{{ Storage::url($letterSubmission->user->id_card_image) }}" alt="Foto KTP" class="img-fluid" style="max-width: 150px;">
                </td>
                </td>
            </tr>
            <tr>
                <th>No KTP</th>
                <td>{{ $letterSubmission->user->id_card ?? '' }}</td>
            </tr>
            <tr>
                <th>No NPWP</th>
                <td>{{ $letterSubmission->user->npwp_number ?? " " }}</td>
            </tr>
        </table>
    </div>
</div>
<script>
    window.onload = function () {
        window.print();
    };
</script>
</body>
</html>
