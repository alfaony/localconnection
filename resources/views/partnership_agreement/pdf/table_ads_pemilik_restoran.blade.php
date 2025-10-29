<div class="card-body" id="printItem">
    <div class="row mb-4">
        <div class="col-6  pe-3 text-center">
            <h6><strong>PERJANJIAN KERJASAMA ANTARA </strong> <strong>ANTARA {{ $agreement->getFields(" nama_perusahaan_pertama") }} DENGAN {{ $agreement->getFields(" nama_perusahaan_pihak_kedua") }}</strong></h6> 
            <h6><strong>
                No: {{ $agreement->number_result }}
            </strong></h6>
        </div>
        <div class="col-6 ps-3 text-center">
            <h6><strong>COOPERATION AGREEMENT</strong> <strong>BETWEEN {{ $agreement->getFields(" nama_perusahaan_pertama") }} WITH {{ $agreement->getFields(" nama_perusahaan_pihak_kedua") ?? $agreement->getFields(" nama_pihak_kedua_individu")  }}</strong></h6>
            <h6>
                <strong>
                    No: {{ $agreement->number_result }}
                </strong>
            </h6>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-justify">
            <p>
            Perjanjian Kerjasama  (selanjutnya disebut <strong>“Perjanjian”</strong>) ini
                ditandatangani pada
                tanggal {{ \Carbon\Carbon::parse($agreement->date_agreement)->format('d-m-Y') }}, oleh dan antara:
            </p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p>
            The Cooperation Agreement (hereinafter referred to as the <strong>“Agreement”</strong>)
                is signed on
                {{ \Carbon\Carbon::parse($agreement->date_agreement)->format('d-m-Y') }}, by and between:
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    {{ $agreement->getFields(" nama_perusahaan_pertama") }}, suatu perseroan terbatas yang didirikan dan berdiri secara sah berdasarkan hukum
                    Indonesia yang berkedudukan di {{ $agreement->getFields(" alamat_perusahaan_pertama") }}, dalam hal ini diwakili oleh {{ $agreement->getFields(" nama_perwakilan_pertama") }} dalam kapasitasnya
                    sebagai {{ $agreement->getFields(" jabatan_perwakilan_pertama") }}, oleh karena itu sah bertindak untuk dan atas nama {{ $agreement->getFields(" entitas_di_wakili_pihak_pertama") }}, (untuk selanjutnya
                    disebut sebagai <strong>“Pihak Pertama”</strong>);
                </li>
                <li style="margin-bottom: 15px;">
                @if($agreement->getFields(" nama_perusahaan_pihak_kedua") && $agreement->getFields(" nama_perwakilan_pihak_kedua") && $agreement->getFields(" alamat_perusahaan_pihak_kedua" ))
                    <p>
                        {{ $agreement->getFields(" nama_perusahaan_pihak_kedua") }}, suatu perseroan terbatas yang didirikan dan berdiri secara sah berdasarkan hukum
                        Indonesia yang beralamat di {{ $agreement->getFields(" alamat_perusahaan_pihak_kedua") }}, dalam hal ini diwakili oleh {{ $agreement->getFields(" nama_perwakilan_pihak_kedua") }} sebagai {{ $agreement->getFields(" jabatan_perwakilan_pihak_kedua") }}, secara
                        sah bertindak untuk dan atas nama {{ $agreement->getFields(" entitas_di_wakili") }}, (untuk selanjutnya disebut sebagai “Pihak
                        kedua”).
                    </p>
                    @endif

                    @if($agreement->getFields(" nama_pihak_kedua_individu") && $agreement->getFields(" nomor_identitas_pihak_kedua_individu") && $agreement->getFields(" alamat_domisili_pihak_kedua_individu" ))
                    <p>
                        {{ $agreement->getFields(" nama_pihak_kedua_individu") }}, perorangan yang memiliki identitas dengan nomor {{ $agreement->getFields(" nomor_identitas_pihak_kedua_individu") }}, yang berdomisili di {{ $agreement->getFields(" alamat_domisili_pihak_kedua_individu") }},
                        mewakili dan bertanggung jawab untuk dan atas nama diri sendiri dan/atau {{ $agreement->getFields(" entitas_di_wakili_individu") }}, (untuk
                        selanjutnya disebut sebagai <strong>“Pihak kedua”</strong>).
                    </p>
                    @endif
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    {{ $agreement->getFields(" nama_perusahaan_pertama") }}, a limited liability company legally established and standing under Indonesian law
                    domiciled in {{ $agreement->getFields(" alamat_perusahaan_pertama") }}, in this case represented by {{ $agreement->getFields(" nama_perwakilan_pertama") }} its capacity as {{ $agreement->getFields(" jabatan_perwakilan_pertama") }}, therefore acting
                    for and on behalf of {{ $agreement->getFields(" entitas_di_wakili_pihak_pertama") }}, (hereinafter referred to as the <strong>“First Party”</strong>);
                </li>
                <li style="margin-bottom: 15px;">
                    @if($agreement->getFields(" nama_perusahaan_pihak_kedua") && $agreement->getFields(" nama_perwakilan_pihak_kedua") && $agreement->getFields(" alamat_perusahaan_pihak_kedua" ))
                    <p>
                        {{ $agreement->getFields(" nama_perusahaan_pihak_kedua") }}, a limited liability company legally established and standing under Indonesian law
                        domiciled in {{ $agreement->getFields(" alamat_perusahaan_pihak_kedua") }}, in this case represented by {{ $agreement->getFields(" nama_perwakilan_pihak_kedua") }} its capacity as {{ $agreement->getFields(" jabatan_perwakilan_pihak_kedua") }}, therefore
                        acting for and on behalf of {{ $agreement->getFields(" entitas_di_wakili") }}, (hereinafter referred to as the <strong>“Second
                            Party”</strong>)
                    </p>
                    @endif

                    @if($agreement->getFields(" nama_pihak_kedua_individu") && $agreement->getFields(" nomor_identitas_pihak_kedua_individu") && $agreement->getFields(" alamat_domisili_pihak_kedua_individu" ))
                    <p>
                        {{ $agreement->getFields(" nama_pihak_kedua_individu") }}, holder of Identity Card number {{ $agreement->getFields(" nomor_identitas_pihak_kedua_individu") }}, domiciled at {{ $agreement->getFields(" alamat_domisili_pihak_kedua_individu") }}, represent and responsible
                        for and on behalf of oneself and/or {{ $agreement->getFields(" entitas_di_wakili_individu") }}, (hereinafter referred to as the
                        <strong>“Second
                            Party”</strong>).
                    </p>
                    @endif
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-justify">
            <p>
                Pihak Pertama dan Pihak Kedua selanjutnya secara bersama-sama di dalam Perjanjian ini akan
                disebut sebagai <strong>“Para Pihak”</strong>, sedangkan masing-masing disebut
                <strong>”Pihak”</strong>.

            </p>
            <p>
                Para Pihak dalam kedudukannya masing-masing sebagaimana tersebut diatas, terlebih dahulu menerangkan sebagai berikut :
            </p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p>
                The First Party and the Second Party will hereinafter be referred to collectively in this
                Agreement as the "Parties", while each is referred to as a <strong>"Party"</strong>.
            </p>
            <p>
                The Parties in their respective standing as mentioned above, first explain as follows:
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                Bahwa Pihak Pertama adalah suatu perseroan terbatas yang bergerak dalam bidang teknologi informasi;  

                </li>
                <li style="margin-bottom: 15px;">
                    Bahwa Pihak kedua adalah suatu @if($agreement->getFields(" nama_perusahaan_pihak_kedua")) perseroan terbatas @else perorangan @endif yang memiliki usaha dibidang makanan dan minuman (<strong>“Pekerjaan”</strong>).

                </li>
                <li style="margin-bottom: 0x;">
                    Bahwa Pihak Pertama bermaksud untuk bekerja sama dengan Pihak kedua untuk {{ $agreement->getFields("tujuan_kerjasama_id ") }} dan Pihak
                    kedua dengan ini menyetujuinya dan Para Pihak sepakat untuk mengikatkan diri dalam sebuah
                    Perjanjian dengan syarat-syarat dan ketentuan-ketentuan kerjasama dalam Perjanjian ini.
                </li>

            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Whereas the First Party is a limited liability company engaged in the field of information technology;
                </li>
                <li style="margin-bottom: 15px;">
                    Whereas the Second Party is a limited liability @if($agreement->getFields(" nama_perusahaan_pihak_kedua")) company @else individual @endif with food and beverages business (<strong>"Work"</strong>).

                </li>
                <li style="margin-bottom: 0x;">
                    Whereas the First Party intends to cooperate with the Second Party for {{ $agreement->getFields("tujuan_kerjasama_en ") }} and the Second
                    Party hereby agrees to it and the Parties agree to bind in an Agreement with the terms and
                    conditions of this Agreement.
                </li>
            </ol>
        </div>
        <div class="col-6  pe-3 text-justify mt-0">
            <p>
                Berdasarkan hal-hal tersebut diatas, maka dengan ini Para Pihak telah setuju dan sepakat untuk
                membuat, menandatangani dan melaksanakan Perjanjian dengan syarat-syarat dan ketentuan-ketentuan
                yang diatur sebagai berikut:
            </p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p>
                Based on the above, the Parties hereby agree and consent to create, sign and implement the
                Agreement with the terms and conditions which are arranged as follows:
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 1
                </strong>
            </p>
            <p>
                <strong>
                    RUANG LINGKUP PEKERJAAN
                </strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    ARTICLE 1
                </strong>
            </p>
            <p>
                <strong>
                    SCOPE OF WORK
                </strong>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                Para Pihak sepakat untuk melakukan kerjasama berdasarkan ruang lingkup Perjanjian ini.
                </li>
                <li style="margin-bottom: 15px;">
                Pihak Kedua bersedia untuk memberikan tempat penempatan untuk produk Pihak Pertama yang dikenal sebagai Table Ads <strong>(“Produk”)</strong>.

                </li>
                <li style="margin-bottom: 15px;">
                    Pihak Kedua wajib mengikuti ketentuan yang ada dalam Perjanjian ini termasuk dan tidak terbatas pada syarat dan ketentuan yang telah disepakati sebelumnya oleh Para Pihak.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                The Parties agree to implement a cooperation based on scope of this Agreement.
                </li>
                <li style="margin-bottom: 15px;">
                The Second Party agrees to provide the placement for the First Party's product known as Table Ads <strong>("Product")</strong>.

                </li>
                <li style="margin-bottom: 15px;">
                The Second Party is obliged to follow the provisions of this Agreement including but not limited to the terms and conditions previously agreed upon by the Parties.
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 2
                </strong>
            </p>
            <p>
                <strong>
                    JANGKA WAKTU
                </strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    ARTICLE 2
                </strong>
            </p>
            <p>
                <strong>
                    TIME PERIOD
                </strong>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-justify">
            <p class="mb-0">
                Perjanjian berlaku selama {{ $agreement->getFields(" masa_berlaku_id") }} terhitung sejak tanggal {{ $agreement->date_agreement ? \Carbon\Carbon::parse($agreement->date_agreement)->format('d-m-Y') : '-' }} sampai dengan para pihak menyelesaikan
                kewajibannya masing-masing dan para pihak telah memperoleh masing-masing haknya.
            </p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p class="mb-0">
                This agreement is valid for a period of {{ $agreement->getFields(" masa_berlaku_en") }} starting from the date {{ $agreement->date_agreement ? \Carbon\Carbon::parse($agreement->date_agreement)->format('d-m-Y') : '-' }} until the parties
                have fulfilled their respective obligations and each party has received their respective rights.

            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 3
                </strong>
            </p>
            <p>
                <strong>
                    BIAYA DAN TATA CARA PEMBAYARAN
                </strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    ARTICLE 3
                </strong>
            </p>
            <p>
                <strong>
                    FEE AND PAYMENT METHOD
                </strong>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Hak dan kewajiban Pihak Pertama, sebagai berikut:
                    <ol type="a">
                        <li style="margin-bottom: 15px;">
                        Pihak Pertama berhak menyimpan 5 (lima)  Produk di lokasi Pihak Kedua.

                        </li>
                        <li style="margin-bottom: 15px;">
                        Pihak Pertama berhak menayangkan iklan pada Produk Pihak Pertama.

                        </li>
                        <li style="margin-bottom: 15px;">
                        Pihak Pertama wajib memberikan 6 (enam) <i>slot</i> dalam setiap Produk yang disimpan.

                        </li>
                        <li style="margin-bottom: 15px;">
                        Pihak Pertama wajib melakukan seleksi atas materi iklan yang akan ditayangkan di Produk.

                        </li>
                        <li style="margin-bottom: 15px;">
                        Pihak Pertama tidak akan memungut biaya kepada Pihak Kedua atas pengerjaan pemasangan unit dan/atau perbaikan atas kerusakan Produk yang tidak didasari oleh kesalahan Pihak Kedua.

                        </li>

                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                    Hak dan kewajiban Pihak Kedua, sebagai berikut:
                    <ol type="a">
                        <li style="margin-bottom: 15px;">
                        Pihak Kedua berhak mendapatkan 6 (enam) <i>slot</i> dalam setiap Produk yang disimpan.

                        </li>
                        <li style="margin-bottom: 15px;">
                        Pihak kedua berhak menolak atas penayangan iklan tertentu, apabila materinya tidak sesuai dengan usaha Pihak kedua.

                        </li>
                        <li style="margin-bottom: 15px;">
                        Pihak Kedua wajib menyediakan tempat untuk 5 (lima) Produk milik Pihak Pertama.

                        </li>
                        <li style="margin-bottom: 15px;">
                        Pihak Kedua wajib untuk tidak memindahkan Produk Pihak Pertama ke tempat yang tidak disetujui oleh Pihak Pertama.

                        </li>
                        <li style="margin-bottom: 15px;">
                        Pihak Kedua wajib bertanggung jawab terhadap keamanan Produk yang berada di lokasi Pihak Kedua.
                        </li>

                    </ol>
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
        <ol>
                <li style="margin-bottom: 15px;">
                    The rights and obligations of the First Party, as follows
                    <ol type="a">
                        <li style="margin-bottom: 15px;">
                        The First Party has the right to deposit 5 (five) Products at the Second Party's location.

                        </li>
                        <li style="margin-bottom: 15px;">
                        The First Party has the right to display advertisements on the First Party's Products.

                        </li>
                        <li style="margin-bottom: 15px;">
                        The First Party is required to provide 6 (six) slots in each Product stored.

                        </li>
                        <li style="margin-bottom: 15px;">
                        The First Party is required to select the advertising materials to be displayed on the Products.

                        </li>
                        <li style="margin-bottom: 15px;">
                        The First Party will not charge the Second Party for the installation of units and/or repair of any damage to the Products that is not caused by the Second Party's fault.

                        </li>

                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                The rights and obligations of the Second Party, as follows:
                <ol type="a">
                        <li style="margin-bottom: 15px;">
                        The Second Party has the right to receive 6 (six) slots in each Product stored.

                        </li>
                        <li style="margin-bottom: 15px;">
                        The Second Party has the right to refuse certain advertisements, when the content is not aligned with the Second Party's business.

                        </li>
                        <li style="margin-bottom: 15px;">
                        The Second Party is required to provide space for 5 (five) Products owned by the First Party.

                        </li>
                        <li style="margin-bottom: 15px;">
                        The Second Party is required not to move the First Party's Products to locations not approved by the First Party.

                        </li>
                        <li style="margin-bottom: 15px;">
                        The Second Party is required to be responsible for the security of the Products at the Second Party's location.
                        </li>

                    </ol>
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 4
                </strong>
            </p>
            <p>
                <strong>
                    BIAYA-BIAYA
                </strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    ARTICLE 4
                </strong>
            </p>
            <p>
                <strong>
                    FEES               
                 </strong>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                Pihak Kedua berhak menerima pendapatan dari penayangan iklan pada Produk yang ditempatkan di lokasi Pihak Kedua sebesar {{ $agreement->getFields(" presentase_pendapatan_penayangan") }}% dari pendapatan kotor pengiklanan.
                </li>
                <li style="margin-bottom: 15px;">
                    <p class="mb-0">Pembayaran oleh Pihak Pertama akan ditransfer paling lambat tanggal {{ $agreement->getFields("pembayaran_bagi_hasil ") ? \Carbon\Carbon::parse($agreement->getFields("pembayaran_bagi_hasil "))->format('d-m-Y') : '-' }}, melalui:</p>
                    <table style="border: 0">
                        <tr>
                            <td>
                                Nama Pemilik Rekening
                            </td>
                            <td>
                                : {{ $agreement->getFields(" nama_pemilik_rekening") }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Nama Bank
                            </td>
                            <td>
                                : {{ $agreement->getFields(" nama_bank") }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Kantor Cabang
                            </td>
                            <td>
                                : {{ $agreement->getFields(" kantor_cabang_bank") }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Nomor Rekening
                            </td>
                            <td>
                                : {{ $agreement->getFields(" nomor_rekening") }}
                            </td>
                        </tr>
                    </table>
                </li>
                <li style="margin-bottom: 15px;">
                    Para Pihak sepakat bahwa setiap dan seluruh pajak, baik pajak reklame maupun pajak penghasilan yang timbul dari terlaksananya Perjanjian ini, akan dibayarkan oleh Pihak Pertama.
                </li>
                <li style="margin-bottom: 15px;">
                    Para Pihak sepakat bahwa ketentuan Pasal 4.1 akan berlaku apabila Pihak Kedua memenuhi kewajiban untuk tetap menyimpan ditempat yang telah ditentukan dan memajang Produk sesuai dengan kesepakatan Para Pihak.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
            <li style="margin-bottom: 15px;">
            The Second Party has the right to receive revenue from the display of advertisements on the Products placed at the Second Party's location with amount of {{ $agreement->getFields(" presentase_pendapatan_penayangan") }}% advertising gross income.
            </li>
                <li style="margin-bottom: 15px;">
                    <p class="mb-0">
                        Payment by the First Party will be transferred no later than dated {{ $agreement->getFields("pembayaran_bagi_hasil ") ? \Carbon\Carbon::parse($agreement->getFields("pembayaran_bagi_hasil "))->format('d-m-Y') : '-' }}, through:
                    </p>
                    <table style="border: 0">
                        <tr>
                            <td>
                            Account Holder Name
                            </td>
                            <td>
                                : {{ $agreement->getFields(" nama_pemilik_rekening") }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Bank Name
                            </td>
                            <td>
                                : {{ $agreement->getFields("nama_bank ") }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Branch Office
                            </td>
                            <td>
                                : {{ $agreement->getFields(" kantor_cabang_bank") }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Account Number
                            </td>
                            <td>
                                : {{ $agreement->getFields("nomor_rekening ") }}
                            </td>
                        </tr>
                    </table>
                </li>
                <li style="margin-bottom: 15px;">
                The Parties agree that any and all taxes, either advertising taxes or income taxes arising from the implementation of this Agreement, will be paid by the First Party.
                </li>
                <li style="margin-bottom: 15px;">
                The Parties agree that the provisions of Article 4.1 will prevail if the Second Party fulfills the obligation to store at the determined place and display the Product in accordance with the Parties’ agreement.
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 5
                </strong>
            </p>
            <p>
                <strong>
                    HAK KEKAYAAN INTELEKTUAL
                </strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    ARTICLE 5
                </strong>
            </p>
            <p>
                <strong>
                    INTELLECTUAL PROPERTY RIGHTS
                </strong>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-6  pe-3 text-justify">
            Selama berlangsungnya Perjanjian ini, segala hak kekayaan intelektual yang diciptakan oleh Pihak
            kedua, termasuk namun tidak terbatas pada setiap hak cipta, hak merek, logo-logo, merek jasa, nama
            jasa, nama domain, karya sastra (artikel), karya audio, karya visual (video), karya audio visual dan
            juga termasuk setiap hak atas kekayaan intelektual lainnya yang timbul menurut hukum Republik
            Indonesia yang berlaku atau setiap negara, yang diciptakan selama bekerja untuk Pihak Pertama adalah
            menjadi hak milik mutlak dari Pihak Pertama.

        </div>
        <div class="col-6 ps-3 text-justify">
            During the period of this Agreement, any intellectual property rights created by the Second Party,
            including but not limited to copyrights, trademarks, logos, service marks, service names, domain
            names, literary works (articles), audio works, visual works (videos), audiovisual works, and any
            other intellectual property rights arising under the laws of the Republic of Indonesia or any other
            applicable country, created while working for the First Party, shall be the exclusive property of
            the First Party.

        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 6
                </strong>
            </p>
            <p>
                <strong>
                    KERAHASIAAN </strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    ARTICLE 6
                </strong>
            </p>
            <p>
                <strong>
                    CONFIDENTIALITY
                </strong>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-6  pe-3 text-justify">
            Untuk hal-hal yang telah disepakati Para Pihak dalam Perjanjian ini, Para Pihak masing-masing wajib
            menjaga kerahasiaan isi dan ketentuan dalam Perjanjian ini dan seluruh informasi atau data, baik
            secara lisan, elektronik atau tertulis yang diterima dari Pihak lainnya, dan tidak akan memberikan
            hal tersebut kepada pihak ketiga tanpa pemberitahuan dan persetujuan tertulis terlebih dahulu dari
            Pihak lainnya, kecuali diharuskan oleh hukum dan atas dasar instrumen pemerintah.

        </div>
        <div class="col-6 ps-3 text-justify">
            Otherwise agreed upon by the Parties in this Agreement, each Party is obligated to maintain the
            confidentiality of the contents and provisions of this Agreement, as well as any information or
            data, whether verbally, electronic, or written that is received from the other Party, neither Party
            shall disclose such information to any third party without prior written notice and consent from the
            other Party, unless when required by law or based on government instruments.

        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 7
                </strong>
            </p>
            <p>
                <strong>
                    PERNYATAAN DAN
                    JAMINAN

                </strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    ARTICLE 7
                </strong>
            </p>
            <p>
                <strong>
                    REPRESENTATIONS AND
                    WARRANTIES
                </strong>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-6  pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Para Pihak adalah subjek hukum yang sah berdasarkan hukum Negara Republik Indonesia dan berwenang untuk membuat Perjanjian ini.
                </li>
                <li style="margin-bottom: 15px;">
                Pihak Pertama tidak mengenakan denda atau biaya penggantian kepada Pihak kedua, apabila terjadi kehilangan Produk yang bukan disebabkan oleh kesalahan dan/atau kelalaian Pihak Kedua.

                </li>
                <li style="margin-bottom: 15px;">
                Pihak Kedua menjamin tidak akan menggunakan Produk untuk keperluan atau kepentingan lain di luar Perjanjian ini.

                </li>
                <li style="margin-bottom: 15px;">
                Pihak Kedua menjamin Pihak Pertama bahwa Pihak Kedua akan senantiasa menutup asuransi atas pencurian atau kerugian yang mungkin terjadi di gerainya dan setuju bahwa selama Produk berada dalam kekuasaannya, barang tersebut akan terjamin dalam asuransi tersebut.

                </li>
                <li style="margin-bottom: 15px;">
                Pihak Kedua wajib memberikan ganti rugi senilai Produk yang rusak apabila terbukti Produk yang disimpan di gerai Pihak Kedua mengalami kerusakan akibat kesalahan dan/atau kelalaian Pihak Kedua.
                </li>
                <li style="margin-bottom: 15px;">
                    Pihak Kedua dengan ini menyetujui dan menyanggupi untuk melaksanakan kewajibannya berdasarkan Perjanjian ini.
                </li>
                <li style="margin-bottom: 15px;">
                    Pihak Kedua menjamin bahwa apabila timbul gugatan dan/atau tuntutan akibat dari segala tindakan dan/atau pelanggaran yang dilakukan oleh Pihak Kedua, maka Pihak Kedua membebaskan Pihak Pertama dari segala bentuk tanggung jawab, dampak, dan ganti rugi akibat gugatan dan/atau tuntutan tersebut.
                </li>
                <li style="margin-bottom: 15px;">
                    Pihak Kedua menyatakan dan menjamin bahwa semua data atau informasi yang disampaikan kepada Pihak Pertama merupakan informasi yang benar.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                The Parties are legal entities recognized under the laws of the Republic of Indonesia and are authorized to enter into this Agreement.

                </li>
                <li style="margin-bottom: 15px;">
                The First Party will not impose any indemnity or replacement fees to the Second Party, in the event of loss of the Product that is not caused by the fault and/or negligence of the Second Party.

                </li>
                <li style="margin-bottom: 15px;">
                The Second Party guarantees that the Products will not be used for purposes or interests other than those specified in this Agreement.


                </li>
                <li style="margin-bottom: 15px;">
                The Second Party guarantees the First Party that it will always maintain insurance for any theft or damage that may occur over its outlets, and agrees that while the Product is in its possession, it will be covered by the said insurance.

                </li>
                <li style="margin-bottom: 15px;">
                The Second Party should provide compensation with the value of Product if it is proven that the Product stored at the Second Party's outlet has been damaged due to the Second Party's fault and/or negligence.

                </li>
                <li style="margin-bottom: 15px;">
                The Second Party hereby agrees and undertakes to implement the obligations under this Agreement.
                </li>
                <li style="margin-bottom: 15px;">
                 The Second Party guarantees that if any lawsuit and/or claim arises due to any actions and/or violations committed by the Second Party, the Second Party shall indemnify the First Party from any form of liability, impact, and compensation resulting from such lawsuit and/or claim.
                </li>
                <li style="margin-bottom: 15px;">
                    The Second Party declares and guarantees that all data or information provided to the First Party is accurate.
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 8
                </strong>
            </p>
            <p>
                <strong>
                    PENGAKHIRAN PERJANJIAN
                </strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    ARTICLE 8
                </strong>
            </p>
            <p>
                <strong>
                    TERMINATION OF AGREEMENT

                </strong>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-6  pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    <p class="mb-0">
                        Pihak Pertama dapat memutuskan Perjanjian ini tanpa perlu pemberitahuan kepada Pihak
                        Kedua, apabila:
                    </p>
                    <ol type="a">
                        <li style="margin-bottom: 15px;">
                            Pihak Kedua melanggar syarat-syarat dan/atau ketentuan-ketentuan dalam Perjanjian
                            ini; dan

                        </li>
                        <li style="margin-bottom: 15px;">
                            Terjadi pelanggaran hukum yang dilakukan oleh Pihak Kedua yang mengakibatkan Pihak
                            Kedua tidak dapat melaksanakan kewajibannya berdasarkan Perjanjian ini.
                        </li>
                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                    Pihak Kedua tidak dapat mengakhiri Perjanjian apabila pada Produk Pihak Pertama masih terdapat penayangan iklan.
                </li>
                <li style="margin-bottom: 15px;">
                    Apabila Pihak Kedua memutuskan mengakhiri Perjanjian ini lebih awal, maka Pihak Kedua wajib memberitahukan secara tertulis paling lambat 60 (enam puluh) hari sebelum tanggal efektif pengakhiran.
                </li>
                <li style="margin-bottom: 15px;">
                Para Pihak setuju dan sepakat untuk mengesampingkan berlakunya ketentuan Pasal 1266 Kitab Undang-Undang Hukum Perdata terhadap Perjanjian ini dalam hal diperlukan suatu putusan pengadilan untuk mengakhiri Perjanjian ini.  

                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    <p class="mb-0">
                        The First party may terminate this Agreement without any notice to the Second Party, if:
                    </p>
                    <ol type="a">
                        <li style="margin-bottom: 15px;">
                            The Second Party violates the terms and/or provisions of this Agreement; and

                        </li>
                        <li style="margin-bottom: 15px;">
                            There has been a legal violation committed by the Second Party, which results in the
                            Second Party being unable to fulfill its obligations under this Agreement.
                        </li>
                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                The Second Party may not terminate the Agreement if the First Party’s Products are still displaying advertisements.


                </li>
                <li style="margin-bottom: 15px;">
                 If the Second Party decides to early termination of this Agreement, the Second Party is obliged to provide written notice no later than 60 (sixty) days before the effective date of the termination.
                </li>
                <li style="margin-bottom: 15px;">
                    The Parties agree and consent to exclude the application of the provisions of Article 1266
                    of the Indonesian Civil Code to this Agreement, in the event that a court decision is
                    required to terminate this Agreement.
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 9
                </strong>
            </p>
            <p>
                <strong>
                    FORCE MAJEURE
                </strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    ARTICLE 9
                </strong>
            </p>
            <p>
                <strong>
                    FORCE MAJEURE

                </strong>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-6  pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Yang dimaksud dengan force majeure dalam Perjanjian ini adalah peristiwa yang terjadi di
                    luar kemampuan Para Pihak untuk mengatasinya, dan bukan disebabkan karena kesalahan ataupun
                    kelalaian Para Pihak, seperti antara lain, bencana alam, kebakaran, peperangan, huru hara,
                    pemberontakan, wabah, epidemi, pandemi, sabotase, dan tindakan pemerintah di bidang moneter,
                    yang secara langsung mengganggu pelaksanaan kewajiban Para Pihak dalam Perjanjian ini dan
                    dinyatakan oleh Pemerintah sebagai force majeure.

                </li>
                <li style="margin-bottom: 15px;">
                    Apabila terjadi force majeure sebagaimana di maksud dalam ayat 1 di atas, maka Pihak yang
                    berada dalam keadaan memaksa berkewajiban memberitahu kan Pihak lainnya dalam waktu
                    selambat-lambatnya 7 (tujuh) hari kalender.

                </li>
                <li style="margin-bottom: 15px;">
                    Force majeure sebagaimana dimaksud dalam Pasal ini tidak menghapuskan atau mengakhiri
                    Perjanjian ini serta Para Pihak wajib menyelesaikan kewajibannya masing-masing.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Force majeure in this Agreement means an event that occurs beyond the ability of the Parties
                    to overcome it, and is not caused by the fault or negligence of the Parties, such as, among
                    others, natural disasters, fires, wars, riots, rebellions, plagues, epidemics, pandemics,
                    sabotage, and government actions in the monetary sector, which directly disrupt the
                    implementation of the Parties' obligations in this Agreement and are declared by the
                    Government as force majeure.

                </li>
                <li style="margin-bottom: 15px;">
                    In the event of force majeure as in paragraph 1 above, the Party in force must notify the
                    other Party by 7 (seven) calendar days.

                </li>
                <li style="margin-bottom: 15px;">
                    Force majeure, as referred to in this Article, does not cancel or terminate this Agreement,
                    and the Parties are still obligated to fulfill their respective obligations.
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 10
                </strong>
            </p>
            <p>
                <strong>
                    KORESPONDENSI
                </strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    ARTICLE 10
                </strong>
            </p>
            <p>
                <strong>
                    CORRESPONDENCE

                </strong>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-6  pe-3 text-justify">
            <p class="mb-0">
                Setiap pemberitahuan dan komunikasi yang dibuat berdasarkan Perjanjian ini harus dibuat secara
                tertulis dan memberitahukan kepada masing-masing Pihak dengan alamat sebagai berikut:
            </p>
            <ol type="a">
                <li style="margin-bottom: 15px;">
                    <table style="border: 0; width: 100%">
                        <tr>
                            <td width="20%">Nama</td>
                            <td width="5%">:</td>
                            <td width="75%">{{ $agreement->getFields(" nama_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" alamat_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Telephone</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" telephone_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" email_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Up</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("up_perwakilan_pertama ") }}</td>
                        </tr>
                    </table>
                </li>
                <li style="margin-bottom: 15px;">
                    <table style="border: 0; width: 100%">
                        <tr>
                            <td width="20%">Nama</td>
                            <td width="5%">:</td>
                            <td width="75%">{{ $agreement->getFields("nama_perwakilan_pihak_kedua ") ?? $agreement->getFields("nama_pihak_kedua_individu ") }}</td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" alamat_perwakilan_pihak_kedua") ?? $agreement->getFields(" alamat_domisili_pihak_kedua_individu")  }}</td>
                        </tr>
                        <tr>
                            <td>Telephone</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" telephone_perwakilan_pihak_kedua") ?? $agreement->getFields(" telephone_perwakilan_pihak_kedua_individu")  }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" email_perwakilan_pihak_kedua") ?? $agreement->getFields(" email_perwakilan_pihak_kedua_individu")  }}</td>
                        </tr>
                        <tr>
                            <td>Up</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" up_perwakilan_pihak_kedua") ?? $agreement->getFields(" up_perwakilan_pihak_kedua_individu")  }}</td>
                        </tr>
                    </table>
                </li>
            </ol>
            <p class="mb-0">
                Atau kepada alamat lain atau nomor lain sebagaimana diberitahukan dari waktu ke waktu oleh
                masing-masing Pihak kepada Pihak lainnya dengan cara sebagaimana disebutkan di atas.
            </p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p class="mb-0">
                Any notice and communication made under this Agreement must be made in writing and notified to
                each Party at the following address:
            </p>
            <ol>
                <li style="margin-bottom: 15px;">
                    <table style="border: 0; width: 100%">
                        <tr>
                            <td width="20%">Name</td>
                            <td width="5%">:</td>
                            <td width="75%">{{ $agreement->getFields(" nama_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Address</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" alamat_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Phone</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" telephone_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" email_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Up</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("up_perwakilan_pertama ") }}</td>
                        </tr>
                    </table>
                </li>
                <li style="margin-bottom: 15px;">
                    <table style="border: 0; width: 100%">
                        <tr>
                            <td width="20%">Name</td>
                            <td width="5%">:</td>
                            <td width="75%">{{ $agreement->getFields("nama_perwakilan_pihak_kedua ") ?? $agreement->getFields("nama_pihak_kedua_individu ") }}</td>
                        </tr>
                        <tr>
                            <td>Address</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" alamat_perwakilan_pihak_kedua") ?? $agreement->getFields(" alamat_domisili_pihak_kedua_individu")  }}</td>
                        </tr>
                        <tr>
                            <td>Phone</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" telephone_perwakilan_pihak_kedua") ?? $agreement->getFields(" telephone_perwakilan_pihak_kedua_individu")  }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" email_perwakilan_pihak_kedua") ?? $agreement->getFields(" email_perwakilan_pihak_kedua_individu")  }}</td>
                        </tr>
                        <tr>
                            <td>Up</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" up_perwakilan_pihak_kedua") ?? $agreement->getFields(" up_perwakilan_pihak_kedua_individu")  }}</td>
                        </tr>
                    </table>
                </li>
            </ol>
            <p class="mb-0">
                Or to another address or other number as notified from time to time by each Party to the other
                Party in the manner as stated above.
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 11
                </strong>
            </p>
            <p>
                <strong>
                    KETERPISAHAN

                </strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    ARTICLE 11
                </strong>
            </p>
            <p>
                <strong>
                    SEVERABILITY

                </strong>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-6  pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Apabila sebagian Pasal dalam Perjanjian ini batal demi hukum atau dibatalkan, maka
                    pembatalan itu tidak akan membatalkan isi Pasal-Pasal lainnya atau tidak membatalkan
                    Perjanjian ini.

                </li>
                <li style="margin-bottom: 15px;">
                    Ketidakberlakuan pasal dan ketentuan tersebut sebagaimana dimaksud pada Pasal 11.1 ini,
                    tidak akan mempengaruhi berlakunya atau dapat dilaksanakannya setiap ketentuan lainnya dari
                    Perjanjian ini dan Para Pihak akan segera melakukan negosiasi untuk ketentuan pengganti,
                    jika diperlukan, yang akan dituangkan dalam Adendum yang menjadi bagian tak terpisahkan dari
                    Perjanjian ini.

                </li>
                <li style="margin-bottom: 15px;">
                    Apabila seluruh isi Pasal dalam Perjanjian ini dibatalkan, maka tidak akan membatalkan Pasal
                    Pengakhiran Perjanjian, Pasal mengenai Hukum Yang Berlaku dan Penyelesaian Perselisihan,
                    Pasal Korespondensi dan Pasal Keterpisahan ini.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    If some of the Articles in this Agreement are null and void or cancelled, then the
                    cancellation will not cancel the contents of the other Articles or cancel this Agreement.

                </li>
                <li style="margin-bottom: 15px;">
                    The invalidity of the articles and provisions as referred to in Article 11.1, will not
                    affect the validity or enforceability of any other provisions of this Agreement and the
                    Parties will immediately negotiate for replacement provisions, if necessary, which will be
                    stated in an Addendum which is an integral part of this Agreement.

                </li>
                <li style="margin-bottom: 15px;">
                    If the entire contents of the Articles in this Agreement are cancelled, then it will not
                    cancel the Termination of Agreement Article, the Article on Governing Law and Dispute
                    Resolution, the Correspondence Article and this Severability Article.
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 12
                </strong>
            </p>
            <p>
                <strong>
                    HUKUM YANG BERLAKU DAN PENYELESAIAN PERSELISIHAN

                </strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    ARTICLE 12
                </strong>
            </p>
            <p>
                <strong>
                    GOVERNING LAW AND DISPUTE RESOLUTION
                </strong>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-6  pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Pelaksanaan Perjanjian ini tunduk pada ketentuan dan peraturan perundang-undangan yang
                    berlaku menurut Hukum Republik Indonesia.

                </li>
                <li style="margin-bottom: 15px;">
                    Dalam hal terjadi perselisihan di antara Para Pihak mengenai pelaksanaan Perjanjian ini,
                    maka Para Pihak dengan didasari itikad baik sepakat untuk menyelesaikannya secara musyawarah
                    untuk mufakat.

                </li>
                <li style="margin-bottom: 15px;">
                    Dalam hal Para Pihak tidak dapat menyelesaikan sengketa(-sengketa) dalam waktu 30 (tiga
                    puluh) hari sejak tanggal suatu sengketa tersebut diajukan oleh suatu Pihak dan
                    diberitahukan kepada Pihak lainnya (atau suatu jangka waktu lain yang disepakati bersama
                    antara Para Pihak), sengketa harus diajukan ke dan secara final diselesaikan melalui
                    Pengadilan Negeri Jakarta Barat.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    The implementation of this Agreement is subject to the provisions and regulations applicable
                    according to the Laws of the Republic of Indonesia.

                </li>
                <li style="margin-bottom: 15px;">
                    In the event of a dispute between the Parties regarding the implementation of this
                    Agreement, the Parties in good faith agree to resolve it through deliberation to reach a
                    consensus.

                </li>
                <li style="margin-bottom: 15px;">
                    In the event that the Parties are unable to resolve the dispute(s) within 30 (thirty) days
                    from the date a dispute is submitted by a Party and notified to the other Party (or another
                    period mutually agreed upon by the Parties), the dispute must be submitted to and finally
                    resolved through the West Jakarta District Court.
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6  pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 13
                </strong>
            </p>
            <p>
                <strong>
                    LAIN LAIN

                </strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    ARTICLE 13
                </strong>
            </p>
            <p>
                <strong>
                    OTHERS

                </strong>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-6  pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Setiap perubahan yang akan dilakukan serta hal-hal yang belum cukup diatur dalam Perjanjian
                    ini akan ditetapkan kemudian secara musyawarah oleh Para Pihak serta akan dituangkan dalam
                    perjanjian tambahan (addendum), atau perjanjian pembaruannya/perubahannya yang disepakati
                    oleh Para Pihak dan merupakan suatu kesatuan dan bagian yang tidak dapat dipisahkan dari
                    Perjanjian ini.

                </li>
                <li style="margin-bottom: 15px;">
                    Seluruh addendum atau perjanjian pembaruannya/perubahannya atas Perjanjian ini sah apabila
                    ditandatangani oleh Para Pihak.

                </li>
                <li style="margin-bottom: 15px;">
                    Apabila berlaku peraturan perundang-undangan atau kebijakan pemerintah terhadap Perjanjian
                    ini, maka Para Pihak akan tunduk pada peraturan perundang-undangan atau kebijakan pemerintah
                    tersebut.
                </li>
                <li style="margin-bottom: 15px;">
                    Semua surat-surat, dokumen-dokumen yang menjadi lampiran yang disebutkan dan turut
                    disertakan dalam Perjanjian ini atau lampiran-lampiran/perjanjian tambahan yang akan dibuat
                    pada waktunya nanti di kemudian hari oleh Para Pihak merupakan satu kesatuan dan bagian yang
                    tidak dapat dipisahkan dari Perjanjian ini.
                </li>

            </ol>
            <p>
                Demikian perjanjian ini dibuat dan ditandatangani pada hari dan tanggal sebagaimana tersebut
                diatas, dibuat rangkap 2 (dua) masing-masing bermeterai cukup serta memiliki kekuatan hukum yang
                mengikat.
            </p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Any changes to be made and matters that are not sufficiently regulated in this Agreement
                    will be determined later through deliberation by the Parties and will be stated in an
                    additional agreement (addendum), or an agreement for renewal/amendment thereof agreed upon
                    by the Parties and will constitute an integral and inseparable part of this Agreement.

                </li>
                <li style="margin-bottom: 15px;">
                    All addendums or agreements for renewal/amendment thereof to this Agreement are valid if
                    signed by the Parties.

                </li>
                <li style="margin-bottom: 15px;">
                    If applicable laws or government policies apply to this Agreement, then the Parties will be
                    subject to such regulations or government policies.
                </li>
                <li style="margin-bottom: 15px;">
                    All letters, documents which are attachments mentioned and included in this Agreement or
                    attachments/additional agreements which will be made at the time by the Parties are an
                    integral part and cannot be separated from this Agreement.
                </li>
            </ol>
            <p>
                Thus this agreement is made and signed on the day and date as stated above, made in 2 (two)
                copies, each with stamped duty and having the same legal binding.

            </p>
        </div>
    </div>
    <!-- THIS DIE -->
    <div class="row mt-5 mb-5">
        <div class="col-5 text-justify">
            <p class="noMargin">
                <strong>
                    PIHAK PERTAMA/FIRST PARTY
                </strong>
            </p>
            <p class="noMargin">
                <strong>
                    {{ $agreement->getFields("nama_perusahaan_pertama") }}
                </strong>
            </p>
        </div>
        <div class="offset-2 col-5 text-justify">
            <p class="noMargin">
                <strong>
                PIHAK KEDUA/SECOND PARTY
            </strong>
            </p>
            <p class="noMargin">
                <strong>
                    {{ $agreement->getFields("nama_perusahaan_pihak_kedua") }}
                </strong>
            </p>
        </div>
    </div>

    <div class="row mt-5 mb-5">
        <div class="col-5 text-justify">
            @if($agreement->getSignature(1))
            <img src="{{ s3_asset(true,10,'public/'.$agreement->getSignature(1)->signature) }}" class="img-thumbnail img-signature">
            @else
            <div style="min-height: 80px; "></div>
            @endif
        </div>
        <div class="offset-2 col-5 text-justify">
            @if($agreement->getSignature(2))
                <img src="{{ s3_asset(true,10,'public/'.$agreement->getSignature(2)->signature) }}" class="img-thumbnail img-signature">
            @else
                <div style="min-height: 80px; "></div>
            @endif
        </div>
    </div>

    <div class="row mt-5 mb-5">
        <div class="col-5 text-justify">

            <p class="noMargin">
                <strong>
                    {{ $agreement->getFields("nama_perwakilan_pertama") }}
                </strong>
            </p>
            <p class="noMargin">
                <strong>
                    {{ $agreement->getFields("jabatan_perwakilan_pertama") }}
                </strong>
            </p>
        </div>
        <div class="offset-2 col-5 text-justify">
            <p class="noMargin">
                <strong>
                    {{ $agreement->getFields("nama_perwakilan_pihak_kedua") ?? $agreement->getFields("nama_pihak_kedua_individu")}}
                </strong>
            </p>
        </div>
    </div>
</div>
