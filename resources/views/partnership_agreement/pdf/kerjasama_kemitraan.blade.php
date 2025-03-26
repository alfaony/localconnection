<div class="card-body" id="printItem">
    <div class="row mb-4">
        <div class="col-6 pe-3 text-center">
            <h6><strong>PERJANJIAN KERJASAMA</strong> <strong>ANTARA
                    {{ $agreement->getFields(" nama_perusahaan_pertama") }} DENGAN
                    {{ $agreement->getFields(" nama_perusahaan_pihak_kedua") }}</h6>
            <h6>No: {{ $agreement->number_result }}</strong></h6>
        </div>
        <div class="col-6 ps-3 text-center">
            <h6><strong>CONSIGNMENT AGREEMENT</strong> <strong>BETWEEN
                    {{ $agreement->getFields(" nama_perusahaan_pertama") }} WITH
                    {{ $agreement->getFields(" nama_perusahaan_pihak_kedua") }}</h6>
            <h6>No: {{ $agreement->number_result }}</strong></h6>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p>
                Perjanjian Kerjasama (selanjutnya disebut <strong>“Perjanjian”</strong>) ini
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
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    {{ $agreement->getFields(" nama_perusahaan_pertama") }}, suatu perseroan terbatas yang didirikan dan
                    berdiri secara sah berdasarkan hukum
                    Indonesia yang berkedudukan di {{ $agreement->getFields(" alamat_perusahaan_pertama") }}, dalam hal
                    ini diwakili oleh {{ $agreement->getFields(" nama_perwakilan_pertama") }} dalam kapasitasnya
                    sebagai {{ $agreement->getFields(" jabatan_perwakilan_pertama") }}, oleh karena itu sah bertindak
                    untuk dan atas nama {{ $agreement->getFields(" entitas_di_wakili_pihak_pertama") }}, (untuk
                    selanjutnya
                    disebut sebagai <strong>“Pihak Pertama”</strong>);
                </li>
                <li style="margin-bottom: 15px;">
                    <p>
                        {{ $agreement->getFields(" nama_perusahaan_pihak_kedua") }}, suatu perseroan terbatas yang
                        didirikan dan berdiri secara sah berdasarkan hukum
                        Indonesia yang beralamat di {{ $agreement->getFields(" alamat_perusahaan_pihak_kedua") }}, dalam
                        hal ini diwakili oleh {{ $agreement->getFields(" nama_perwakilan_pihak_kedua") }} sebagai
                        {{ $agreement->getFields(" jabatan_perwakilan_pihak_kedua") }}, secara
                        sah bertindak untuk dan atas nama {{ $agreement->getFields(" entitas_di_wakili") }}, (untuk
                        selanjutnya disebut sebagai “Pihak
                        kedua”).
                    </p>
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    {{ $agreement->getFields(" nama_perusahaan_pertama") }}, a limited liability company legally
                    established and standing under Indonesian law
                    domiciled in {{ $agreement->getFields(" alamat_perusahaan_pertama") }}, in this case represented by
                    {{ $agreement->getFields(" nama_perwakilan_pertama") }} its capacity as
                    {{ $agreement->getFields(" jabatan_perwakilan_pertama") }}, therefore acting
                    for and on behalf of {{ $agreement->getFields(" entitas_di_wakili_pihak_pertama") }}, (hereinafter
                    referred to as the <strong>“First Party”</strong>);
                </li>
                <li style="margin-bottom: 15px;">
                    <p>
                        {{ $agreement->getFields(" nama_perusahaan_pihak_kedua") }}, a limited liability company legally
                        established and standing under Indonesian law
                        domiciled in {{ $agreement->getFields(" alamat_perusahaan_pihak_kedua") }}, in this case
                        represented by {{ $agreement->getFields(" nama_perwakilan_pihak_kedua") }} its capacity as
                        {{ $agreement->getFields(" jabatan_perwakilan_pihak_kedua") }}, therefore
                        acting for and on behalf of {{ $agreement->getFields(" entitas_di_wakili") }}, (hereinafter
                        referred to as the <strong>“Second
                            Party”</strong>)
                    </p>
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p>
                Pihak Pertama dan Pihak Kedua selanjutnya secara bersama-sama di dalam Perjanjian ini akan
                disebut sebagai <strong>“Para Pihak”</strong>, sedangkan masing-masing disebut
                <strong>”Pihak”</strong>.

            </p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p>
                The First Party and the Second Party will hereinafter be referred to collectively in this
                Agreement as the "Parties", while each is referred to as a <strong>"Party"</strong>.
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p>
                Para Pihak dalam kedudukannya masing-masing sebagaimana tersebut diatas, terlebih dahulu
                menerangkan sebagai berikut :
            </p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p>
                The Parties in their respective standing as mentioned above, first explain as follows:
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Bahwa Pihak Pertama adalah suatu perseroan yang bergerak di bidang penyelenggara jaringan dan jasa
                    telekomunikasi.
                </li>
                <li style="margin-bottom: 15px;">
                    Bahwa Pihak kedua adalah suatu perseroan yang bergerak di bidang jasa telekomunikasi.
                </li>
                <li style="margin-bottom: 0x;">
                    Bahwa Pihak Pertama bermaksud untuk bekerja sama dengan Pihak kedua untuk melakukan pembangunan
                    jaringan Fiber To The Home (FTTH), dengan ini menyetujuinya, Para Pihak sepakat untuk mengikatkan
                    diri dalam sebuah Perjanjian dengan syarat-syarat dan ketentuan-ketentuan kerjasama dalam Perjanjian
                    ini.

            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Whereas the First Party is a company engaged in the field of network and telecommunications
                    services.

                </li>
                <li style="margin-bottom: 15px;">
                    Whereas the Second Party is a company engaged in telecommunications services.
                </li>
                <li style="margin-bottom: 0x;">
                    Whereas the First Party intends to collaborate with Party Two in the development of a Fiber To The
                    Home (FTTH) network, and hereby agrees to do so, the Parties agree to be bound by the terms and
                    conditions of this Agreement.

                </li>
            </ol>
        </div>
        <div class="col-6 pe-3 text-justify mt-0">
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
        <div class="col-6 pe-3 text-center">
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
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Para Pihak sepakat bahwa Pihak Kedua akan melakukan kerjasama pembangunan jaringan Fiber To The Home
                    (FTTH) <strong>(“Pembangunan FTTH”)</strong>.
                </li>
                <li style="margin-bottom: 15px;">
                    Para Pihak sepakat untuk menyewakan Fiber To The Home (FTTH) kepada pelanggan dan/atau pihak ketiga,
                    dimana Pihak Kedua akan mendapatkan persentase keuntungan <strong>(“Proyek”)</strong>.
                </li>
                <li style="margin-bottom: 15px;">
                    Para Pihak sepakat Pembangunan FTTH ditentukan di lokasi yang kemudian akan disepakati oleh Para
                    Pihak.

                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    The Parties agree that Second Party will undertake the collaboration for the development of Fiber To
                    The Home (FTTH) network <strong>("FTTH Development")</strong>.
                </li>
                <li style="margin-bottom: 15px;">
                    The Parties agree to lease the Fiber To The Home (FTTH) to customers and/or third parties, with
                    Party Two receiving a percentage of the profits <strong>("Project")</strong>.
                </li>
                <li style="margin-bottom: 15px;">
                    The Parties agree that the FTTH development will be carried out at a location to be mutually agreed
                    upon by the Parties.
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-center">
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
        <div class="col-6 pe-3 text-justify">
            <p class="mb-0">
                Perjanjian ini mulai berlaku selama {{ $agreement->getFields("masa_berlaku_id") }} terhitung efektif
                sejak tanggal
                {{ $agreement->getFields("tanggal_mulai_perjanjian") ? \Carbon\Carbon::parse($agreement->getFields("tanggal_mulai_perjanjian"))->format('d-m-Y') : '-' }}
                sampai dengan tanggal
                {{ $agreement->getFields("tanggal_berakhir_perjanjian") ? \Carbon\Carbon::parse($agreement->getFields("tanggal_berakhir_perjanjian"))->format('d-m-Y') : '-' }}
                dan/atau sampai dengan para pihak menyelesaikan kewajibannya masing-masing dan para pihak telah
                memperoleh masing-masing haknya.
            </p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p class="mb-0">
                This agreement is valid for {{ $agreement->getFields("masa_berlaku_en") }} years effective from
                {{ $agreement->getFields("tanggal_mulai_perjanjian") ? \Carbon\Carbon::parse($agreement->getFields("tanggal_mulai_perjanjian"))->format('d-m-Y') : '-' }}
                until
                {{ $agreement->getFields("tanggal_berakhir_perjanjian") ? \Carbon\Carbon::parse($agreement->getFields("tanggal_berakhir_perjanjian"))->format('d-m-Y') : '-' }}
                and/or the parties have fulfilled their respective obligations and each party has received their
                respective rights. </p>
        </div>
    </div>


    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 3
                </strong>
            </p>
            <p>
                <strong>
                    HAK DAN KEWAJIBAN PARA PIHAK

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
                    RIGHTS AND OBLIGATIONS OF THE PARTIES

                </strong>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    <p>Hak dan Kewajiban Pihak Pertama, sebagai berikut:</p>
                    <ol type="a">
                        <li style="margin-bottom: 15px;">
                            Pihak Pertama berhak mendapatkan pembiayaan atas Pembangunan FTTH dari Pihak Kedua.

                        </li>
                        <li style="margin-bottom: 15px;">
                            Pihak Pertama wajib memberikan upaya terbaiknya dalam melakukan dan menyelesaikan
                            Pembangunan FTTH sesuai jangka waktu yang telah ditentukan.

                        </li>
                        <li style="margin-bottom: 15px;">
                            Pihak Pertama wajib memberikan laporan Pembangunan FTTH <strong>(“Laporan”)</strong> secara
                            berkala paling lambat setiap tanggal diakhir bulan berjalan.

                        </li>
                        <li style="margin-bottom: 15px;">
                            Pihak Pertama wajib mengembalikan Dana Pembiayaan kepada Pihak Pertama berdasarkan Lampiran
                            1 pada Perjanjian ini.

                        </li>
                        <li style="margin-bottom: 15px;">
                            Pihak Pertama wajib melaksanakan pekerjaan semaksimal mungkin, dengan itikad baik, dan
                            profesional.

                        </li>
                        <li style="margin-bottom: 15px;">
                            Pihak Pertama wajib membebaskan Pihak Kedua dari segala bentuk tanggung jawab, dampak, dan
                            ganti rugi akibat gugatan dan/atau tuntutan akibat dari segala tindakan dan/atau pelanggaran
                            yang dilakukan oleh Pihak Pertama, serta membersihkan nama baik Pihak Kedua.
                        </li>
                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                    <p>
                        Hak dan Kewajiban Pihak Kedua, sebagai berikut:

                    </p>
                    <ol type="a">
                        <li style="margin-bottom: 15px;">
                            Pihak Kedua berhak menerima hasil Pembangunan FTTH oleh Pihak Pertama secara tepat waktu.

                        </li>
                        <li style="margin-bottom: 15px;">
                            Pihak Kedua berhak atas pengembalian Dana Pembiayaan atas Pembangunan FTTH yang
                            perhitungannya tercantum pada Lampiran 1 Perjanjian ini.

                        </li>
                        <li style="margin-bottom: 15px;">
                            Pihak Kedua berhak menerima persentase Bagi Hasil yang perhitungannya tercantum pada
                            Lampiran 1 Perjanjian ini.

                        </li>
                        <li style="margin-bottom: 15px;">
                            Pihak Kedua berhak menerima Laporan dari Pihak Pertama paling lambat di tanggal akhir bulan
                            berjalan.
                        </li>
                        <li style="margin-bottom: 15px;">
                            Pihak Kedua wajib memenuhi pembiayaan Pembangunan FTTH berdasarkan kesepakatan Para Pihak.
                        </li>
                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                    Para Pihak bertanggung jawab terhadap pemeliharaan infrastruktur FTTH termasuk biaya operasional
                    yang dikeluarkan saat pemeliharaan maupun perbaikan.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    <p>
                        The Rights and Obligations of the First Party, as follows
                    </p>
                    <ol type="a">
                        <li style="margin-bottom: 15px;">
                            The First Party has the right to receive financing for FTTH Development from the Second
                            Party.

                        </li>
                        <li style="margin-bottom: 15px;">
                            The First Party is required to provide its best efforts in carrying out and completing FTTH
                            Development according to the specified period.

                        </li>
                        <li style="margin-bottom: 15px;">
                            The First Party is required to provide a periodic FTTH Development report <strong>(“Report”)</strong> no
                            later than the end of the following month.

                        </li>
                        <li style="margin-bottom: 15px;">
                            The First Party is required to return the Financing Funds to the First Party based on
                            Appendix 1 to this Agreement.

                        </li>
                        <li style="margin-bottom: 15px;">
                            The First Party is required to implement the work as much as possible, in good faith, and
                            professionally.

                        </li>
                        <li style="margin-bottom: 15px;">
                            The First Party is required to release the Second Party from all forms of responsibility,
                            impacts, and indemnity due to claims and/or demands of all actions and/or infringement by
                            the First Party, and restore the Second Party’s reputation.
                        </li>
                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                    <p>
                        The Rights and Obligations of the Second Party, as follows:

                    </p>
                    <ol type="a">
                        <li style="margin-bottom: 15px;">
                            The Second Party has the right to receive the results of the FTTH Development by the First
                            Party immediately.

                        </li>
                        <li style="margin-bottom: 15px;">
                            The Second Party has the right to repayment of the Financing Funds for the FTTH Development,
                            the calculation of which is outlined in Appendix 1 of this Agreement.

                        </li>
                        <li style="margin-bottom: 15px;">
                            The Second Party has the right to receive the Profit Sharing percentage, the calculation of
                            which is outlined in Appendix 1 of this Agreement.

                        </li>
                        <li style="margin-bottom: 15px;">
                            The Second Party has the right to receive the Report from the First Party no later than the
                            end of the following month.
                        </li>
                        <li style="margin-bottom: 15px;">
                            The Second Party is required to fulfill the financing for the FTTH Development based on the
                            agreement of the Parties.
                        </li>
                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                    The Parties are responsible for the maintenance of FTTH infrastructure including operational fees
                    during maintenance and repairs.
                </li>
            </ol>
        </div>
    </div>


    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 4
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
                    ARTICLE 4
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
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Segala bentuk biaya dan ketentuan pembayaran tercantum pada Lampiran 1 Perjanjian ini.
                </li>
                <li style="margin-bottom: 15px;">
                    Para Pihak sepakat untuk membuka rekening bersama dalam melaksanakan ketentuan dan aktivitas
                    keuangan pada Perjanjian ini.

                </li>
                <li style="margin-bottom: 15px;">
                    Para Pihak sepakat bahwa pengakhiran Perjanjian ini tidak menghapuskan kewajiban perpajakan
                    masing-masing Pihak yang dapat berlaku berdasarkan hukum berlaku.
                </li>
                <li style="margin-bottom: 15px;">
                    Para Pihak akan bertanggung jawab untuk pembayaran pajak masing-masing dan/atau untuk persyaratan
                    administratif yang berkaitan dengan pajak tersebut, serta membayar semua jenis pajak tepat waktu
                    sesuai dengan ketentuan perpajakan yang berlaku.
                </li>
                <li style="margin-bottom: 15px;">
                    Dalam hal Pihak Pertama mengalami kesulitan keuangan, maka penanggung jawab dari Pihak Pertama dalam
                    hal ini {{ $agreement->getFields("penanggung_jawab_id") }}, menjaminkan harta pribadi sebagai jaminan untuk pelunasan atau pengembalian uang,
                    sesuai yang tertuang pada Akta Jaminan Perorangan, yang tidak terpisahkan dengan perjanjian ini.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    All fees and payment method are outlined in Appendix 1 of this Agreement.
                </li>
                <li style="margin-bottom: 15px;">
                    The Parties agree to open a joint account for the implementation of the financial requirements and
                    activities under this Agreement.

                </li>
                <li style="margin-bottom: 15px;">
                    The Parties agree that the termination of this Agreement does not remove any tax obligations of each
                    Party that may apply under the applicable laws.
                </li>
                <li style="margin-bottom: 15px;">
                    The Parties will be responsible for the payment of their respective taxes and/or for any
                    administrative requirements that are related to taxes, and will pay all types of taxes on time
                    following applicable tax regulations.
                </li>
                <li style="margin-bottom: 15px;">
                    In the event the First party enters financial trouble, the responsible person from the First Party,
                    {{ $agreement->getFields("penanggung_jawab_en") }}, will pledge the personal assets as collateral for the settlement or repayment of funds, which
                    will be stated in the Personal Guarantee Deed, which is inseparable from this Agreement.
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-center">
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
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Selama berlangsungnya Perjanjian ini, segala hak kekayaan intelektual yang diciptakan oleh Pihak
                    Pertama yaitu Pembangunan FTTH termasuk dengan barang yang menjadi bagian dari Pembangunan FTTH akan
                    menjadi milik Para Pihak.
                </li>
                <li style="margin-bottom: 15px;">
                    Apabila Pihak Pertama bermaksud melakukan kerjasama dengan pihak lain dan/atau pihak ketiga, Pihak
                    Kedua tetap berhak atas persentase Bagi Hasil.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    During the term of this Agreement, all intellectual property rights created by the First Party,
                    including the FTTH Development and any items that are part of the FTTH Development, shall belong to
                    both Parties.
                </li>
                <li style="margin-bottom: 15px;">
                    When the First Party intends to enter into a partnership with another party and/or third party, the
                    Second Party shall retain the right to a percentage of the profit share.
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-center">
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
        <div class="col-6 pe-3 text-justify">
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
        <div class="col-6 pe-3 text-center">
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
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Para Pihak adalah subjek hukum yang sah berdasarkan hukum Negara Republik Indonesia dan
                    berwenang untuk membuat Perjanjian ini.

                </li>
                <li style="margin-bottom: 15px;">
                    Para Pihak menjamin untuk melaksanakan semua ketentuan dalam Perjanjian ini.
                </li>
                <li style="margin-bottom: 15px;">
                    Para Pihak dengan ini menyetujui dan menyanggupi untuk melaksanakan kewajibannya berdasarkan Perjanjian ini.
                </li>
                <li style="margin-bottom: 15px;">
                    Para Pihak menyatakan dan menjamin bahwa semua data atau informasi yang disampaikan kepada Pihak Pertama merupakan informasi yang benar.
                </li>
                <li style="margin-bottom: 15px;">
                    Pihak Pertama menjamin bahwa apabila timbul gugatan dan/atau tuntutan akibat dari segala tindakan dan/atau pelanggaran yang dilakukan oleh Pihak Pertama, maka Pihak Pertama membebaskan Pihak Kedua dari segala bentuk tanggung jawab, dampak, dan ganti rugi akibat gugatan dan/atau tuntutan tersebut.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    The Parties are legal entities recognized under the laws of the Republic of Indonesia and
                    are authorized to enter into this Agreement.

                </li>
                <li style="margin-bottom: 15px;">
                    The Parties guarantee to fulfill all provisions of this Agreement.
                </li>
                <li style="margin-bottom: 15px;">
                    The Parties hereby agree and undertake to implement the obligations under this Agreement.
                </li>
                <li style="margin-bottom: 15px;">
                    The Parties declare and guarantee that all data or information provided to the First Party is accurate.
                </li>
                <li style="margin-bottom: 15px;">
                    The First Party warrants that, in the event of any lawsuit and/or claim arising from any act and/or violation committed by the First Party, the First Party shall hold the Second Party harmless from any form of liability, consequences, and compensation resulting from such lawsuit and/or claim.
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-center">
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
        <div class="col-6 pe-3 text-justify">
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
                    Apabila Pihak Pertama memutuskan mengakhiri Perjanjian ini lebih awal, maka Pihak Pertama wajib
                    memberitahukan secara tertulis paling lambat 60 (enam puluh) hari sebelum tanggal efektif
                    pengakhiran dan Pihak Pertama wajib mengembalikan Dana Pembiayaan seluruhnya ditambah denda sebesar
                    100% (seratus persen) dari Dana Pembiayaan yang telah ditransfer oleh Pihak Pertama.
                </li>
                <li style="margin-bottom: 15px;">
                    Para Pihak setuju dan sepakat untuk mengesampingkan berlakunya ketentuan Pasal 1266 Kitab
                    Undang-Undang Hukum Perdata terhadap Perjanjian ini dalam hal diperlukan suatu putusan
                    pengadilan untuk mengakhiri Perjanjian ini.
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
                    When the First Party decides to terminate this Agreement earlier, the First Party should notify the
                    Second Party through written letter at least 60 (sixty) days prior to the effective termination date
                    and the First Party must repay the entire Financing Funds along with a penalty of 100% (one hundred
                    percent) of the Financing Funds that have been transfered by the First Party.

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
        <div class="col-6 pe-3 text-center">
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
        <div class="col-6 pe-3 text-justify">
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
        <div class="col-6 pe-3 text-center">
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
        <div class="col-6 pe-3 text-justify">
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
                            <td width="75%">
                                {{ $agreement->getFields("nama_perwakilan_pihak_kedua ") ?? $agreement->getFields("nama_pihak_kedua_individu ") }}
                            </td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" alamat_perwakilan_pihak_kedua") ?? $agreement->getFields(" alamat_domisili_pihak_kedua_individu")  }}
                            </td>
                        </tr>
                        <tr>
                            <td>Telephone</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" telephone_perwakilan_pihak_kedua") ?? $agreement->getFields(" telephone_perwakilan_pihak_kedua_individu")  }}
                            </td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" email_perwakilan_pihak_kedua") ?? $agreement->getFields(" email_perwakilan_pihak_kedua_individu")  }}
                            </td>
                        </tr>
                        <tr>
                            <td>Up</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" up_perwakilan_pihak_kedua") ?? $agreement->getFields(" up_perwakilan_pihak_kedua_individu")  }}
                            </td>
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
            <ol type="a">
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
                            <td width="75%">
                                {{ $agreement->getFields("nama_perwakilan_pihak_kedua ") ?? $agreement->getFields("nama_pihak_kedua_individu ") }}
                            </td>
                        </tr>
                        <tr>
                            <td>Address</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" alamat_perwakilan_pihak_kedua") ?? $agreement->getFields(" alamat_domisili_pihak_kedua_individu")  }}
                            </td>
                        </tr>
                        <tr>
                            <td>Phone</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" telephone_perwakilan_pihak_kedua") ?? $agreement->getFields(" telephone_perwakilan_pihak_kedua_individu")  }}
                            </td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" email_perwakilan_pihak_kedua") ?? $agreement->getFields(" email_perwakilan_pihak_kedua_individu")  }}
                            </td>
                        </tr>
                        <tr>
                            <td>Up</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" up_perwakilan_pihak_kedua") ?? $agreement->getFields(" up_perwakilan_pihak_kedua_individu")  }}
                            </td>
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
        <div class="col-6 pe-3 text-center">
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
        <div class="col-6 pe-3 text-justify">
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
        <div class="col-6 pe-3 text-center">
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
        <div class="col-6 pe-3 text-justify">
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
        <div class="col-6 pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 13
                </strong>
            </p>
            <p>
                <strong>
                    LAMPIRAN-LAMPIRAN


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
                    APPENDIX

                </strong>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Lampiran adalah ketentuan-ketentuan khusus yang merupakan satu kesatuan dan bagian yang tidak
                    terpisahkan dengan Perjanjian ini serta mempunyai kekuatan hukum yang sama dengan Perjanjian ini
                    serta mengikat Para Pihak.
                </li>
                <li style="margin-bottom: 15px;">
                    <p>
                        Lampiran-lampiran dalam Perjanjian ini, sebagai berikut:
                    </p>
                    <ol type="a">
                        <li style="margin-bottom: 15px;">
                            Lampiran 1 : Dana Pembiayaan dan Bagi Hasil
                        </li>
                        <li style="margin-bottom: 15px;">
                            Lampiran 2 : Topologi Jaringan
                        </li>
                        <li style="margin-bottom: 15px;">
                            Lampiran 3 : Formulir BAST RFS
                        </li>
                        <li style="margin-bottom: 15px;">
                            Lampiran 4 : Lokasi
                        </li>
                    </ol>

                </li>

            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    The Appendix are specific provisions that form an integral part of this Agreement and have the same
                    legal force as the Agreement itself and are binding on the Parties.
                </li>
                <li style="margin-bottom: 15px;">
                    <p>
                        The appendix to this Agreement is as follows:
                    </p>
                    <ol type="a">
                        <li style="margin-bottom: 15px;">
                            Appendix 1: Financing Funds and Profit Sharing
                        </li>
                        <li style="margin-bottom: 15px;">
                            Appendix 2: Network Topology
                        </li>
                        <li style="margin-bottom: 15px;">
                            Appendix 3: RFS BAST Form
                        </li>
                        <li style="margin-bottom: 15px;">
                            Appendix 4: Location
                        </li>
                    </ol>
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 14
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
                    ARTICLE 14
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
        <div class="col-6 pe-3 text-justify">
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
                    {{ $agreement->getFields("nama_perusahaan_pihak_kedua" ) }}
                </strong>
            </p>
        </div>
    </div>

    <div class="row mt-5 mb-5">
        <div class="col-5 text-justify">
            @if($agreement->getSignature(1))
            <img src="{{ Storage::url('public/'.$agreement->getSignature(1)->signature) }}" class="img-thumbnail img-signature">
            @else
            <div style="min-height: 80px; "></div>
            @endif
        </div>
        <div class="offset-2 col-5 text-justify">
            @if($agreement->getSignature(2))
                <img src="{{ Storage::url('public/'.$agreement->getSignature(2)->signature) }}" class="img-thumbnail img-signature">
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
    <div style="page-break-after: always;"></div>

    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0">
                <strong>
                    Lampiran 1
                </strong>
            </p>
            <p>
                <strong>
                    DANA PEMBIAYAAN DAN BAGI HASIL
                </strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    LAMPIRAN 1

                </strong>
            </p>
            <p>
                <strong>
                    FINANCING FUNDS AND PROFIT SHARING
                </strong>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol type="A">
                <li style="margin-bottom: 15px;">
                    <p class="mb-0">
                        <strong>
                            Dana Pembiayaan
                        </strong>
                    </p>
                    <ol>
                        <li style="margin-bottom: 15px;">
                            Pihak Kedua akan memberikan pembiayaan <strong>(“Dana Pembiayaan”)</strong> kepada Pihak
                            Pertama terhadap Pembangunan FTTH dengan Dana Pembiayaan sebesar {{ $agreement->getFields("dana_pembiayaan") ? 'Rp ' . number_format($agreement->getFields("dana_pembiayaan"), 0, ',', '.') : '-' }}.
                        </li>
                        <li style="margin-bottom: 15px;">
                            Pihak Kedua akan melakukan pembayaran paling lambat {{ $agreement->getFields("pembayaran_paling_lambat") }} hari setelah Perjanjian
                            ditandatangani oleh Para Pihak.
                        </li>
                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                    <strong>
                        Bagi Hasil
                    </strong>
                    <ol>
                        <li style="margin-bottom: 15px;">
                            Para Pihak sepakat perhitungan persentase keuntungan <strong>(“Bagi Hasil”)</strong>
                            dihitung berdasarkan pendapatan bersih yang diterima dari penyewaan FTTH kepada pelanggan
                            dan/atau pihak ketiga.
                        </li>
                        <li style="margin-bottom: 15px;">
                            Para Pihak sepakat skema Bagi Hasil adalah sebagai berikut:
                            <ol type="a">
                                <li style="margin-bottom: 15px;">
                                    Pihak Pertama sebesar {{ $agreement->getFields("presentase_bagi_hasil_pihak_pertama") }}%
                                </li>
                                <li style="margin-bottom: 15px;">
                                    Pihak Kedua sebesar {{ $agreement->getFields("presentase_bagi_hasil_pihak_kedua") }}%
                                </li>
                            </ol>
                        </li>
                        <li style="margin-bottom: 15px;">
                            Pembayaran atas Bagi Hasil akan dilakukan setiap bulannya dengan cara transfer dari rekening
                            bersama oleh Pihak Pertama kepada masing-masing Pihak paling lambat setiap tanggal {{ $agreement->getFields(" tangga_pembayaran_paling_lambat") }} pada
                            bulan berikutnya.

                        </li>
                        <li style="margin-bottom: 15px;">
                            <p>Pembayaran akan ditransfer dari rekening bersama oleh Pihak Pertama berdasarkan rekening
                                sebagai berikut:</p>
                            <strong>
                                PIHAK PERTAMA
                            </strong>
                            <table style="border: 0">
                                <tr>
                                    <td>
                                        Nomor Rekening
                                    </td>
                                    <td>
                                        : {{ $agreement->getFields(" nomor_rekening") }}
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
                                        Atas nama </td>
                                    <td>
                                        : {{ $agreement->getFields(" nama_pemilik_rekening") }}
                                    </td>
                                </tr>
                            </table>
                        </li>
                    </ol>
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol type="A">
                <li style="margin-bottom: 15px;">
                    <p class="mb-0">
                        <strong>
                            Financing Funds
                        </strong>
                    </p>
                    <ol>
                        <li style="margin-bottom: 15px;">
                            The Second Party will provide financing <strong>("Financing Funds")</strong> to the First
                            Party for the FTTH Development, with the Financing Funds amounting to Rp {{ $agreement->getFields("dana_pembiayaan") ? 'Rp ' . number_format($agreement->getFields("dana_pembiayaan"), 0, ',', '.') : '-' }}.
                        </li>
                        <li style="margin-bottom: 15px;">
                            The Second Party will do the payment no later than {{ $agreement->getFields(" pembayaran_paling_lambat") }} days after the Agreement has been
                            signed by the Parties.
                        </li>
                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                    <strong>
                        Profit Sharing
                    </strong>
                    <ol>
                        <li style="margin-bottom: 15px;">
                            The Parties agree that the profit-sharing percentage <strong>("Profit Sharing"</strong>)
                            will be calculated based on the net income received from the FTTH rent to customers and/or
                            third parties.
                        </li>
                        <li style="margin-bottom: 15px;">
                            he Parties agree that the Profit Sharing scheme is as follows:
                            <ol type="a">
                                <li style="margin-bottom: 15px;">
                                    The First Party of {{ $agreement->getFields(" presentase_bagi_hasil_pihak_pertama") }}%
                                </li>
                                <li style="margin-bottom: 15px;">
                                    The Second Party of {{ $agreement->getFields(" presentase_bagi_hasil_pihak_kedua") }}%
                                </li>
                            </ol>
                        </li>
                        <li style="margin-bottom: 15px;">
                            Payments for Profit Sharing will be made monthly by transfer from the joint account by Party
                            One to each Party, no later than the {{ $agreement->getFields(" tangga_pembayaran_paling_lambat") }} of the next month.
                        </li>
                        <li style="margin-bottom: 15px;">
                            <p>
                                The payment will be transferred from the joint account by Party One based on the
                                following account details:
                            </p>
                            <strong>
                                THE FIRST PARTY
                            </strong>
                            <table style="border: 0">
                                <tr>
                                    <td>
                                        Account no. </td>
                                    <td>
                                        : {{ $agreement->getFields(" nomor_rekening") }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Bank
                                    </td>
                                    <td>
                                        : {{ $agreement->getFields(" nama_bank") }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Branch
                                    </td>
                                    <td>
                                        : {{ $agreement->getFields(" kantor_cabang_bank") }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Holder Name
                                    </td>
                                    <td>
                                        : {{ $agreement->getFields(" nama_pemilik_rekening") }}
                                    </td>
                                </tr>
                            </table>
                        </li>
                    </ol>
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0">
                <strong>
                    LAMPIRAN 2
                </strong>
            </p>
            <p>
                <strong>
                    TOPOLOGI JARINGAN
                </strong>
            </p>
            @if ($agreement->getFields('image_topologi'))
                <img src="{{ Storage::url($agreement->getFields('image_topologi')) }}" class="img-thumbnail" alt="topologi jaringan">
            @endif
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    LAMPIRAN 2

                </strong>
            </p>
            <p>
                <strong>
                    TOPOLOGY NETWORK
                </strong>
            </p>
            @if ($agreement->getFields('image_topologi'))
                <img src="{{ Storage::url($agreement->getFields('image_topologi')) }}" class="img-thumbnail" alt="topologi jaringan">
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0">
                <strong>
                    LAMPIRAN 3
                </strong>
            </p>
            <p>
                <strong>
                    FORMULIR BAST RFS
                </strong>
            </p>
            @if ($agreement->getFields('image_bast'))
                <img src="{{ Storage::url($agreement->getFields('image_bast')) }}" class="img-thumbnail" alt="topologi jaringan">
            @endif
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    LAMPIRAN 3

                </strong>
            </p>
            <p>
                <strong>
                    RFS BAST FORM
                </strong>
            </p>
            @if ($agreement->getFields('image_bast'))
                <img src="{{ Storage::url($agreement->getFields('image_bast')) }}" class="img-thumbnail" alt="topologi jaringan">
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0">
                <strong>
                    LAMPIRAN 4
                </strong>
            </p>
            <p>
                <strong>
                    LOKASI
                </strong>
            </p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0">
                <strong>
                    APPENDIX 4

                </strong>
            </p>
            <p>
                <strong>
                    LOCATION
                </strong>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-12 text-center">
            <table style="border: 1px solid black;">
                <tr style="background-color: grey;">
                    <th style="border: 2px solid black;">Lokasi Kerjasama</th>
                    <th style="border: 2px solid black;">Jumlah Kapasitas Pelanggan</th>
                    <th style="border: 2px solid black;">Periode Minimum Komitmen</th>
                    <th style="border: 2px solid black;">Minimum Komitmen Jumlah BAST IKR  per Bulan</th>
                </tr>
                <tr>
                    <td rowspan="4" style="border: 2px solid black;">{{ $agreement->getFields("lokasi_kerjasama") }}</td>
                    <td rowspan="4" style="border: 2px solid black;">{{ $agreement->getFields("jumlah_kapasitas_pelanggan") }}</td>
                    <td style="border: 2px solid black;">
                        Periode mulai Bulan ke {{ $agreement->getFields(" periode_pertama_bulan_dari") }} s/d {{ $agreement->getFields(" periode_pertama_bulan_sampai") }}
                    </td>
                    <td style="border: 2px solid black;">
                        {{ $agreement->getFields(" presentase_pertama_dari_pelanggan") }}% dari Jumlah Kapasitas Pelanggan
                    </td>
                </tr>
                <tr>
                    <td style="border: 2px solid black;">
                        Periode bulan ke {{ $agreement->getFields(" periode_kedua_bulan_dari") }} s/d {{ $agreement->getFields(" periode_kedua_bulan_sampai") }}
                    </td>
                    <td style="border: 2px solid black;">
                        {{ $agreement->getFields(" presentase_kedua_dari_pelanggan") }}% dari Jumlah Kapasitas Pelanggan
                    </td>
                </tr>
                <tr>
                    <td style="border: 2px solid black;">
                        Periode bulan ke {{ $agreement->getFields(" periode_ketiga_bulan_dari") }} s/d {{ $agreement->getFields(" periode_ketiga_bulan_sampai") }}
                    </td>
                    <td style="border: 2px solid black;">
                        {{ $agreement->getFields(" presentase_ketiga_dari_pelanggan") }}% dari Jumlah Kapasitas Pelanggan
                    </td>
                </tr>
                <tr>
                    <td style="border: 2px solid black;">
                        Periode Bulan ke {{ $agreement->getFields(" periode_keempat_bulan_dari") }} dan seterusnya hingga masa kontrak berakhir
                    </td>
                    <td style="border: 2px solid black;">
                        {{ $agreement->getFields(" presentase_keempat_dari_pelanggan") }}% dari jumlah kapasitas Pelanggan
                    </td>
                </tr>
            </table>

        </div>
    </div>
</div>