@extends('adminlte::page')

@section('content')
<div class="container">
    <div class="col-md-12">
        @if(Session::get('store'))
        <div class="alert alert-success mt-3">Surat Perjanjian Berhasil Ditambahkan</div>
        @endif
        @if(Session::get('update'))
        <div class="alert alert-success mt-3">Surat Perjanjian Berhasil Diperbarui</div>
        @endif
    </div>
    <div class="card scrollable" id="printThis">
        <div class="card-body" id="printItem">
            <div class="row">
                <div class="col-12">
                    <div class="text-center mb-4">
                        <p class="text-center mb-0">
                            <strong>PERJANJIAN SEWA MENYEWA</strong>
                        </p>
                        <p class="text-center mb-3"><strong>
                                UNIT {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }}
                            </strong></p>
                    </div>
                    <p class="mt-3 mb-2">Perjanjian Sewa Menyewa Unit {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }}, yang untuk selanjutnya disebut dengan
                        “Perjanjian” ini dibuat dan disetujui pada <strong>{{ \Carbon\Carbon::parse($agreementLetter->date)->format('d F Y') }}</strong> oleh dan antara:</p>
                    <table class="table table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th colspan="2" class="text-left">OBJEK SEWA (Unit {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }})</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="w-50">
                                    <p class="mb-0"><strong>Tipe</strong></p>
                                    <p class="mb-0">{{ isset($agreementLetter->custom_fields['custom_type']) ? e($agreementLetter->custom_fields['custom_type']) : '-' }}</p>
                                </td>
                                <td>
                                    <p class="mb-0"><strong>Tanggal Mulai Sewa</strong></p>
                                    <p class="mb-0">{{ \Carbon\Carbon::parse($agreementLetter->rent_start_duration)->locale('id')->translatedFormat('d F Y') }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50">
                                    <p class="mb-0"><strong>Dimensi (PxLxT)</strong></p>
                                    <p class="mb-0">{{ isset($agreementLetter->custom_fields['custom_length']) ? e($agreementLetter->custom_fields['custom_length']) : '-' }} x {{ isset($agreementLetter->custom_fields['custom_width']) ? e($agreementLetter->custom_fields['custom_width']) : '-' }} x {{ isset($agreementLetter->custom_fields['custom_height']) ? e($agreementLetter->custom_fields['custom_height']) : '-' }}</p>
                                </td>
                                <td>
                                    <p class="mb-0"><strong>Lama Sewa</strong></p>
                                    <p class="mb-0">{{ $agreementLetter->rent_count }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50">
                                    <p class="mb-0"><strong>Nomor Unit</strong></p>
                                    <p class="mb-0">{{ $agreementLetter->getCustomField('custom_unit_number') }}</p>
                                </td>
                                <td>
                                    <p class="mb-0"><strong>Tanggal Selesai Sewa</strong></p>
                                    <p class="mb-0">{{ \Carbon\Carbon::parse($agreementLetter->rent_end_duration)->locale('id')->translatedFormat('d F Y') }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50">
                                    <p class="mb-0"><strong>Lokasi</strong></p>
                                    <p class="mb-0">{{ $agreementLetter->rent_address }}</p>
                                </td>
                                <td>
                                    <p class="mb-0"><strong>Biaya Sewa</strong></p>
                                    <p class="mb-0">Rp. {{ number_format($agreementLetter->quote->total, 0, ',', '.') }}</p>
                                </td>
                            </tr>
                        </tbody>
                        <thead class="thead-dark">
                            <tr>
                                <th colspan="2" class="text-left"><strong>PIHAK PERTAMA</strong> (Penyedia {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }})
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="w-50">
                                    <p class="mb-0"><strong>Nama Perusahaan</strong></p>
                                    <p class="mb-0">{{ $agreementLetter->getCustomField('custom_provider_company') }}</p>
                                </td>
                                <td>
                                    <p class="mb-0"><strong>No Telepon</strong></p>
                                    <p class="mb-0">{{ $agreementLetter->getCustomField('custom_provider_phone') }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50" rowspan="2">
                                    <p class="mb-0"><strong>Alamat</strong></p>
                                    <p class="mb-0">{{ $agreementLetter->rent_address }}</p>
                                </td>
                                <td style="background-color: #e5e5e5;">
                                    <p class="mb-0"><strong>Atas Nama</strong></p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p class="mb-0"><strong>Nama</strong></p>
                                    <p class="mb-0">{{ $company['director'] }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50">
                                    <p class="mb-0"><strong>Email PIC</strong></p>
                                    <p class="mb-0">{{ $agreementLetter->getCustomField('custom_provider_email') }}</p>
                                </td>
                                <td>
                                    <p class="mb-0"><strong>Jabatan</strong></p>
                                    <p class="mb-0">Direktur</p>
                                </td>
                            </tr>
                        </tbody>
                        <thead class="thead-dark">
                            <tr>
                                <th colspan="2" class="text-left"><strong>PIHAK KEDUA</strong> (Penyewa {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }})</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="w-50">
                                    <p class="mb-0"><strong>Nama Perusahaan</strong></p>
                                    <p class="mb-0">{{ $agreementLetter->getCustomField('custom_tenant_company') }}</p>
                                </td>
                                <td>
                                    <p class="mb-0"><strong>No Telp
                                            Perusahaan</strong></p>
                                    <p class="mb-0">{{ $agreementLetter->getCustomField('custom_tenant_phone') }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50">
                                    <p class="mb-0"><strong>NPWP Perusahaan</strong></p>
                                    <p class="mb-0"><strong>{{ $agreementLetter->getCustomField('custom_tenant_npwp') }}</strong></p>
                                </td>
                                <td>
                                    <p class="mb-0"><strong>Alamat</strong></p>
                                    <p class="mb-0">{{ $agreementLetter->getCustomField('custom_tenant_address') }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50">
                                    <p class="mb-0"><strong>Nama PIC</strong></p>
                                    <p class="mb-0">{{ $agreementLetter->getCustomField('custom_pic_name') }}</p>
                                </td>
                            </tr>

                            <tr>
                                <td class="w-50">
                                    <p class="mb-0"><strong>Jabatan PIC</strong></p>
                                    <p class="mb-0">{{ $agreementLetter->getCustomField('custom_pic_position') }}</p>
                                </td>
                                <td>
                                    <p class="mb-0"><strong>No Telp PIC</strong></p>
                                    <p class="mb-0">{{ $agreementLetter->getCustomField('custom_pic_phone') }}</p>
                                </td>
                            </tr>

                            <tr>
                                <td class="w-50">
                                    <p class="mb-0"><strong>NIK</strong></p>
                                    <p class="mb-0">{{ $agreementLetter->getCustomField('custom_pic_nik') }}</p>
                                </td>
                                <td>
                                    <p class="mb-0"><strong>Email PIC</strong></p>
                                    <p class="mb-0">{{ $agreementLetter->getCustomField('custom_pic_email') }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="text-justify">
                        <strong>PIHAK PERTAMA</strong> dan <strong>PIHAK KEDUA</strong> untuk selanjutnya disebut “Para
                        Pihak”. <strong>Para Pihak</strong>
                        masing-masing dalam kedudukannya tersebut diatas terlebih dahulu menerangkan:
                    </p>
                    <ul>
                        <li>
                            Bahwa <strong>PIHAK PERTAMA</strong> adalah pemilik unit {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }} yang beralamat di
                            {{ $agreementLetter->rent_address ?? "-" }}
                        </li>

                        <li>
                            Bahwa <strong>PIHAK PERTAMA</strong> bermaksud menyewakan unit {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }} kepada Pihak
                            Kedua, dan <strong>PIHAK KEDUA</strong> berkeinginan menyewa unit Gudang kepada Pihak
                            Pertama.
                        </li>
                    </ul>

                    <p>
                        Terkait dengan apa yang diuraikan diatas, <strong>Para Pihak</strong> telah setuju untuk
                        melangsungkan
                        Perjanjian ini dengan syarat dan ketentuan yang mengikat <strong>Para Pihak</strong> tersebut,
                        sebagai
                        berikut:
                    </p>

                    <p class="text-center mb-0">PASAL 1</p>
                    <p class="text-center mb-2">DEFINISI</p>
                    <p class="mb-0">
                        Dalam perjanjian sewa menyewa ini yang dimaksud dengan:
                    </p>
                    <ol>
                        <li>
                            Perjanjian adalah Perjanjian Sewa Menyewa antara <strong>PIHAK PERTAMA</strong> dan <strong>PIHAK
                            KEDUA</strong> yaitu persetujuan dimana <strong>PIHAK PERTAMA</strong> selaku pemberi sewa
                            mengikatkan dirinya untuk mengalihkan kuasa atas suatu unit self storage untuk
                            dipergunakan kepada <strong>PIHAK KEDUA</strong> dan <strong>PIHAK KEDUA</strong> selaku
                            penerima sewa
                            mengikatkan dirinya untuk membayar sejumlah uang atas unit sewaan.
                        </li>
                        <li>
                            {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }} adalah gudang milik <strong>PIHAK PERTAMA</strong> yang diperuntukan untuk
                            penyimpanan barang
                        </li>
                        <li>
                            Biaya sewa adalah biaya yang harus dibayarkan <strong>PIHAK KEDUA</strong> kepada <strong>PIHAK
                            PERTAMA</strong> atas penyewaan barang.
                        </li>
                        <li>
                            Sengketa adalah segala perselisihan yang berkaitan dengan perjanjian, termasuk tapi
                            tidak terbatas pada perbedaan pendapat dan ingkar janji.
                        </li>
                    </ol>

                    <p class="text-center mb-0">PASAL 2</p>
                    <p class="text-center mb-2">RUANG LINGKUP SEWA MENYEWA</p>
                    <p class="mb-0">
                        Para Pihak dengan ini setuju bahwa <strong>PIHAK PERTAMA</strong> akan
                        memberikan sewa dalam hal
                        ini
                        Unit Self Storage kepada <strong>PIHAK KEDUA</strong> sesuai dengan syarat dan kondisi yang
                        diatur dalam
                        Perjanjian ini.
                    </p>

                    <p class="text-center mb-0">PASAL 3</p>
                    <p class="text-center mb-2">JANGKA WAKTU SEWA</p>
                    <p class="mb-0">
                        Perjanjian ini berlaku untuk jangka waktu selama {{ $agreementLetter->rent_count }} terhitung sejak tanggal <strong>{{ \Carbon\Carbon::parse($agreementLetter->rent_start_duration)->locale('id')->translatedFormat('d F Y') }}</strong>
                        sampai
                        dengan <strong>{{ \Carbon\Carbon::parse($agreementLetter->rent_end_duration)->locale('id')->translatedFormat('d F Y') }}</strong> dan setelah jangka waktu tersebut berakhir, apabila <strong>PIHAK
                            KEDUA</strong>
                        bermaksud
                        untuk memperbarui perjanjian sewa menyewa, maka <strong>PIHAK KEDUA</strong> wajib melakukan
                        pemesanan
                        kembali
                    </p>

                    <p class="text-center mb-0">PASAL 4</p>
                    <p class="text-center mb-2">BIAYA DAN MEKANISME PEMBAYARAN</p>
                    <p class="mb-0">
                        <strong>PIHAK KEDUA</strong> dan <strong>PIHAK PERTAMA</strong> sepakat bahwa segala biaya
                        berkaitan dengan Sewa Menyewa Unit
                        {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }} serta mekanisme pembayarannya adalah sebagaimana diatur dalam LAMPIRAN 1 Perjanjian,
                        yang merupakan satu kesatuan yang tidak terpisahkan dari Perjanjian ini.
                    </p>


                    <p class="text-center mb-0">PASAL 5</p>
                    <p class="text-center mb-2">PENYESUAIAN BIAYA SEWA</p>
                    <p class="text-justify">
                        <strong>PIHAK PERTAMA</strong> berhak melakukan penyesuaian biaya sewa apabila jangka waktu sewa
                        sudah berakhir.
                    </p>

                    <p class="text-center mb-0">PASAL 6</p>
                    <p class="text-center mb-2">OBJEK SEWA MENYEWA</p>
                    <p class="mb-0">
                        <strong>PIHAK KEDUA</strong> dengan ini menyewa satu unit Gudang dari <strong>PIHAK
                            PERTAMA</strong> dengan
                        rincian sebagai berikut:
                    </p>
                    <ol>
                        <li>
                            Unit Gudang terletak di {{ $agreementLetter->rent_address ?? "-" }}
                        </li>
                        <li>
                            Unit Gudang dengan ukuran panjang <strong>{{  $agreementLetter->getCustomField('custom_length') }}</strong>, Lebar <strong>{{  $agreementLetter->getCustomField('custom_width') }}</strong>, dan
                            tinggi <strong>{{  $agreementLetter->getCustomField('custom_height') }}</strong>
                        </li>
                        <li>
                            Unit Gudang disewakan atas nama <strong>PIHAK KEDUA</strong> untuk digunakan sebagai tempat
                            penyimpanan barang milik <strong>PIHAK KEDUA</strong>.
                        </li>
                    </ol>
                    <p class="mt-1">
                        Untuk selanjutnya disebut sebagai Objek Sewa
                    </p>

                    <p class="text-center mb-0">PASAL 7</p>
                    <p class="text-center mb-2">PENGGUNAAN OBJEK SEWA</p>
                    <ol>
                        <li>
                            <strong>PIHAK KEDUA</strong> mempergunakan unit {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }} dengan tujuan untuk menyimpan
                            barang-barang pribadi dan/atau bisnis.
                        </li>
                        <li>
                            <strong>PIHAK KEDUA</strong> dilarang menggunakan Unit {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }} untuk aktivitas bekerja di
                            dalamnya. Unit {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }} hanya boleh digunakan untuk penyimpanan barang.
                        </li>
                        <li>
                            <strong>PIHAK KEDUA</strong> diberikan maksimal waktu berdiam di dalam Unit {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }}
                            selama
                            3 jam dalam sekali kunjungan.
                        </li>
                        <li>
                            <strong>PIHAK KEDUA</strong> berkewajiban untuk menjaga kebersihan, kemananan, ketertiban
                            dan
                            ketentraman lingkungan {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }}.
                        </li>
                    </ol>

                    <p class="text-center mb-0">PASAL 8</p>
                    <p class="text-center mb-2">PENYERAHAN OBJEK SEWA</p>
                    <ol>
                        <li>
                            <strong>PIHAK PERTAMA</strong> akan menyerahkan Objek Sewa Kepada <strong>PIHAK
                                KEDUA</strong> pada tanggal
                            {{ \Carbon\Carbon::parse($agreementLetter->rent_start_duration)->format('d F Y') }}.
                        </li>
                        <li>
                            Serah terima akan dilakukan dengan suatu Berita Acara Serah Terima yang
                            ditandatangani oleh <strong>PIHAK PERTAMA</strong> dan <strong>PIHAK KEDUA</strong> setelah
                            memenuhi
                            persyaratan dalam perjanjian sewa menyewa ini.
                        </li>
                    </ol>

                    <p class="text-center mb-0">PASAL 9</p>
                    <p class="text-center mb-2">KEAMANAN</p>
                    <ol>
                        {{--
                        <li>
                            <strong>PIHAK PERTAMA</strong> memberikan akses kepada <strong>PIHAK KEDUA</strong> ke
                            lantai tempat Unit self storage
                            berada dengan menggunakan dua opsi akses dari tiga opsi yang tersedia, yaitu:
                            <ol type="A">
                                <li>
                                    Face recognition
                                </li>
                                <li>
                                    Akses Kartu
                                </li>
                                <li>
                                    Fingerprint
                                </li>
                            </ol>
                        </li>
                        --}}
                        <li>
                            <strong>PIHAK PERTAMA</strong> menyediakan sistem keamanan CCTV yang dipantau 24 jam untuk
                            memantau
                            aktivitas di area {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }}
                        </li>
                        <li>
                            <strong>PIHAK PERTAMA</strong> menyediakan alarm kebakaran dan sistem pemadam kebakaran
                            untuk
                            meningkatkan keamanan Unit Self Storage dan fasilitas Self Storage secara keseluruhan.
                        </li>
                        {{-- 
                        <li>
                            <strong>PIHAK KEDUA</strong> diwajibkan untuk memasang pengaman tambahan berupa gembok.
                        </li>
                        --}}
                        <li>
                            <strong>PIHAK KEDUA</strong> wajib mengikuti instruksi keamanan yang diberikan oleh
                            <strong>PIHAK PERTAMA</strong>.
                        </li>
                    </ol>

                    <p class="text-justify">
                        Data CCTV yang tercatat akan diperlakukan secara rahasia dan hanya akan diakses oleh pihak yang
                        berwenang atau sesuai dengan kebijakan privasi yang berlaku
                    </p>

                    <p class="text-center mb-0">PASAL 10</p>
                    <p class="text-center mb-2">HAK DAN KEWAJIBAN</p>
                    <p class="text-left mb-0">
                        <strong>PIHAK PERTAMA</strong>:
                    </p>
                    <ol>
                        <li>
                            <strong>PIHAK PERTAMA</strong> berhak menerima pembayaran Biaya Sewa dan biaya lain-lain
                            yang terutang.
                        </li>
                        <li>
                            <strong>PIHAK PERTAMA</strong> berkewajiban menyerahkan Unit {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }} dalam kondisi baik
                            kepada <strong>PIHAK KEDUA</strong>.
                        </li>
                        <li>
                            <strong>PIHAK PERTAMA</strong> bertanggung jawab atas perawatan dan keamanan fasilitas
                            {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }} secara umum.
                        </li>
                    </ol>
                    <p class="text-left mb-0 mt-0">
                        <strong>PIHAK KEDUA</strong> :
                    </p>
                    <ol>
                        <li>
                            <strong>PIHAK KEDUA</strong> berhak menggunakan Unit {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }} untuk keperluan
                            penyimpanan barang pribadi atau komersial.
                        </li>
                        <li>
                            <strong>PIHAK KEDUA</strong> berkewajiban membayar Biaya Sewa dan biaya lain-lain yang
                            terutang tepat waktu.
                        </li>
                        <li>
                            <strong>PIHAK KEDUA</strong> bertanggung jawab atas kondisi barang yang disimpan di dalam
                            Unit
                            {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }}
                        </li>
                    </ol>

                    <p class="text-center mb-0">PASAL 11</p>
                    <p class="text-center mb-2"><strong>KETERLAMBATAN PEMBAYARAN</strong></p>
                    <ol>
                        <li>
                            Apabila <strong>PIHAK KEDUA</strong> gagal untuk membayar biaya sewa selama maksimal 2 (dua)
                            bulan berturut-turut, dimana <strong>PIHAK PERTAMA</strong> telah memberikan pemberitahuan
                            setiap
                            terjadinya keterlambatan, maka <strong>PIHAK KEDUA</strong> dianggap mengalami keterlambatan
                            pembayaran.
                        </li>
                        <li>
                            Jika <strong>PIHAK KEDUA</strong> masih gagal membayar dalam waktu yang telah ditentukan
                            dalam
                            pemberitahuan, maka <strong>PIHAK PERTAMA</strong> berhak untuk mengosongkan unit yang
                            disewa,
                            dan melelang barang-barang yang ada di dalam unit tersebut. Hasil lelang akan
                            digunakan untuk membayar tunggakan sewa dan selebihnya akan dikembalikan kepada
                            <strong>PIHAK KEDUA</strong>.
                        </li>
                    </ol>

                    <p class="text-center mb-0">PASAL 12</p>
                    <p class="text-center mb-2"><strong>LARANGAN LARANGAN</strong></p>
                    <ol>
                        <li>
                            <strong>PIHAK PERTAMA</strong> tidak dibenarkan meminta <strong>PIHAK KEDUA</strong> untuk
                            mengakhiri
                            jangka waktu kontrak dan menyerahkan kembali unit self storage tersebut kepada
                            <strong>PIHAK PERTAMA</strong> sebelum jangka waktu sewa menyewa sebagaimana yang tertulis
                            dalam pasal 3 surat perjanjian ini berakhir kecuali disepakati oleh kedua belah pihak.
                        </li>
                        <li>
                            <strong>PIHAK KEDUA</strong> tidak dibenarkan sama sekali untuk mengalihkan hak atau
                            menyewakan kepada PIHAK KETIGA tanpa izin serta persetujuan dari <strong>PIHAK
                            PERTAMA.</strong>
                        </li>
                        <li>
                            <strong>PIHAK KEDUA</strong> tidak dibenarkan untuk mengubah struktur dan instalasi dari
                            unit
                            {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }}. Yang dimaksudkan dengan struktur adalah sistem konstruksi bangunan
                            yang menunjang berdirinya unit self storage tersebut, seperti: pondasi, balok, kolom,
                            lantai, dan dinding.
                        </li>
                        <li>
                            <strong>PIHAK KEDUA</strong> dilarang menyimpan barang-barang berbahaya dan beracun dalam
                            Unit {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }}, seperti gas, bahan peledak, kembang api, pestisida, senjata,
                            material beracun, atau bahan ilegal lainnya.
                        </li>
                        <li>
                            <strong>PIHAK KEDUA</strong> dilarang menyimpan barang-barang yang mengeluarkan bau
                            menyengat, yang dapat mengganggu lingkungan penyimpanan serta mempengaruhi
                            penyewa lainnya.
                        </li>
                        <li>
                            <strong>PIHAK KEDUA</strong> dilarang menyimpan barang cair yang bersifat rawan tumpah dan
                            berpotensi merusak unit {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }} maupun barang-barang lain di sekitarnya.
                        </li>
                        <li>
                            <strong>PIHAK KEDUA</strong> dilarang menyebabkan gangguan atau kebisingan yang dapat
                            memicu alarm kebakaran tanpa alasan yang sah.
                        </li>
                        <li>
                            <strong>PIHAK KEDUA</strong> dilarang menggunakan unit {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }} untuk aktivitas yang
                            ilegal
                            atau membahayakan keselamatan dan dapat menyebabkan pengakhiran Perjanjian
                            secara seketika.
                        </li>
                    </ol>

                    <p class="text-center mb-0">PASAL 13</p>
                    <p class="text-center mb-2">JAMINAN</p>
                    <p>
                        <strong>PIHAK PERTAMA</strong> menjamin bahwa unit {{ isset($agreementLetter->custom_fields['custom_unit']) ? e($agreementLetter->custom_fields['custom_unit']) : '-' }} yang disewakan tersebut di atas
                        adalah hak miliknya dan bebas dari segala tuntutan hukum dan persoalan-persoalan yang
                        dapat mengganggu <strong>PIHAK KEDUA</strong> atas penggunaannya selama jangka waktu berlakunya
                        surat perjanjian ini. Segala kerugian yang timbul akibat kelalaian <strong>PIHAK
                            PERTAMA</strong> ini
                        sepenuhnya menjadi tanggung jawab <strong>PIHAK PERTAMA</strong>.
                    </p>

                    <p class="text-center mb-0">PASAL 14</p>
                    <p class="text-center mb-2">FORCE MAJEURE</p>
                    <p class="text-justify mt-0 mb-0">
                        Yang dinamakan force majeure adalah hal-hal yang terjadi di luar kekuasaan <strong>PIHAK
                        PERTAMA</strong>, seperti:
                    </p>
                    <ol class="mb-5">
                        <li>
                            Yang dimaksud dengan "Force Majeure" adalah suatu kejadian yang terjadi di luar
                            kekuasaan <strong>Para Pihak</strong> termasuk namun tidak terbatas pada kejadian yang
                            merupakan akibat dari tindakan alam, banjir, kebakaran, gempa bumi, peperangan,
                            petir, sabotase, pemogokan kerja, huru-hara, kerusuhan, bencana alam, tindakan
                            pemerintah, perubahan hukum dan atau hal-hal lainnya yang terjadi di luar kendali
                            dan tidak wajar atau tidak dapat dicegah, yang secara langsung maupun tidak
                            langsung mengakibatkan baik <strong>PIHAK KEDUA</strong> maupun <strong>PIHAK
                                PERTAMA</strong> tidak
                            dapat melaksanakan, melanjutkan atau terlambat melaksanakan Perjanjian ini
                        </li>
                        <li>
                            Dalam hal terjadi Force Majeure, pihak yang mengalami Force Majeure tersebut
                            wajib memberitahukan kejadian tersebut secara tertulis selambat-lambatnya 6
                            (enam) hari kerja kepada pihak lainnya, terhitung sejak peristiwa itu terjadi, dikuatkan
                            dengan bukti-bukti yang cukup.
                        </li>
                        <li>
                            Dalam hal terjadi Force Majeure, <strong>PIHAK KEDUA</strong> maupun <strong>PIHAK
                                PERTAMA</strong>
                            sepakat bahwa tidak ada Pihak yang harus bertanggung jawab kepada Pihak lain
                            atas tidak-ada pelaksanaan atau penundaan pelaksanaan atas
                            kewajiban-kewajibannya dalam Perjanjian ini dan untuk tidak menuntut kerugian
                            yang timbul akibat Force Majeure tersebut, dengan ketentuan Pihak yang tercegah
                            atau tertunda tersebut telah membuat usaha yang wajar dan mampu untuk
                            menghilangkan hambatan dan untuk melanjutkan pelaksanaan kewajibannya,
                            secepatnya sejauh memungkinkan.
                        </li>
                    </ol>

                    <div class="row pt-5 mb-1 text-center">
                        <div class="col-5">
                            <p class="mb-0">
                                <strong>PIHAK PERTAMA</strong>
                            </p>
                        </div>
                        <div class="offset-1 col-5 mb-1">
                            <p class="mb-0">
                                <strong>PIHAK KEDUA</strong>
                            </p>
                        </div>
                        <!-- Margin TTD -->
                        <div class="offset-2 col-11 mb-1 mt-1">
                            <img src="{{ asset('logo/paraf.png') }}" alt="Signature" style="with:auto; height:150px"
                                class="left-aligned-image">
                        </div>

                        <div class="col-5">
                            <p class="mb-0">
                                {{ $company['director'] }}
                            </p>
                            <p>
                                <strong>Direktur</strong>
                            </p>
                        </div>
                        <div class="offset-1 col-5 mb-5">
                            <p class="mb-0">
                                {{ $agreementLetter->quote ? $agreementLetter->quote->customer->director : '' }}
                            </p>
                            <p>
                                <strong>Penyewa</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 text-center mt-3">
        <!-- Penambahan class text-center dan mt-3 -->
        <a href="{{ route('agreement-letter.edit',$agreementLetter->slug) }}" class="btn btn-primary"><i
                class="fa fa-edit"></i>Edit</a>
        <button type="button" id="downloadWorkOrder" class="btn btn-success"><i class="fa fa-file-pdf"></i>
            {{__('Download')}}</button>
    </div>
