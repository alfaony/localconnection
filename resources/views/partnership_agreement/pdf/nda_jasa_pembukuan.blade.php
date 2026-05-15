<div class="card-body" id="printItem">

    {{-- HEADER --}}
    <div class="row mb-2">
        <div class="col-6 pe-3 text-center">
            <h6><strong>NON-DISCLOSURE AGREEMENT</strong></h6>
            <h6>No. {{ $agreement->number_result }}</h6>
        </div>
        <div class="col-6 ps-3 text-center">
            <h6><strong>NON-DISCLOSURE AGREEMENT</strong></h6>
            <h6>No. {{ $agreement->number_result }}</h6>
        </div>
    </div>

    {{-- INTRO --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p>Perjanjian Kerahasiaan ini (<strong>"Perjanjian"</strong>) dibuat dan ditandatangani pada tanggal
                {{ \Carbon\Carbon::parse($agreement->date_agreement)->locale('id')->isoFormat('D MMMM Y') }}
                oleh dan antara:</p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p>This Non-Disclosure Agreement (hereinafter referred to as the <strong>"Agreement"</strong>) is made and
                entered into on
                {{ \Carbon\Carbon::parse($agreement->date_agreement)->format('d F Y') }}
                by and between:</p>
        </div>
    </div>

    {{-- PIHAK PERTAMA --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>PIHAK PERTAMA</strong></p>
            <p><Strong>PT GEMA TEKNOLOGI CAHAYA GEMILANG</Strong>, suatu perseroan terbatas yang didirikan dan tunduk berdasarkan hukum
                Negara Republik Indonesia, berkedudukan di Jakarta Barat, beralamat di Podomoro City Ruko GSA 8DH, Jl.
                Letjen S Parman, Tj Duren Selatan, Kecamatan Grogol Petamburan, Jakarta Barat, DKI Jakarta 11460,
                dalam hal ini diwakili secara sah oleh:<br>
                Nama : {{ $agreement->getFields("nama_perwakilan_pertama") }}, Jabatan : {{ $agreement->getFields("jabatan_perwakilan_pertama_id") }}, bertindak untuk dan atas
                nama PT Gema Teknologi Cahaya Gemilang, selanjutnya disebut sebagai <strong>"Penyedia Layanan"</strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>FIRST PARTY</strong></p>
            <p><strong>PT GEMA TEKNOLOGI CAHAYA GEMILANG</strong>, a limited liability company duly established and
                existing under the laws of the Republic of Indonesia, having its registered address at Podomoro City
                Ruko GSA 8DH, Jl. Letjen S Parman, Tj Duren Selatan, Kecamatan Grogol Petamburan, West Jakarta, DKI
                Jakarta 11460, legally represented herein by:<br>
                Name : {{ $agreement->getFields("nama_perwakilan_pertama") }}, Title : {{ $agreement->getFields("jabatan_perwakilan_pertama") }}, acting for and on behalf
                of PT Gema Teknologi Cahaya Gemilang, hereinafter referred to as the <strong>"Service Provider"</strong>
            </p>
        </div>
    </div>

    {{-- PIHAK KEDUA --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>PIHAK KEDUA</strong></p>
            <p><strong>{{ $agreement->getFields("nama_perusahaan_kedua") }}</strong>, suatu perusahaan yang didirikan
                berdasarkan hukum yang berlaku di Republik Indonesia, beralamat di
                {{ $agreement->getFields("alamat_perusahaan_kedua") }}, dalam hal ini diwakili secara sah oleh:<br>
                Nama : {{ $agreement->getFields("nama_perwakilan_kedua") }}, Jabatan : {{ $agreement->getFields("jabatan_perwakilan_kedua_id") }},
                bertindak untuk dan atas nama perusahaan tersebut, selanjutnya disebut sebagai <strong>"Client"</strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>SECOND PARTY</strong></p>
            <p><strong>{{ $agreement->getFields("nama_perusahaan_kedua") }}</strong>, a company established under the
                applicable laws of the Republic of Indonesia, having its address at
                {{ $agreement->getFields("alamat_perusahaan_kedua") }},
                legally represented herein by:<br>
                Name : {{ $agreement->getFields("nama_perwakilan_kedua") }}, Title : {{ $agreement->getFields("jabatan_perwakilan_kedua_en") }},
                acting for and on behalf of the company, hereinafter referred to as the <strong>"Client"</strong>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p>Penyedia Layanan dan Client selanjutnya secara bersama-sama disebut sebagai <strong>"Para Pihak"</strong>
                dan masing-masing disebut sebagai <strong>"Pihak"</strong>.</p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p>The Service Provider and the Client shall hereinafter collectively be referred to as the
                <strong>"Parties"</strong> and individually as a <strong>"Party"</strong>.</p>
        </div>
    </div>

    {{-- MENIMBANG --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>MENIMBANG</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">Bahwa Para Pihak bermaksud untuk melakukan kerja sama layanan jasa
                    pembukuan, pengelolaan laporan keuangan, pelaporan perpajakan, serta layanan administrasi keuangan
                    lainnya.</li>
                <li style="margin-bottom: 8px;">Bahwa dalam pelaksanaan kerja sama tersebut, Para Pihak dapat saling
                    memberikan informasi yang bersifat rahasia, penting, dan/atau mengandung data bisnis.</li>
                <li style="margin-bottom: 8px;">Bahwa Para Pihak sepakat untuk menjaga kerahasiaan informasi tersebut
                    sesuai dengan syarat dan ketentuan dalam Perjanjian ini.</li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>WHEREAS</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">The Parties intend to enter into cooperation regarding bookkeeping
                    services, financial reporting management, tax reporting, and other financial administrative
                    services.</li>
                <li style="margin-bottom: 8px;">In connection with such cooperation, the Parties may disclose
                    confidential, important, and/or business-related information to one another.</li>
                <li style="margin-bottom: 8px;">The Parties agree to maintain the confidentiality of such information
                    in accordance with the terms and conditions set forth in this Agreement.</li>
            </ol>
        </div>
    </div>

    {{-- INFORMASI RAHASIA --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>INFORMASI RAHASIA</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">Yang dimaksud dengan <strong>"Informasi Rahasia"</strong> meliputi
                    namun tidak terbatas pada :
                    <ol type="a" style="margin-top: 6px;">
                        <li>Data Transaksi Usaha</li>
                        <li>Laporan Keuangan</li>
                        <li>Dokumen Perpajakan</li>
                        <li>Data Customer dan Vendor</li>
                        <li>Cashflow dan Payroll</li>
                        <li>Data Operasional Bisnis</li>
                        <li>Akses Software Accounting</li>
                        <li>User Login dan Password</li>
                        <li>Dokumen Kontrak</li>
                        <li>Data Internal Perusahaan</li>
                        <li>Strategi Bisnis dan Pricing</li>
                    </ol>
                    Seluruh data yang diberikan selama kerja sama berlangsung baik dalam bentuk tertulis, lisan,
                    digital, elektronik, maupun bentuk lainnya.
                </li>
                <li style="margin-bottom: 8px;">Para Pihak wajib menjaga dan merahasiakan seluruh Informasi Rahasia
                    serta dilarang Mengungkapkan kepada pihak ketiga, Menggandakan tanpa izin, Menyebarluaskan,
                    Menggunakan untuk kepentingan pribadi, Menggunakan diluar tujuan kerja sama, tanpa persetujuan
                    tertulis dari Pihak lainnya.</li>
                <li style="margin-bottom: 8px;">Kewajiban kerahasiaan tidak berlaku terhadap informasi yang Telah
                    menjadi informasi publik, Diperoleh secara sah dari pihak ketiga, Wajib diungkapkan berdasarkan
                    hukum atau perintah pemerintah, Sudah dimiliki sebelumnya tanpa kewajiban kerahasiaan.</li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>CONFIDENTIAL INFORMATION</strong></p>
            <ol>
                <li style="margin-bottom: 8px;"><strong>"Confidential information"</strong> includes but is not limited
                    to :
                    <ol style="margin-top: 6px;">
                        <li>Business transaction data</li>
                        <li>Financial statements</li>
                        <li>Tax documents</li>
                        <li>Customer and vendor data</li>
                        <li>Cashflow and payroll data</li>
                        <li>Business operational data</li>
                        <li>Accounting software access</li>
                        <li>User login credentials and passwords</li>
                        <li>Contract documents</li>
                        <li>Internal company data</li>
                        <li>Business strategies and pricing</li>
                    </ol>
                    including all information disclosed during the course of the cooperation, whether in written, oral,
                    digital, electronic, or any other form.
                </li>
                <li style="margin-bottom: 8px;">The Parties shall maintain and protect all confidential Information and
                    are prohibited from disclosing it to any third party, reproducing it without authorization,
                    distributing it, using it for personal purposes, or using it beyond the scope of the cooperation,
                    without prior written consent from the other Party.</li>
                <li style="margin-bottom: 8px;">The confidentiality obligations shall not apply to information that
                    has become publicly available, has been lawfully obtained from a third party, is required to be
                    disclosed by law or governmental order, or was previously possessed without any confidentiality
                    obligation.</li>
            </ol>
        </div>
    </div>

    {{-- TUJUAN PENGGUNAAN --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>TUJUAN PENGGUNAAN INFORMASI</strong></p>
            <p>Informasi Rahasia hanya dapat digunakan untuk kepentingan Pelaksanaan jasa pembukuan, Penyusunan
                laporan keuangan, Pelaporan perpajakan, Administrasi keuangan, Konsultasi keuangan dan perpajakan,
                Penggunaan software accounting berbasis cloud dan tidak dapat digunakan untuk tujuan lain tanpa
                persetujuan tertulis dari Pihak lainnya.</p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>PURPOSE OF USE OF INFORMATION</strong></p>
            <p>The confidential information may only be used for the purposes of bookkeeping services implementation,
                preparation of financial reports, tax reporting, financial administration, financial and tax
                consultation, and the use of cloud-based accounting software, and shall not be used for any other
                purpose without prior written consent from the other Party.</p>
        </div>
    </div>

    {{-- AKSES CORETAX DJP --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>AKSES CORETAX DJP</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">Dalam hal Client meminta Penyedia Layanan untuk membantu proses
                    administrasi dan/atau pelaporan perpajakan melalui Coretax DJP, maka pemberian akses bersifat
                    opsional dan hanya dilakukan berdasarkan persetujuan Client.</li>
                <li style="margin-bottom: 8px;">Penyedia Layanan hanya dapat menggunakan akses tersebut untuk
                    kepentingan layanan yang telah disepakati, termasuk pengecekan data perpajakan, penginputan data,
                    pelaporan SPT, dan administrasi perpajakan lainnya.</li>
                <li style="margin-bottom: 8px;">Client bertanggung jawab atas kebenaran, kelengkapan, dan keabsahan
                    seluruh data serta dokumen yang diberikan kepada Penyedia Layanan.</li>
                <li style="margin-bottom: 8px;">Penyedia Layanan tidak bertanggung jawab atas sanksi, denda, koreksi,
                    atau konsekuensi perpajakan lainnya yang timbul akibat data yang tidak benar, tidak lengkap,
                    terlambat diberikan, atau keputusan perpajakan Client.</li>
                <li style="margin-bottom: 8px;">Setelah layanan selesai atau akses tidak lagi diperlukan, Client
                    berhak mencabut akses Coretax DJP, dan Penyedia Layanan wajib berhenti menggunakan akses
                    tersebut.</li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>ACCESS TO CORETAX DJP</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">If the Client requests the Service Provider to assist with tax
                    administration and/or tax reporting through Coretax DJP, the granting of access shall be optional
                    and subject to the Client's approval.</li>
                <li style="margin-bottom: 8px;">The Service Provider may only use such access for the agreed services,
                    including tax data checking, data input, tax return filing, and other tax administration
                    purposes.</li>
                <li style="margin-bottom: 8px;">The Client shall be responsible for the accuracy, completeness, and
                    validity of all data and documents provided to the Service Provider.</li>
                <li style="margin-bottom: 8px;">The Service Provider shall not be liable for any sanctions, penalties,
                    corrections, or other tax consequences arising from incorrect, incomplete, late-submitted data, or
                    tax decisions made by the Client.</li>
                <li style="margin-bottom: 8px;">Upon completion of the services or when access is no longer required,
                    the Client has the right to revoke the Coretax DJP access, and the Service Provider shall cease
                    using such access.</li>
            </ol>
        </div>
    </div>

    {{-- RUANG LINGKUP LAYANAN --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>RUANG LINGKUP LAYANAN</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">Para Pihak memahami bahwa biaya layanan jasa pembukuan untuk periode
                    kerjasama selama 12 (dua belas) bulan ditentukan berdasarkan Jenis paket layanan yang dipilih dan
                    Jumlah transaksi maksimal sebanyak
                    {{ $agreement->getFields("jumlah_transaksi_maksimal") }}
                    ({{ $agreement->getFields("jumlah_transaksi_terbilang_id") }})
                    transaksi per bulan.
                </li>
                <li style="margin-bottom: 8px;">Paket Basic mencakup layanan pembukuan dengan biaya sebesar
                    Rp {{ $agreement->getFields("biaya_paket_basic") ? number_format($agreement->getFields("biaya_paket_basic"), 0, ',', '.') : '-' }}
                    ({{ $agreement->getFields("biaya_paket_basic_terbilang_id") }})
                    untuk periode layanan selama 12 (dua belas) bulan.
                </li>
                <li style="margin-bottom: 8px;">Paket lainnya dapat mencakup Layanan pembukuan, Pelaporan pajak,
                    Pendampingan administrasi keuangan, Scope tambahan sesuai kesepakatan Para Pihak.</li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>SCOPE OF SERVICE</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">The Parties understand that the bookkeeping service fee for a
                    cooperation period of 12 (twelve) months shall be determined based on the type of service package
                    selected and a maximum transaction volume of
                    {{ $agreement->getFields("jumlah_transaksi_maksimal") }}
                    ({{ $agreement->getFields("jumlah_transaksi_terbilang_en") }})
                    transactions per month.
                </li>
                <li style="margin-bottom: 8px;">The Basic Package includes bookkeeping services with a fee of
                    IDR {{ $agreement->getFields("biaya_paket_basic") ? number_format($agreement->getFields("biaya_paket_basic"), 0, ',', '.') : '-' }}
                    ({{ $agreement->getFields("biaya_paket_basic_terbilang_en") }})
                    for a service period of 12 (twelve) months.
                </li>
                <li style="margin-bottom: 8px;">Other packages may include bookkeeping services, tax reporting
                    services, financial administration assistance, and additional scopes of work as agreed upon by the
                    Parties.</li>
            </ol>
        </div>
    </div>

    {{-- CAKUPAN LAYANAN --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>CAKUPAN LAYANAN</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">Layanan yang termasuk dalam paket antara lain:
                    <ol type="a" style="margin-top: 6px;">
                        <li style="margin-bottom: 6px;">Layanan pembukuan bulanan hingga
                            {{ $agreement->getFields("jumlah_transaksi_bulanan") }}
                            transaksi per bulan.</li>
                        <li style="margin-bottom: 6px;">Pencatatan transaksi dan penyusunan laporan keuangan oleh
                            admin profesional.</li>
                        <li style="margin-bottom: 6px;">Akses software Keloola Accounting berbasis cloud.</li>
                        <li style="margin-bottom: 6px;">Laporan keuangan bulanan meliputi Neraca, Laba Rugi, Buku
                            Besar, Jurnal Transaksi.</li>
                        <li style="margin-bottom: 6px;">Pelaporan pajak usaha {{ $agreement->getFields("nama_entitas_pajak") }}
                            yang terdiri SPT PPh 21/26, PPh 23, PPh 25, PPh Final Pasal 4 ayat (2).</li>
                        <li style="margin-bottom: 6px;">Diskusi dan konsultasi langsung dengan tim keuangan.</li>
                        <li style="margin-bottom: 6px;">Onboarding dan setup sistem pembukuan setelah pembayaran
                            terkonfirmasi.</li>
                    </ol>
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>SERVICE COVERAGE</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">The services included in the package include:
                    <ol type="a" style="margin-top: 6px;">
                        <li style="margin-bottom: 6px;">Monthly bookkeeping services for up to
                            {{ $agreement->getFields("jumlah_transaksi_bulanan") }}
                            transactions per month.</li>
                        <li style="margin-bottom: 6px;">Transaction recording and preparation of financial reports by
                            professional administrators.</li>
                        <li style="margin-bottom: 6px;">Access to cloud-based Keloola Accounting software.</li>
                        <li style="margin-bottom: 6px;">Monthly financial reports including Balance Sheet, Profit &
                            Loss Statement, General Ledger, and Transaction Journals.</li>
                        <li style="margin-bottom: 6px;">Business tax reporting for {{ $agreement->getFields("nama_entitas_pajak") }},
                            including Income Tax Article 21/26, Article 23, Article 25, and Final Income Tax Article 4
                            paragraph (2).</li>
                        <li style="margin-bottom: 6px;">Direct discussion and consultation with the finance team.</li>
                        <li style="margin-bottom: 6px;">Onboarding and bookkeeping system setup after payment
                            confirmation.</li>
                    </ol>
                </li>
            </ol>
        </div>
    </div>

    {{-- PENYESUAIAN BIAYA --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>PENYESUAIAN BIAYA</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">Besaran biaya final akan disesuaikan dengan:
                    <ol type="a" style="margin-top: 6px;">
                        <li>Jumlah transaksi aktual bulanan.</li>
                        <li>Paket layanan yang dipilih.</li>
                    </ol>
                </li>
                <li style="margin-bottom: 8px;">Kompleksitas dokumen dan administrasi.</li>
                <li style="margin-bottom: 8px;">Apabila jumlah transaksi melebihi batas paket yang disepakati, maka:
                    <ol type="a" style="margin-top: 6px;">
                        <li>Akan dikenakan biaya tambahan (add-on).</li>
                        <li>Nilai biaya tambahan akan diinformasikan terlebih dahulu kepada Client.</li>
                    </ol>
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>FEE ADJUSTMENT</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">The final service fee shall be adjusted based on:
                    <ol type="a" style="margin-top: 6px;">
                        <li>Actual monthly transaction volume.</li>
                        <li>The selected service package.</li>
                    </ol>
                </li>
                <li style="margin-bottom: 8px;">The complexity of documents and administrative requirements.</li>
                <li style="margin-bottom: 8px;">If the transaction volume exceeds the agreed package limit:
                    <ol type="a" style="margin-top: 6px;">
                        <li>Additional charges (add-ons) shall apply.</li>
                        <li>The amount of the additional charges shall be informed to the Client in advance.</li>
                    </ol>
                </li>
            </ol>
        </div>
    </div>

    {{-- KEWAJIBAN CLIENT --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>KEWAJIBAN CLIENT</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">Client wajib:
                    <ol type="a" style="margin-top: 6px;">
                        <li>Memberikan data dan dokumen yang valid.</li>
                        <li>Memberikan dokumen tepat waktu.</li>
                        <li>Tidak memberikan data manipulatif/fiktif.</li>
                        <li>Menjamin legalitas data yang diberikan.</li>
                    </ol>
                </li>
                <li style="margin-bottom: 8px;">Penyedia Layanan tidak bertanggung jawab atas:
                    <ol type="a" style="margin-top: 6px;">
                        <li>Kesalahan data dari Client.</li>
                        <li>Dokumen yang tidak lengkap.</li>
                        <li>Data manipulatif.</li>
                        <li>Keterlambatan dokumen yang menyebabkan keterlambatan laporan.</li>
                    </ol>
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>CLIENT OBLIGATIONS</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">The Client shall:
                    <ol type="a" style="margin-top: 6px;">
                        <li>Provide valid data and documents.</li>
                        <li>Submit documents in a timely manner.</li>
                        <li>Not provide manipulative or fictitious data.</li>
                        <li>Guarantee the legality of all submitted data.</li>
                    </ol>
                </li>
                <li style="margin-bottom: 8px;">The Service Provider shall not be responsible for:
                    <ol type="a" style="margin-top: 6px;">
                        <li>Errors in data provided by the Client.</li>
                        <li>Incomplete documents.</li>
                        <li>Manipulative or fictitious data.</li>
                        <li>Delays in document submission resulting in delays in report preparation.</li>
                    </ol>
                </li>
            </ol>
        </div>
    </div>

    {{-- KEPEMILIKAN DATA --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>KEPEMILIKAN DATA</strong></p>
            <p>Seluruh data, laporan, dan dokumen milik Client tetap menjadi hak milik Client. Penyedia Layanan hanya
                bertindak sebagai pengelola administrasi dan pembukuan sesuai ruang lingkup layanan.</p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>DATA OWNERSHIP</strong></p>
            <p>All data, reports, and documents belonging to the Client shall remain the sole property of the Client.
                The Service Provider shall only act as an administrative and bookkeeping service provider in accordance
                with the agreed scope of services.</p>
        </div>
    </div>

    {{-- JANGKA WAKTU --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>JANGKA WAKTU</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">Perjanjian ini berlaku sejak tanggal ditandatangani dan berlaku selama
                    12 (dua belas) bulan.</li>
                <li style="margin-bottom: 8px;">Kewajiban menjaga kerahasiaan tetap berlaku sampai kerjasama
                    berakhir.</li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>TERM</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">This Agreement shall become effective from the date of signing and
                    shall remain valid for a period of 12 (twelve) months.</li>
                <li style="margin-bottom: 8px;">The obligation to maintain confidentiality shall remain in effect
                    until the cooperation has ended.</li>
            </ol>
        </div>
    </div>

    {{-- PENGEMBALIAN DATA --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>PENGEMBALIAN DATA</strong></p>
            <p>Setelah kerjasama berakhir, Para Pihak wajib:</p>
            <ol type="a">
                <li style="margin-bottom: 6px;">Mengembalikan dokumen milik masing-masing Pihak.</li>
                <li style="margin-bottom: 6px;">Menghapus data yang tidak diperlukan.</li>
                <li style="margin-bottom: 6px;">Tidak menyimpan atau menggunakan data tanpa izin.</li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>RETURN OF DATA</strong></p>
            <p>Upon termination of the cooperation, the Parties shall:</p>
            <ol type="a">
                <li style="margin-bottom: 6px;">Return documents belonging to each respective Party.</li>
                <li style="margin-bottom: 6px;">Delete any unnecessary data.</li>
                <li style="margin-bottom: 6px;">Refrain from storing or using any data without authorization.</li>
            </ol>
        </div>
    </div>

    {{-- GANTI RUGI --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>GANTI RUGI</strong></p>
            <p>Pihak yang melanggar ketentuan kerahasiaan dalam Perjanjian ini wajib bertanggung jawab atas kerugian
                yang timbul sesuai ketentuan hukum yang berlaku di Republik Indonesia.</p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>COMPENSATION</strong></p>
            <p>Any Party that violates the confidentiality provisions under this Agreement shall be liable for any
                losses arising therefrom in accordance with the applicable laws of the Republic of Indonesia.</p>
        </div>
    </div>

    {{-- HUKUM YANG BERLAKU --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>HUKUM YANG BERLAKU</strong></p>
            <p>Perjanjian ini tunduk dan ditafsirkan berdasarkan hukum Republik Indonesia. Apabila terjadi
                perselisihan, Para Pihak sepakat untuk menyelesaikannya terlebih dahulu secara musyawarah. Apabila
                tidak tercapai kesepakatan, maka perselisihan akan diselesaikan melalui Badan Arbitrase Nasional
                Indonesia (BANI) di Jakarta.</p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>GOVERNING LAW</strong></p>
            <p>This Agreement shall be governed by and construed in accordance with the laws of the Republic of
                Indonesia. In the event of any dispute, the Parties agree to first resolve the matter amicably through
                mutual deliberation. If no agreement can be reached, the dispute shall be resolved through the
                Indonesian National Arbitration Board (Badan Arbitrase Nasional Indonesia – BANI) in Jakarta.</p>
        </div>
    </div>

    {{-- KETENTUAN UMUM --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>KETENTUAN UMUM</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">Para pihak telah memperoleh seluruh izin/persetujuan yang diperlukan,
                    serta telah dilakukan oleh pihak yang berwenang untuk dapat bertindak atas nama perusahaan sesuai
                    dengan anggaran dasarnya dan/atau telah memperoleh persetujuan korporasi untuk mengadakan
                    Perjanjian ini.</li>
                <li style="margin-bottom: 8px;">Semua pemberitahuan yang diperlukan atau diperbolehkan untuk diberikan
                    berdasarkan Perjanjian ini harus dibuatkan dalam bentuk tertulis, dan harus disampaikan melalui
                    email atau surat pos udara pada alamat tujuan yang tertera di bawah ini:
                    <br><br>
                    <table style="border: 0; width: 100%; margin-bottom: 10px;">
                        <tr><td colspan="3"><strong>{{ $agreement->getFields("nama_perusahaan_kedua") }}</strong></td></tr>
                        <tr>
                            <td width="25%">Alamat</td>
                            <td width="5%">:</td>
                            <td>{{ $agreement->getFields("alamat_perusahaan_kedua") }}</td>
                        </tr>
                        <tr>
                            <td>Atensi</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("atensi_client") }}</td>
                        </tr>
                        <tr>
                            <td>Tel</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("telp_client") }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("email_client") }}</td>
                        </tr>
                    </table>
                    <table style="border: 0; width: 100%;">
                        <tr><td colspan="3"><strong>PT Gema Teknologi Cahaya Gemilang</strong></td></tr>
                        <tr>
                            <td width="25%">Alamat</td>
                            <td width="5%">:</td>
                            <td>Podomoro City Ruko GSA 8DH, Jl. Letjend S Parman, Tj Duren Sel, Kec. Grogol Petamburan, Jakarta Barat ID 11460</td>
                        </tr>
                        <tr>
                            <td>Atensi</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("nama_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Tel</td>
                            <td>:</td>
                            <td>62 811 1922 1858</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>mitra@brightcorporation.biz</td>
                        </tr>
                    </table>
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>GENERAL PROVISIONS</strong></p>
            <ol>
                <li style="margin-bottom: 8px;">The Parties have obtained all necessary permits and approvals, and
                    this Agreement has been executed by duly authorized representatives acting on behalf of their
                    respective companies in accordance with their articles of association and/or corporate approvals
                    required to enter into this Agreement.</li>
                <li style="margin-bottom: 8px;">All notices required or permitted under this Agreement shall be made
                    in writing and delivered via email or registered airmail to the addresses specified below:
                    <br><br>
                    <table style="border: 0; width: 100%; margin-bottom: 10px;">
                        <tr><td colspan="3"><strong>{{ $agreement->getFields("nama_perusahaan_kedua") }}</strong></td></tr>
                        <tr>
                            <td width="25%">Address</td>
                            <td width="5%">:</td>
                            <td>{{ $agreement->getFields("alamat_perusahaan_kedua") }}</td>
                        </tr>
                        <tr>
                            <td>Attention</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("atensi_client") }}</td>
                        </tr>
                        <tr>
                            <td>Tel</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("telp_client") }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("email_client") }}</td>
                        </tr>
                    </table>
                    <table style="border: 0; width: 100%;">
                        <tr><td colspan="3"><strong>PT Gema Teknologi Cahaya Gemilang</strong></td></tr>
                        <tr>
                            <td width="25%">Address</td>
                            <td width="5%">:</td>
                            <td>Podomoro City Ruko GSA 8DH, Jl. Letjend S Parman, Tj Duren Sel, Kec. Grogol Petamburan, Jakarta Barat ID 11460</td>
                        </tr>
                        <tr>
                            <td>Attention</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("nama_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Tel</td>
                            <td>:</td>
                            <td>62 811 1922 1858</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>mitra@brightcorporation.biz</td>
                        </tr>
                    </table>
                </li>
            </ol>
        </div>
    </div>

    {{-- KESELURUHAN PERJANJIAN --}}
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p><strong>KESELURUHAN PERJANJIAN</strong></p>
            <p>Perjanjian ini merupakan keseluruhan perjanjian antara Para Pihak sehubungan dengan pokok dari
                Perjanjian ini dan menggantikan seluruh perjanjian-perjanjian dan pemahaman-pemahaman sebelumnya baik
                lisan dan tertulis antara kedua belah Pihak sehubungan dengan hal-hal pokok Perjanjian. Tidak ada
                pengubahan atau modifikasi terhadap Perjanjian ini menjadi sah atau mengikat Para Pihak, terkecuali
                dibuat secara tertulis dan ditandatangani oleh kedua pihak oleh perwakilan mereka yang diberikan
                kewenangan secara sah.</p>
            <p>Perjanjian ini akan ditandatangani dalam beberapa rangkap terpisah. Setiap rangkap merupakan dokumen
                asli dan masing-masing memiliki kekuatan hukum yang sama dan mengikat. Penandatanganan dan pengiriman
                Perjanjian ini secara elektronik dianggap sah dan efektif, dan tanda tangan elektronik dengan ini
                dianggap asli.</p>
            <p>Demikian Perjanjian ini dibuat dan ditandatangani oleh Para Pihak dalam keadaan sadar, tanpa paksaan,
                dan memiliki kekuatan hukum yang mengikat.</p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p><strong>ENTIRE AGREEMENT</strong></p>
            <p>This Agreement constitutes the entire agreement between the Parties with respect to the subject matter
                herein and supersedes all prior agreements, understandings, and communications, whether oral or
                written, between the Parties relating to the subject matter of this Agreement. No amendment or
                modification of this Agreement shall be valid or binding upon the Parties unless made in writing and
                signed by duly authorized representatives of both Parties.</p>
            <p>This Agreement may be executed in several counterparts. Each counterpart shall be deemed an original
                document, and all counterparts together shall constitute one and the same legally binding Agreement.
                The execution and delivery of this Agreement electronically shall be deemed valid and effective, and
                electronic signatures shall be considered as original signatures.</p>
            <p>IN WITNESS WHEREOF, this Agreement has been made and executed by the Parties voluntarily, without
                coercion, and with full legal force and effect.</p>
        </div>
    </div>

    {{-- SIGNATURE --}}
    <div class="row mt-5 mb-3">
        <div class="col-5 text-justify">
            <p class="noMargin">PT GEMA TEKNOLOGI CAHAYA GEMILANG</p>
        </div>
        <div class="offset-2 col-5 text-justify">
            <p class="noMargin">{{ $agreement->getFields("nama_perusahaan_kedua") }}</p>
        </div>
    </div>

    <div class="row mt-5 mb-3">
        <div class="col-5 text-justify">
            @if($agreement->getSignature(1))
                <img src="{{ s3_asset(true, 10, 'public/' . $agreement->getSignature(1)->signature) }}"
                    class="img-thumbnail img-signature">
            @else
                <div style="min-height: 80px;"></div>
            @endif
        </div>
        <div class="offset-2 col-5 text-justify">
            @if($agreement->getSignature(2))
                <img src="{{ s3_asset(true, 10, 'public/' . $agreement->getSignature(2)->signature) }}"
                    class="img-thumbnail img-signature">
            @else
                <div style="min-height: 80px;"></div>
            @endif
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-5 text-justify">
            <p class="noMargin"><strong>{{ $agreement->getFields("nama_perwakilan_pertama") }}</strong></p>
            <p class="noMargin"><strong>{{ $agreement->getFields("jabatan_perwakilan_pertama") }}</strong></p>
        </div>
        <div class="offset-2 col-5 text-justify">
            <p class="noMargin"><strong>{{ $agreement->getFields("nama_perwakilan_kedua") }}</strong></p>
            <p class="noMargin"><strong>{{ $agreement->getFields("jabatan_perwakilan_kedua") }}</strong></p>
        </div>
    </div>

</div>
