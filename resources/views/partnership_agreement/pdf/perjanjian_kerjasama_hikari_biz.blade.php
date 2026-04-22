<div class="card-body" id="printItem">
    <div class="row mb-4">
        <div class="col-6 pe-3 text-center">
            <h6><strong>PERJANJIAN KERJASAMA</strong> <strong>ANTARA
                    {{ $agreement->getFields("nama_perusahaan_pertama") }} DENGAN
                    {{ $agreement->getFields("nama_perusahaan_pihak_kedua") }}</h6>
            <h6>No: {{ $agreement->number_result }}</strong></h6>
        </div>
        <div class="col-6 ps-3 text-center">
            <h6><strong>COOPERATION AGREEMENT</strong> <strong>BETWEEN
                    {{ $agreement->getFields("nama_perusahaan_pertama") }} WITH
                    {{ $agreement->getFields("nama_perusahaan_pihak_kedua") }}</h6>
            <h6>No: {{ $agreement->number_result }}</strong></h6>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p>
                Perjanjian Kerjasama (selanjutnya disebut <strong>"Perjanjian"</strong>) ini
                ditandatangani pada tanggal {{ \Carbon\Carbon::parse($agreement->date_agreement)->format('d-m-Y') }}, oleh dan antara:
            </p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p>
                The Cooperation Agreement (hereinafter referred to as the <strong>"Agreement"</strong>)
                is signed on {{ \Carbon\Carbon::parse($agreement->date_agreement)->format('d-m-Y') }}, by and between:
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    {{ $agreement->getFields("nama_perusahaan_pertama") }}, suatu perseroan terbatas yang didirikan dan
                    berdiri secara sah berdasarkan hukum Indonesia yang berkedudukan di
                    {{ $agreement->getFields("alamat_perusahaan_pertama") }}, dalam hal ini diwakili oleh
                    {{ $agreement->getFields("nama_perwakilan_pertama") }} dalam kapasitasnya sebagai
                    {{ $agreement->getFields("jabatan_perwakilan_pertama") }}, oleh karena itu sah bertindak untuk dan
                    atas nama {{ $agreement->getFields("entitas_di_wakili_pihak_pertama") }}, (untuk selanjutnya
                    disebut sebagai <strong>"Pihak Pertama"</strong>);
                </li>
                <li style="margin-bottom: 15px;">
                    <p>
                        {{ $agreement->getFields("nama_perusahaan_pihak_kedua") }}, suatu perseroan terbatas yang
                        didirikan dan berdiri secara sah berdasarkan hukum Indonesia yang beralamat di
                        {{ $agreement->getFields("alamat_perusahaan_pihak_kedua") }}, dalam hal ini diwakili oleh
                        {{ $agreement->getFields("nama_perwakilan_pihak_kedua") }} sebagai
                        {{ $agreement->getFields("jabatan_perwakilan_pihak_kedua") }}, secara sah bertindak untuk dan
                        atas nama {{ $agreement->getFields("entitas_di_wakili") }}, (untuk selanjutnya disebut sebagai
                        "Pihak kedua").
                    </p>
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    {{ $agreement->getFields("nama_perusahaan_pertama") }}, a limited liability company legally
                    established and standing under Indonesian law domiciled in
                    {{ $agreement->getFields("alamat_perusahaan_pertama") }}, in this case represented by
                    {{ $agreement->getFields("nama_perwakilan_pertama") }} its capacity as
                    {{ $agreement->getFields("jabatan_perwakilan_pertama") }}, therefore acting for and on behalf of
                    {{ $agreement->getFields("entitas_di_wakili_pihak_pertama") }}, (hereinafter referred to as the
                    <strong>"First Party"</strong>);
                </li>
                <li style="margin-bottom: 15px;">
                    <p>
                        {{ $agreement->getFields("nama_perusahaan_pihak_kedua") }}, a limited liability company legally
                        established and standing under Indonesian law domiciled in
                        {{ $agreement->getFields("alamat_perusahaan_pihak_kedua") }}, in this case represented by
                        {{ $agreement->getFields("nama_perwakilan_pihak_kedua") }} its capacity as
                        {{ $agreement->getFields("jabatan_perwakilan_pihak_kedua") }}, therefore acting for and on
                        behalf of {{ $agreement->getFields("entitas_di_wakili") }}, (hereinafter referred to as the
                        <strong>"Second Party"</strong>)
                    </p>
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p>
                Pihak Pertama dan Pihak Kedua selanjutnya secara bersama-sama di dalam Perjanjian ini akan
                disebut sebagai <strong>"Para Pihak"</strong>, sedangkan masing-masing disebut
                <strong>"Pihak"</strong>.
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
                    Bahwa Pihak Pertama adalah suatu perseroan yang bergerak di bidang penyelenggara jaringan dan jasa telekomunikasi.
                </li>
                <li style="margin-bottom: 15px;">
                    Bahwa Pihak kedua adalah suatu perseroan yang bergerak di bidang jasa telekomunikasi.
                </li>
                <li style="margin-bottom: 15px;">
                    Bahwa Pihak Pertama bermaksud untuk bekerja sama dalam bentuk penyediaan layanan
                    {{ $agreement->getFields("nama_layanan") }}, dengan ini menyetujuinya, Para Pihak sepakat
                    untuk mengikatkan diri dalam sebuah Perjanjian dengan syarat-syarat dan
                    ketentuan-ketentuan kerjasama dalam Perjanjian ini.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Whereas the First Party is a company engaged in the field of network and telecommunications services.
                </li>
                <li style="margin-bottom: 15px;">
                    Whereas the Second Party is a company engaged in telecommunications services.
                </li>
                <li style="margin-bottom: 15px;">
                    Whereas the First Party intends to engage in a cooperation in the form of providing
                    {{ $agreement->getFields("nama_layanan") }} services, and the Second Party agrees thereto,
                    the Parties hereby agree to bind themselves in this Agreement under the terms and conditions
                    set forth herein.
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

    {{-- PASAL 1 --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>PASAL 1</strong></p>
            <p><strong>RUANG LINGKUP PERJANJIAN</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>ARTICLE 1</strong></p>
            <p><strong>SCOPE OF AGREEMENT</strong></p>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Para Pihak sepakat bahwa Pihak Pertama menyediakan layanan {{ $agreement->getFields("nama_layanan") }}.
                    dengan Spesifikasi layanan : Bandwidth {{ $agreement->getFields("bandwidth_mbps") }} Mbps,
                    Lokasi Instalasi : {{ $agreement->getFields("lokasi_instalasi") }} yang dimana layanan diberikan
                    untuk digunakan oleh Pihak Kedua sesuai dengan kebutuhan {{ $agreement->getFields("kebutuhan_pihak_kedua") }}.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    The Parties agree that the First Party shall provide {{ $agreement->getFields("nama_layanan") }}.
                    The service specifications are as follows: Bandwidth of {{ $agreement->getFields("bandwidth_mbps") }} Mbps,
                    Installation Location: {{ $agreement->getFields("lokasi_instalasi") }}, whereby the services are to
                    be utilized by the Second Party in accordance with its {{ $agreement->getFields("kebutuhan_pihak_kedua") }}.
                </li>
            </ol>
        </div>
    </div>

    {{-- PASAL 2 --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>PASAL 2</strong></p>
            <p><strong>JANGKA WAKTU</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>ARTICLE 2</strong></p>
            <p><strong>TIME PERIOD</strong></p>
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
                memperoleh masing-masing haknya. Dan Perjanjian dapat diperpanjang berdasarkan kesepakatan para pihak.
            </p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p class="mb-0">
                This agreement is valid for {{ $agreement->getFields("masa_berlaku_en") }} years effective from
                {{ $agreement->getFields("tanggal_mulai_perjanjian") ? \Carbon\Carbon::parse($agreement->getFields("tanggal_mulai_perjanjian"))->format('d-m-Y') : '-' }}
                until
                {{ $agreement->getFields("tanggal_berakhir_perjanjian") ? \Carbon\Carbon::parse($agreement->getFields("tanggal_berakhir_perjanjian"))->format('d-m-Y') : '-' }}
                and/or the parties have fulfilled their respective obligations and each party has received their
                respective rights. This Agreement may be extended upon mutual agreement of the Parties.
            </p>
        </div>
    </div>

    {{-- PASAL 3 --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>PASAL 3</strong></p>
            <p><strong>HAK DAN KEWAJIBAN PARA PIHAK</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>ARTICLE 3</strong></p>
            <p><strong>RIGHTS AND OBLIGATIONS OF THE PARTIES</strong></p>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    <p>Hak dan Kewajiban Pihak Pertama, sebagai berikut:</p>
                    <ol type="a">
                        <li style="margin-bottom: 15px;">
                            Pihak Pertama wajib menyediakan layanan {{ $agreement->getFields("nama_layanan") }} sesuai SLA berlaku.
                        </li>
                        <li style="margin-bottom: 15px;">
                            Pihak Pertama wajib memberikan upaya terbaiknya dalam melakukan dan menyelesaikan proses {{ $agreement->getFields("nama_layanan") }}.
                        </li>
                        <li style="margin-bottom: 15px;">
                            Pihak Pertama wajib memberikan dan menjamin kualitas layanan sesuai dengan spesifikasi dan memberikan support teknis saat dibutuhkan.
                        </li>
                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                    <p>Hak dan Kewajiban Pihak Kedua, sebagai berikut:</p>
                    <ol type="a">
                        <li style="margin-bottom: 15px;">
                            Pihak Kedua berhak menggunakan layanan {{ $agreement->getFields("nama_layanan") }}.
                        </li>
                        <li style="margin-bottom: 15px;">
                            Pihak Kedua wajib melakukan pembayaran sesuai ketentuan yang disepakati.
                        </li>
                        <li style="margin-bottom: 15px;">
                            Pihak Kedua wajib menjaga penggunaan layanan tidak melanggar hukum yang berlaku.
                        </li>
                        <li style="margin-bottom: 15px;">
                            Pihak Kedua berhak mendapatkan support teknis sesuai dengan SLA yang diberikan.
                        </li>
                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                    Para Pihak bertanggung jawab memenuhi hak dan kewajiban yang tertera.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    <p>The Rights and Obligations of the First Party, as follows:</p>
                    <ol type="a">
                        <li style="margin-bottom: 15px;">
                            The First Party shall be obligated to provide the {{ $agreement->getFields("nama_layanan") }} services in accordance with the applicable Service Level Agreement (SLA).
                        </li>
                        <li style="margin-bottom: 15px;">
                            The First Party shall use its best efforts to carry out and complete the {{ $agreement->getFields("nama_layanan") }} process.
                        </li>
                        <li style="margin-bottom: 15px;">
                            The First Party shall provide and ensure that the quality of the services meets the agreed specifications and shall deliver technical support as required.
                        </li>
                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                    <p>The Rights and Obligations of the Second Party, as follows:</p>
                    <ol type="a">
                        <li style="margin-bottom: 15px;">
                            The Second Party shall have the right to use the {{ $agreement->getFields("nama_layanan") }} services.
                        </li>
                        <li style="margin-bottom: 15px;">
                            The Second Party shall be obligated to make payments in accordance with the agreed terms.
                        </li>
                        <li style="margin-bottom: 15px;">
                            The Second Party shall ensure that the use of the services does not violate any applicable laws and regulations.
                        </li>
                        <li style="margin-bottom: 15px;">
                            The Second Party shall be entitled to receive technical support in accordance with the applicable Service Level Agreement (SLA).
                        </li>
                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                    The Parties shall be responsible for fulfilling the rights and obligations as set forth herein.
                </li>
            </ol>
        </div>
    </div>

    {{-- PASAL 4 --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>PASAL 4</strong></p>
            <p><strong>BIAYA DAN TATA CARA PEMBAYARAN</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>ARTICLE 4</strong></p>
            <p><strong>FEE AND PAYMENT METHOD</strong></p>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Biaya layanan terdiri dari {{ $agreement->getFields("biaya_layanan_komponen") }}.
                </li>
                <li style="margin-bottom: 15px;">
                    Para Pihak sepakat skema pembayaran bulanan, yang dimana pembayaran dilakukan maksimal
                    {{ $agreement->getFields("hari_pembayaran_invoice") }} hari sejak invoice penagihan diterima.
                </li>
                <li style="margin-bottom: 15px;">
                    Detail biaya yang perlu dibayarkan tercantum pada Lampiran 1.
                </li>
                <li style="margin-bottom: 15px;">
                    Keterlambatan pembayaran dapat dikenakan denda sebesar
                    {{ $agreement->getFields("denda_keterlambatan_persen") }}% Per bulan.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    The service fees shall consist of {{ $agreement->getFields("biaya_layanan_komponen") }}.
                </li>
                <li style="margin-bottom: 15px;">
                    The Parties agree to a monthly payment scheme, whereby payment shall be made no later than
                    {{ $agreement->getFields("hari_pembayaran_invoice") }} days from the date the invoice is received.
                </li>
                <li style="margin-bottom: 15px;">
                    The details of the fees payable are set forth in Appendix 1.
                </li>
                <li style="margin-bottom: 15px;">
                    Any delay in payment may be subject to a penalty of
                    {{ $agreement->getFields("denda_keterlambatan_persen") }}% per month.
                </li>
            </ol>
        </div>
    </div>

    {{-- PASAL 5 --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>PASAL 5</strong></p>
            <p><strong>SLA DAN LAYANAN</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>ARTICLE 5</strong></p>
            <p><strong>SERVICE LEVEL AGREEMENT (SLA) AND SERVICES</strong></p>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    Pihak Pertama menjamin uptime layanan minimal {{ $agreement->getFields("uptime_persen") }}%.
                </li>
                <li style="margin-bottom: 15px;">
                    Gangguan akan ditangani dalam waktu Respon Awal {{ $agreement->getFields("respon_awal_jam") }} Jam,
                    dengan Penyelesaian maksimal {{ $agreement->getFields("penyelesaian_jam") }} Jam.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    The First Party guarantees a minimum service uptime of {{ $agreement->getFields("uptime_persen") }}%.
                </li>
                <li style="margin-bottom: 15px;">
                    Any service disruption shall be addressed with an Initial Response Time of
                    {{ $agreement->getFields("respon_awal_jam") }} hours and a maximum Resolution Time of
                    {{ $agreement->getFields("penyelesaian_jam") }} hours.
                </li>
            </ol>
        </div>
    </div>

    {{-- PASAL 6 --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>PASAL 6</strong></p>
            <p><strong>KERAHASIAAN</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>ARTICLE 6</strong></p>
            <p><strong>CONFIDENTIALITY</strong></p>
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

    {{-- PASAL 7 --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>PASAL 7</strong></p>
            <p><strong>PERNYATAAN DAN JAMINAN</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>ARTICLE 7</strong></p>
            <p><strong>REPRESENTATIONS AND WARRANTIES</strong></p>
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
                    Pihak Pertama menjamin bahwa apabila timbul gugatan dan/atau tuntutan akibat dari segala tindakan
                    dan/atau pelanggaran yang dilakukan oleh Pihak Pertama, maka Pihak Pertama membebaskan Pihak Kedua
                    dari segala bentuk tanggung jawab, dampak, dan ganti rugi akibat gugatan dan/atau tuntutan tersebut.
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
                    The First Party warrants that, in the event of any lawsuit and/or claim arising from any act and/or
                    violation committed by the First Party, the First Party shall hold the Second Party harmless from any
                    form of liability, consequences, and compensation resulting from such lawsuit and/or claim.
                </li>
            </ol>
        </div>
    </div>

    {{-- PASAL 8 --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>PASAL 8</strong></p>
            <p><strong>PENGAKHIRAN PERJANJIAN</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>ARTICLE 8</strong></p>
            <p><strong>TERMINATION OF AGREEMENT</strong></p>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    <p class="mb-0">Pihak Pertama dapat memutuskan Perjanjian ini tanpa perlu pemberitahuan kepada Pihak Kedua, apabila:</p>
                    <ol type="a">
                        <li style="margin-bottom: 15px;">
                            Pihak Kedua melanggar syarat-syarat dan/atau ketentuan-ketentuan dalam Perjanjian ini; dan
                        </li>
                        <li style="margin-bottom: 15px;">
                            Terjadi pelanggaran hukum yang dilakukan oleh Pihak Kedua yang mengakibatkan Pihak Kedua
                            tidak dapat melaksanakan kewajibannya berdasarkan Perjanjian ini.
                        </li>
                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                    Apabila Pihak Kedua memutuskan mengakhiri Perjanjian ini lebih awal, maka Pihak Pertama wajib
                    memberitahukan secara tertulis paling lambat 60 (enam puluh) hari sebelum tanggal efektif
                    pengakhiran dan Pihak Kedua wajib membayar sisa tagihan layanan ditambah denda sebesar
                    {{ $agreement->getFields("denda_pengakhiran_persen") }}%
                    ({{ $agreement->getFields("denda_pengakhiran_terbilang") }}).
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
                    <p class="mb-0">The First party may terminate this Agreement without any notice to the Second Party, if:</p>
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
                    In the event that the Second Party decides to terminate this Agreement prior to its expiry,
                    the Second Party shall provide written notice to the First Party no later than sixty (60) days
                    prior to the effective termination date, and shall be obligated to settle any remaining service
                    fees along with a penalty of {{ $agreement->getFields("denda_pengakhiran_persen") }}%
                    ({{ $agreement->getFields("denda_pengakhiran_terbilang") }}).
                </li>
                <li style="margin-bottom: 15px;">
                    The Parties agree and consent to exclude the application of the provisions of Article 1266
                    of the Indonesian Civil Code to this Agreement, in the event that a court decision is
                    required to terminate this Agreement.
                </li>
            </ol>
        </div>
    </div>

    {{-- PASAL 9 --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>PASAL 9</strong></p>
            <p><strong>FORCE MAJEURE</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>ARTICLE 9</strong></p>
            <p><strong>FORCE MAJEURE</strong></p>
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

    {{-- PASAL 10 --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>PASAL 10</strong></p>
            <p><strong>KORESPONDENSI</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>ARTICLE 10</strong></p>
            <p><strong>CORRESPONDENCE</strong></p>
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
                            <td width="75%">{{ $agreement->getFields("nama_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("alamat_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Telephone</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("telephone_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("email_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Up</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("up_perwakilan_pertama") }}</td>
                        </tr>
                    </table>
                </li>
                <li style="margin-bottom: 15px;">
                    <table style="border: 0; width: 100%">
                        <tr>
                            <td width="20%">Nama</td>
                            <td width="5%">:</td>
                            <td width="75%">
                                {{ $agreement->getFields("nama_perwakilan_pihak_kedua") ?? $agreement->getFields("nama_pihak_kedua_individu") }}
                            </td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("alamat_perwakilan_pihak_kedua") ?? $agreement->getFields("alamat_domisili_pihak_kedua_individu") }}</td>
                        </tr>
                        <tr>
                            <td>Telephone</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("telephone_perwakilan_pihak_kedua") ?? $agreement->getFields("telephone_perwakilan_pihak_kedua_individu") }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("email_perwakilan_pihak_kedua") ?? $agreement->getFields("email_perwakilan_pihak_kedua_individu") }}</td>
                        </tr>
                        <tr>
                            <td>Up</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("up_perwakilan_pihak_kedua") ?? $agreement->getFields("up_perwakilan_pihak_kedua_individu") }}</td>
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
                            <td width="75%">{{ $agreement->getFields("nama_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Address</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("alamat_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Phone</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("telephone_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("email_perwakilan_pertama") }}</td>
                        </tr>
                        <tr>
                            <td>Up</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("up_perwakilan_pertama") }}</td>
                        </tr>
                    </table>
                </li>
                <li style="margin-bottom: 15px;">
                    <table style="border: 0; width: 100%">
                        <tr>
                            <td width="20%">Name</td>
                            <td width="5%">:</td>
                            <td width="75%">
                                {{ $agreement->getFields("nama_perwakilan_pihak_kedua") ?? $agreement->getFields("nama_pihak_kedua_individu") }}
                            </td>
                        </tr>
                        <tr>
                            <td>Address</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("alamat_perwakilan_pihak_kedua") ?? $agreement->getFields("alamat_domisili_pihak_kedua_individu") }}</td>
                        </tr>
                        <tr>
                            <td>Phone</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("telephone_perwakilan_pihak_kedua") ?? $agreement->getFields("telephone_perwakilan_pihak_kedua_individu") }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("email_perwakilan_pihak_kedua") ?? $agreement->getFields("email_perwakilan_pihak_kedua_individu") }}</td>
                        </tr>
                        <tr>
                            <td>Up</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields("up_perwakilan_pihak_kedua") ?? $agreement->getFields("up_perwakilan_pihak_kedua_individu") }}</td>
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

    {{-- PASAL 11 --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>PASAL 11</strong></p>
            <p><strong>KETERPISAHAN</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>ARTICLE 11</strong></p>
            <p><strong>SEVERABILITY</strong></p>
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

    {{-- PASAL 12 --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>PASAL 12</strong></p>
            <p><strong>HUKUM YANG BERLAKU DAN PENYELESAIAN PERSELISIHAN</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>ARTICLE 12</strong></p>
            <p><strong>GOVERNING LAW AND DISPUTE RESOLUTION</strong></p>
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

    {{-- PASAL 13 --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>PASAL 13</strong></p>
            <p><strong>LAMPIRAN-LAMPIRAN</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>ARTICLE 13</strong></p>
            <p><strong>APPENDIX</strong></p>
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
                    <p>Lampiran-lampiran dalam Perjanjian ini, sebagai berikut:</p>
                    <ol type="a">
                        <li style="margin-bottom: 15px;">Lampiran 1 : Surat Penawaran</li>
                        <li style="margin-bottom: 15px;">Lampiran 2 : Formulir BAST</li>
                        <li style="margin-bottom: 15px;">Lampiran 3 : Lokasi</li>
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
                    <p>The appendix to this Agreement is as follows:</p>
                    <ol type="a">
                        <li style="margin-bottom: 15px;">Appendix 1: Quotation Letter</li>
                        <li style="margin-bottom: 15px;">Appendix 2: BAST Form</li>
                        <li style="margin-bottom: 15px;">Appendix 3: Location</li>
                    </ol>
                </li>
            </ol>
        </div>
    </div>

    {{-- PASAL 14 --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>PASAL 14</strong></p>
            <p><strong>LAIN LAIN</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>ARTICLE 14</strong></p>
            <p><strong>OTHERS</strong></p>
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

    {{-- TANDA TANGAN --}}
    <div class="row mt-5 mb-5">
        <div class="col-5 text-justify">
            <p class="noMargin"><strong>PIHAK PERTAMA/FIRST PARTY</strong></p>
            <p class="noMargin"><strong>{{ $agreement->getFields("nama_perusahaan_pertama") }}</strong></p>
        </div>
        <div class="offset-2 col-5 text-justify">
            <p class="noMargin"><strong>PIHAK KEDUA/SECOND PARTY</strong></p>
            <p class="noMargin"><strong>{{ $agreement->getFields("nama_perusahaan_pihak_kedua") }}</strong></p>
        </div>
    </div>

    <div class="row mt-5 mb-5">
        <div class="col-5 text-justify">
            @if($agreement->getSignature(1))
                <img src="{{ s3_asset(true,10,'public/'.$agreement->getSignature(1)->signature) }}" class="img-thumbnail img-signature">
            @else
                <div style="min-height: 80px;"></div>
            @endif
        </div>
        <div class="offset-2 col-5 text-justify">
            @if($agreement->getSignature(2))
                <img src="{{ s3_asset(true,10,'public/'.$agreement->getSignature(2)->signature) }}" class="img-thumbnail img-signature">
            @else
                <div style="min-height: 80px;"></div>
            @endif
        </div>
    </div>

    <div class="row mt-5 mb-5">
        <div class="col-5 text-justify">
            <p class="noMargin"><strong>{{ $agreement->getFields("nama_perwakilan_pertama") }}</strong></p>
            <p class="noMargin"><strong>{{ $agreement->getFields("jabatan_perwakilan_pertama") }}</strong></p>
        </div>
        <div class="offset-2 col-5 text-justify">
            <p class="noMargin">
                <strong>{{ $agreement->getFields("nama_perwakilan_pihak_kedua") ?? $agreement->getFields("nama_pihak_kedua_individu") }}</strong>
            </p>
        </div>
    </div>

    <div style="page-break-after: always;"></div>

    {{-- LAMPIRAN 1 - BIAYA LAYANAN --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>Lampiran 1</strong></p>
            <p><strong>BIAYA LAYANAN</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>LAMPIRAN 1</strong></p>
            <p><strong>SERVICE FEES</strong></p>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol type="A">
                <li style="margin-bottom: 15px;">
                    <p class="mb-0"><strong>Biaya</strong></p>
                    <ol>
                        <li style="margin-bottom: 15px;">
                            Bandwidth : Rp {{ $agreement->getFields("biaya_bandwidth_bulanan") ? number_format($agreement->getFields("biaya_bandwidth_bulanan"), 0, ',', '.') : '-' }} / Bulan
                        </li>
                        <li style="margin-bottom: 15px;">
                            Registrasi &amp; SetUp : Rp {{ $agreement->getFields("biaya_registrasi_setup") ? number_format($agreement->getFields("biaya_registrasi_setup"), 0, ',', '.') : '-' }} (Satu Kali Bayar)
                        </li>
                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                    <p class="mb-0"><strong>Ketentuan</strong></p>
                    <ol>
                        <li style="margin-bottom: 15px;">Harga belum termasuk PPN.</li>
                        <li style="margin-bottom: 15px;">
                            Pembayaran Net {{ $agreement->getFields("payment_terms_hari") }} Hari.
                        </li>
                        <li style="margin-bottom: 15px;">Registrasi dan Set up sesuai hasil survey.</li>
                        <li style="margin-bottom: 15px;">
                            <p>Pembayaran akan ditransfer dari rekening bersama oleh Pihak Pertama berdasarkan rekening sebagai berikut:</p>
                            <strong>PIHAK PERTAMA</strong>
                            <table style="border: 0">
                                <tr>
                                    <td>No. Rekening</td>
                                    <td>: {{ $agreement->getFields("nomor_rekening") }}</td>
                                </tr>
                                <tr>
                                    <td>Bank</td>
                                    <td>: {{ $agreement->getFields("nama_bank") }}</td>
                                </tr>
                                <tr>
                                    <td>Cabang</td>
                                    <td>: {{ $agreement->getFields("kantor_cabang_bank") }}</td>
                                </tr>
                                <tr>
                                    <td>Atas nama</td>
                                    <td>: {{ $agreement->getFields("nama_pemilik_rekening") }}</td>
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
                    <p class="mb-0"><strong>Fees</strong></p>
                    <ol>
                        <li style="margin-bottom: 15px;">
                            Bandwidth : IDR {{ $agreement->getFields("biaya_bandwidth_bulanan") ? number_format($agreement->getFields("biaya_bandwidth_bulanan"), 0, ',', '.') : '-' }} / Month
                        </li>
                        <li style="margin-bottom: 15px;">
                            Registration &amp; Set Up : IDR {{ $agreement->getFields("biaya_registrasi_setup") ? number_format($agreement->getFields("biaya_registrasi_setup"), 0, ',', '.') : '-' }} (One Time Payment)
                        </li>
                    </ol>
                </li>
                <li style="margin-bottom: 15px;">
                    <p class="mb-0"><strong>Terms and Conditions</strong></p>
                    <ol>
                        <li style="margin-bottom: 15px;">Prices are exclusive of VAT.</li>
                        <li style="margin-bottom: 15px;">
                            Payment terms : Net {{ $agreement->getFields("payment_terms_hari") }} Days.
                        </li>
                        <li style="margin-bottom: 15px;">Registration and set up are subject to site survey results.</li>
                        <li style="margin-bottom: 15px;">
                            <p>The payment will be transferred from the joint account by Party One based on the following account details:</p>
                            <strong>THE FIRST PARTY</strong>
                            <table style="border: 0">
                                <tr>
                                    <td>Account no.</td>
                                    <td>: {{ $agreement->getFields("nomor_rekening") }}</td>
                                </tr>
                                <tr>
                                    <td>Bank</td>
                                    <td>: {{ $agreement->getFields("nama_bank") }}</td>
                                </tr>
                                <tr>
                                    <td>Branch</td>
                                    <td>: {{ $agreement->getFields("kantor_cabang_bank") }}</td>
                                </tr>
                                <tr>
                                    <td>Holder Name</td>
                                    <td>: {{ $agreement->getFields("nama_pemilik_rekening") }}</td>
                                </tr>
                            </table>
                        </li>
                    </ol>
                </li>
            </ol>
        </div>
    </div>

    <div style="page-break-after: always;"></div>

    {{-- LAMPIRAN 1 - SURAT PENAWARAN --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>LAMPIRAN 1</strong></p>
            <p><strong>SURAT PENAWARAN</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>LAMPIRAN 1</strong></p>
            <p><strong>QUOTATION LETTER</strong></p>
        </div>
    </div>
    @if($agreement->getFields('lampiran_1_text'))
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            {!! $agreement->getFields('lampiran_1_text') !!}
        </div>
        <div class="col-6 ps-3 text-justify">
            {!! $agreement->getFields('lampiran_1_text_en') !!}
        </div>
    </div>
    @endif
    @if($agreement->getFields('lampiran_1_image'))
    <div class="row">
        <div class="col-12 text-center mt-2">
            <img src="{{ s3_asset(true,10,$agreement->getFields('lampiran_1_image')) }}" class="img-fluid" style="max-width:50%;" alt="Lampiran 1">
        </div>
    </div>
    @endif

    <div style="page-break-after: always;"></div>

    {{-- LAMPIRAN 2 - FORMULIR BAST --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>LAMPIRAN 2</strong></p>
            <p><strong>FORMULIR BAST</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>LAMPIRAN 2</strong></p>
            <p><strong>BAST FORM</strong></p>
        </div>
    </div>
    @if($agreement->getFields('lampiran_2_text'))
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            {!! $agreement->getFields('lampiran_2_text') !!}
        </div>
        <div class="col-6 ps-3 text-justify">
            {!! $agreement->getFields('lampiran_2_text_en') !!}
        </div>
    </div>
    @endif
    @if($agreement->getFields('lampiran_2_image'))
    <div class="row">
        <div class="col-12 text-center mt-2">
            <img src="{{ s3_asset(true,10,$agreement->getFields('lampiran_2_image')) }}" class="img-fluid" style="max-width:50%;" alt="Lampiran 2">
        </div>
    </div>
    @endif

    <div style="page-break-after: always;"></div>

    {{-- LAMPIRAN 3 - LOKASI --}}
    <div class="row">
        <div class="col-6 pe-3 text-center">
            <p class="mb-0"><strong>LAMPIRAN 3</strong></p>
            <p><strong>LOKASI</strong></p>
        </div>
        <div class="col-6 ps-3 text-center">
            <p class="mb-0"><strong>LAMPIRAN 3</strong></p>
            <p><strong>LOCATION</strong></p>
        </div>
    </div>
    @if($agreement->getFields('lampiran_3_text'))
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            {!! $agreement->getFields('lampiran_3_text') !!}
        </div>
        <div class="col-6 ps-3 text-justify">
            {!! $agreement->getFields('lampiran_3_text_en') !!}
        </div>
    </div>
    @endif
    @if($agreement->getFields('lampiran_3_image'))
    <div class="row">
        <div class="col-12 text-center mt-2">
            <img src="{{ s3_asset(true,10,$agreement->getFields('lampiran_3_image')) }}" class="img-fluid" style="max-width:50%;" alt="Lampiran 3">
        </div>
    </div>
    @endif
</div>