</div>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    updateCustomerField();

    $('.select2').select2({
        width: '100%',
        placeholder: 'Pilih Quote'
    });

    $("#downloadWorkOrder").click(function(e) {
        e.preventDefault();
        prinsts();

    });

    $(".select2").on("change", updateCustomerField);
});


function updateCustomerField() {
    // Mendapatkan nilai dari atribut data-customer
    var customerName = $(".select2").find("option:selected").data("customer");

    // Menampilkan nilai tersebut di elemen dengan id "customer"
    $("#customer").val(customerName);
}

function prinsts() {
    let name = "{{ $nomorAgreementLetter }}" + "_surat_perjanjian";
    let printContents = document.getElementById("printThis").innerHTML;
    let originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;

    window.addEventListener("beforeprint", (event) => {
        document.title = name;
    });

    window.print();
    document.body.innerHTML = originalContents;
}
</script>
@stop
@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<style>
@media print {
    #printItem {
        margin-left: 50px;
        margin-right: 50px;
        font-size: 10px;
    }

    @font-face {
        font-family: "Times";
        src: url("/fonts/times.ttf") format("truetype");
    }

    table,
    th,
    td {
        border: 1px solid black;
        border-collapse: collapse;
    }

    .centered-image {
        display: block;
        margin-left: auto;
        margin-right: auto;
        max-width: 100%;
        height: auto;
    }

    .page-break {
        page-break-before: always;
        /* atau page-break-after: always; */
    }
}

