<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perjanjian Kerja Magang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    body 
    {
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

    .detail-table th, .detail-table td {
        margin-bottom: 0px;
        margin-top: 0px;
        padding: 0px;
    }
    p {
        font-size: 12px;
    }

    li {
        font-size: 12px;
    }

    th
    {
        font-size: 13px;
        font-weight: bold;
    }
    td
    {
        font-size: 12px;
    }
    
    h6{
        font-size: 13px;
        font-weight: bold;
    }
    .page-break 
    {
        page-break-after: always;
    }
</style>
@php
    $fieldData = $letterSubmission->convert_field;
@endphp
<body>
    <div class="body-container">
        <!-- Header -->
        <div class="header">
            PERJANJIAN KERJA
        </div>
        <h6 class="text-center"><strong>{{ $company['name'] ?? "" }}</strong></h6>

        <p>Pada Hari {{ $date }} bertempat di Jakarta, telah ditanda tangani perjanjian kerja sama antara:</p>

        <!-- Table to display company and employee information -->
        <table class="table table-borderless detail-table">
            <tbody>
                <tr>
                    <td><strong>Nama</strong></td>
                    <td>: {{ $company['name'] ?? "" }} </td>
                </tr>
                <tr>
                    <td><strong>Penanggung Jawab</strong></td>
                    <td>: {{ $company['director'] ?? "" }}</td>
                </tr>
                <tr>
                    <td><strong>Alamat</strong></td>
                    <td>: {{ $company['address'] ?? "" }}</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p>Bertindak atas perusahaan yang mempekerjakan, selanjutnya disebut PIHAK PERTAMA.</p>
                    </td>
                </tr>
                <tr>
                    <td><strong>Nama</strong></td>
                    <td>: {{ Auth::user()->name }}</td>
                </tr>
                <tr>
                    <td><strong>No KTP</strong></td>
                    <td>: {{ Auth::user()->id_card }}</td>
                </tr>
                <tr>
                    <td><strong>Alamat</strong></td>
                    <td>: {{ Auth::user()->address }}</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p>
                        Bertindak atas nama pribadi, sebagai pekerja / staff yang dipekerjakan selanjutnya disebut sebagai PIHAK KEDUA.
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="row">
            <div class="col">
                <h6><strong>PASAL 1. MAKSUD DAN TUJUAN</strong></h6>
                <p>PARA PIHAK sepakat untuk menjalin hubungan kerja bersama, dimana PIHAK PERTAMA memberikan pekerjaan tetap bulanan kepada PIHAK KEDUA. </p>
            </div>
        </div>
        <!-- Section 1 -->
        <div class="row">
            <div class="col">
                <h6><strong>PASAL 2. RUANG LINGKUP</strong></h6>
                <p>PIHAK PERTAMA menunjuk PIHAK KEDUA untuk melakukan pekerjaan magang sesuai kebutuhan PIHAK PERTAMA dalam melakukan operasional dan fungsi perusahaan terkait desain antarmuka pengguna dan pengalaman pengguna.</p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <h6><strong>PASAL 3. HAK & KEWAJIBAN PARA PIHAK</strong></h6>
                <ul>
                    <li>PIHAK KEDUA berhak menerima kompensasi atas tunjangan magang sesuai kesepakatan.</li>
                    <li>PIHAK KEDUA berhak mendapatkan bimbingan dan pelatihan dari PIHAK PERTAMA.</li>
                    <li>PIHAK KEDUA wajib mentaati seluruh peraturan yang ditetapkan PIHAK PERTAMA.</li>
                    <li>PIHAK KEDUA wajib menjalankan tugas dan pekerjaan dengan bertanggung jawab, dan memberikan hasil yang maksimal.</li>
                </ul>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <h6><strong>PASAL 4. TANGGUNG JAWAB HUKUM</strong></h6>
                <p>PARA PIHAK sepakat bahwa segala tindakan yang menyebabkan kerugian pada perusahaan harus dipertanggungjawabkan walaupun hubungan magang telah berakhir ataupun diakhiri secara sepihak.</p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <h6><strong>PASAL 5. JAM KERJA & CARA BEKERJA</strong></h6>
                <p>Pekerjaan diselesaikan dan dikerjakan baik di tempat kerja yang ditentukan sesuai kebutuhan perusahaan dengan penjadwalan kerja sesuai kebutuhan dan kondisi. Disepakati penanggalan merah adalah libur. PIHAK KEDUA wajib bekerja di tempat yang ditentukan sesuai kebutuhan perusahaan.</p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <h6><strong>PASAL 6. JANGKA WAKTU</strong></h6>
                <p>PARA PIHAK sepakat perjanjian kerja magang ini berlaku selama 3 bulan dan dapat diperpanjang sesuai kesepakatan kedua belah pihak.</p>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col text-left">
                <p>Jakarta, {{ $date ?? "" }}</p>
            </div>
        </div>
        <!-- Signatures Section -->
        <div class="row mt-4">
            <div class="col-6 text-center">
                <p><strong>PIHAK PERTAMA</strong></p>
                 @if($letterSubmission->is_approved == 1)
                <img src="{{ asset('logo/paraf.png') }}" class="img-fluid" alt="Signature" style="with:auto; height:150px">
                <p>_________________________</p>
                <p>{{ $company['director'] ?? "" }}</p>
                @endif
            </div>
            <div class="col-6 text-center">
                <p><strong>PIHAK KEDUA</strong></p>
                @if($letterSubmission->is_approved == 1)
                <img src="{{ Storage::url($letterSubmission->user->signature) }}" class="img-fluid" alt="Signature" style="with:auto; height:150px">
                <p>_________________________</p>
                <p>{{ $letterSubmission->user->name }}</p>
                @endif
            </div>
        </div>
        <div class="page-break"></div>
        <div class="header">
            SURAT KEPUTUSAN MANAJEMEN
        </div>
        <div class="sub-header">
            Perihal: Penerimaan Magang
        </div>
        
        <!-- Information Table -->
        <table class="info-table">
            <tr>
                <th>Nama Lengkap</th>
                <td>{{ $letterSubmission->user->name ?? "" }}</td>
            </tr>
            <tr>
                <th>Jabatan / Fungsi / Keahlian</th>
                <td>{{ $letterSubmission->user->last_position ? $letterSubmission->user->last_position->position->name : "" }}</td>
            </tr>
            <tr>
                <th>Tanggal Perhitungan Gaji </th>
                <td>{{ $fieldData['salary_date'] ?? "" }}</td>
            </tr>
            <tr>
                <th>Jam Kerja</th>
                <td>{{ $fieldData['working_hours'] ?? ''  }}</td>
            </tr>
            <tr>
                <th>Penempatan</th>
                <td>{{ $fieldData['work_location'] ?? ''  }}</td>
            </tr>
        </table>
        
        <p>
            Perjanjian ini berlaku efektif sejak ditandatangani, dan peserta magang akan menjalankan peran sebagai UI/UX Designer selama masa magang. Peserta magang tunduk pada ketentuan yang berlaku dalam perusahaan serta aturan dan tanggung jawab yang telah ditetapkan.
        </p>
        
        <table class="table table-borderless">
            <tr>
                <td>
                    <p><strong>Fungsi Manajemen: UI/UX Designer</strong></p>
                </td>
            </tr>
            <tr>
                <td>
                    <p><strong>Tanggung Jawab Pekerjaan:</strong></p>
                </td>
            </tr>
            <tr>
                <td>
                    <ol>
                        <li>Mendesain dan mengembangkan mockup dan prototype UI/UX.</li>
                        <li>Melakukan uji coba usability dan mengumpulkan feedback pengguna.</li>
                        <li>Berkolaborasi dengan tim pengembangan untuk mengimplementasikan desain.</li>
                        <li>Memastikan desain konsisten dengan panduan merek perusahaan.</li>
                        <li>Membantu membuat asset image di website yang dibangun.</li>
                    </ol>
                </td>
            </tr>
        </table>
        <!-- Footer -->
        <div class="text-left mb-">
            <p>Jakarta, 12 Agustus 2024</p>
        </div>

        <!-- KTP and Signature Section -->
        <div class="row photo-ktp">
            <div class="col">
                <p><strong>{{ $company['director'] ?? "" }}</strong></p>
            </div>
            <div class="col">
                <p><strong>{{ $letterSubmission->user->name ?? "" }}</strong></p>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