.centered-image {
    display: block;
    margin-left: auto;
    margin-right: auto;
    max-width: 100%;
    height: auto;
}

@font-face {
    font-family: "Times";
    src: url("/fonts/times.ttf") format("truetype");
}

#printItem {
    margin-left: 50px;
    margin-right: 50px;
    font-size: 12px;
}


.container {
    /* background-color: #fff; */
    padding: 10px;
    border-radius: 5px;
}

.select2-selection__rendered {
    line-height: 31px !important;
}

.select2-container .select2-selection--single {
    height: 35px !important;
}

.select2-selection__arrow {
    height: 34px !important;
}

hr {
    border: 1px solid black;
    border-radius: 5px;
}

.select2-selection__rendered {
    line-height: 31px !important;
}

.select2-container .select2-selection--single {
    height: 35px !important;
}

.select2-selection__arrow {
    height: 34px !important;
}

/* li */
.margin {
    margin-bottom: 15px;
}

.noMargin {
    margin-bottom: 0px;
}

.scrollable {
    width: 100%;
    height: 650px;
    overflow: auto;
    border: 1px solid #ccc;
}

table,
th,
td {
    border: 1px solid black;
    border-collapse: collapse;
}

.left-aligned-image {
    float: left;
}



table,
th,
td {
    border: 0px solid black;
    border-collapse: collapse;
}
</style>
<style>
body {
    line-height: 1.6;
}

.container {
    /* max-width: 600px; */
    margin: 0 auto;
}

.text-center {
    text-align: center;
}

.text-left {
    text-align: left;
}

.text-right {
    text-align: right;
}

table {
    margin-bottom: 20px;
}

.signature {
    margin-top: 50px;
}

.signature p {
    margin-bottom: 5px;
}

.scrollable-div {
    max-height: 600px;
    overflow-y: auto;
}

.text-justify {
    text-align: justify;
}

.card-body {
    padding-left: 10rem;
    padding-right: 10rem;
}
</style>
@stop