
<div class="card-body" id="printItem">
    <div class="row mb-4">
        <div class="col-6 pe-3 text-center">
            <h6><strong>PERJANJIAN KERJASAMA</strong> <strong>ANTARA {{ $agreement->getFields(" nama_perusahaan_pertama") }} DENGAN {{ $agreement->getFields(" nama_pihak_kedua_individu") }}</h6> 
            <h6>No: {{ $agreement->number_result }}</strong></h6>
        </div>
        <div class="col-6 ps-3 text-center">
            <h6><strong>CONSIGNMENT AGREEMENT</strong> <strong>BETWEEN {{ $agreement->getFields(" nama_perusahaan_pertama") }} WITH {{ $agreement->getFields(" nama_pihak_kedua_individu") }}</h6>
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
            Consignment Agreement (hereinafter referred to as the <strong>“Agreement”</strong>)
                is signed on
                {{ \Carbon\Carbon::parse($agreement->date_agreement)->format('d-m-Y') }}, by and between:
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    {{ $agreement->getFields(" nama_perusahaan_pertama") }}, suatu perseroan terbatas yang didirikan dan berdiri secara sah berdasarkan hukum
                    Indonesia yang berkedudukan di {{ $agreement->getFields(" alamat_perusahaan_pertama") }}, dalam hal ini diwakili oleh {{ $agreement->getFields(" nama_perwakilan_pertama") }} dalam kapasitasnya
                    sebagai {{ $agreement->getFields(" jabatan_perwakilan_pertama") }}, oleh karena itu sah bertindak untuk dan atas nama {{ $agreement->getFields(" entitas_di_wakili_pihak_pertama") }}, (untuk selanjutnya
                    disebut sebagai <strong>“Pihak Pertama”</strong>).
                </li>
                <li style="margin-bottom: 15px;">
                    <p>
                    {{ $agreement->getFields(" nama_pihak_kedua_individu") }}, pemegang Kartu Tanda Penduduk nomor {{ $agreement->getFields(" nomor_identitas_pihak_kedua_individu") }}, berdomisili di {{ $agreement->getFields(" alamat_domisili_pihak_kedua_individu") }}, oleh karena itu sah bertindak untuk dan atas nama diri sendiri, (untuk selanjutnya disebut sebagai  <strong>“Pihak kedua”</strong>).  
                    </p>
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    {{ $agreement->getFields(" nama_perusahaan_pertama") }}, a limited liability company legally established and standing under Indonesian law
                    domiciled in {{ $agreement->getFields(" alamat_perusahaan_pertama") }}, in this case represented by {{ $agreement->getFields(" nama_perwakilan_pertama") }} its capacity as {{ $agreement->getFields(" jabatan_perwakilan_pertama") }}, therefore acting
                    for and on behalf of {{ $agreement->getFields(" entitas_di_wakili_pihak_pertama") }}, (hereinafter referred to as the <strong>“First Party”</strong>).
                </li>
                <li style="margin-bottom: 15px;">
                    <p>
                    {{ $agreement->getFields(" nama_pihak_kedua_individu") }}, holder of Resident Identity Card number {{ $agreement->getFields(" nomor_identitas_pihak_kedua_individu") }}, domiciled at {{ $agreement->getFields(" alamat_domisili_pihak_kedua_individu") }}, therefore acting for and on behalf of itself, (hereinafter referred to as the <strong>“Second Party”</strong>)
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
                Bahwa Pihak Pertama adalah suatu perseroan terbatas yang bergerak di bidang jasa.
                </li>
                <li style="margin-bottom: 15px;">
                Bahwa Pihak Kedua adalah perorangan yang memiliki ahli sebagai {{ $agreement->getFields(" keahlian_id") }} untuk mendukung bisnis Pihak Pertama (<strong>“Pekerjaan”</strong>).
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                Whereas the First Party is a limited liability company engaged in the service sector.

                </li>
                <li style="margin-bottom: 15px;">
                Whereas the Second Party is an individual who has expertise as {{ $agreement->getFields(" keahlian_en") }} to support the First Party’s business <strong>("Work")</strong>.
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
                Para pihak sepakat bahwa Pihak Pertama akan menggunakan jasa Pihak Kedua, dengan rincian sebagai berikut:
                {!! $agreement->getFields(" detail_kesepakatan_jasa_id") !!}

                </li>
                <li style="margin-bottom: 15px;">
                Pihak Kedua wajib mengikuti ketentuan yang ada dalam Perjanjian ini termasuk dan tidak terbatas pada syarat dan ketentuan yang telah disepakati sebelumnya oleh Para Pihak.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                The parties agree that the First Party will use the Second Party’s service, with the following details:
                {!! $agreement->getFields(" detail_kesepakatan_jasa_en") !!}

                </li>
                <li style="margin-bottom: 15px;">
                The Second Party is obliged to follow the provisions contained in this Agreement including but not limited to the terms and conditions previously agreed upon by the Parties.
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
            Perjanjian ini berlaku untuk jangka waktu {{ $agreement->getFields("masa_berlaku_id") }} dimulai sejak tanggal {{ $agreement->getFields("tanggal_mulai_perjanjian") ? \Carbon\Carbon::parse($agreement->getFields("tanggal_mulai_perjanjian"))->format('d-m-Y') : '-' }} dan akan berakhir pada tanggal {{ $agreement->getFields("tanggal_berakhir_perjanjian") ? \Carbon\Carbon::parse($agreement->getFields("tanggal_berakhir_perjanjian"))->format('d-m-Y') : '-' }} <strong>(“Jangka Waktu”)</strong>.
            </p>
        </div>
        <div class="col-6 ps-3 text-justify">
            <p class="mb-0">
                This agreement is valid for a period of {{ $agreement->getFields("masa_berlaku_id") }} starting from the date  {{ $agreement->getFields("tanggal_mulai_perjanjian") ? \Carbon\Carbon::parse($agreement->getFields("tanggal_mulai_perjanjian"))->format('d-m-Y') : '-' }} and will end on the date {{ $agreement->getFields("tanggal_berakhir_perjanjian") ? \Carbon\Carbon::parse($agreement->getFields("tanggal_berakhir_perjanjian"))->format('d-m-Y') : '-' }} <strong>(“Time Period”)</strong>.
            </p>
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
                BIAYA DAN CARA PEMBAYARAN

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
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                Atas Pekerjaan yang dilaksanakan oleh Pihak Kedua berdasarkan Perjanjian ini, Pihak Pertama akan membayar biaya jasa <strong>(“Biaya Pekerjaan”)</strong>, dengan rincian sebagai berikut:
                </li>
                <ol type="a">
                    <li style="margin-bottom: 15px;">
                    Termin I akan dibayarkan sebesar {{ $agreement->getFields("pembayaran_termin_pertama") ? "Rp ".number_format($agreement->getFields("pembayaran_termin_pertama"), 0, ',', '.') : '-' }} setelah pekerjaan selesai dengan persentase {{ $agreement->getFields("pesentase_pembayaran_termin_pertama") }}%.
                    </li>
                    <li style="margin-bottom: 15px;">
                    Termin II akan dibayarkan sebesar {{ $agreement->getFields("pembayaran_termin_kedua") ? "Rp ".number_format($agreement->getFields("pembayaran_termin_kedua"), 0, ',', '.') : '-' }} setelah pekerjaan selesai dengan persentase {{ $agreement->getFields("pesentase_pembayaran_termin_kedua") }}%.
                    </li>
                </ol>
                <li>
                    Pembayaran oleh Pihak Pertama akan dilaksanakan dalam jangka waktu {{ $agreement->getFields(" jangka_waktu_dilaksanakan_pembayaran") }} hari sejak menerima tagihan, dengan cara transfer ke rekening Pihak Pertama dengan data sebagai berikut:
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
                <li>
                    Para Pihak akan bertanggung jawab untuk pembayaran pajak masing-masing dan/atau untuk persyaratan administratif yang berkaitan dengan pajak tersebut, serta membayar semua jenis pajak tepat waktu sesuai dengan ketentuan perpajakan yang berlaku.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    For Work implemented by the Second Party based on this Agreement, the First Party will pay the service fee <strong>(“Work Fee”)</strong>, with the following details:
                </li>
                <ol type="a">
                    <li style="margin-bottom: 15px;">
                    Term I will be paid of IDR {{ $agreement->getFields("pembayaran_termin_pertama") ? "Rp ".number_format($agreement->getFields("pembayaran_termin_pertama"), 0, ',', '.') : '-' }} after the work is completed with a percentage of {{ $agreement->getFields(" pesentase_pembayaran_termin_pertama") }}%.
                    </li>
                    <li style="margin-bottom: 15px;">
                    Term II will be paid of IDR {{ $agreement->getFields("pembayaran_termin_kedua") ? "Rp ".number_format($agreement->getFields("pembayaran_termin_kedua"), 0, ',', '.') : '-' }} after the work is completed with a percentage of {{ $agreement->getFields(" pesentase_pembayaran_termin_kedua") }}%.
                    </li>
                </ol>
                <li>
                The payment by the First Party will be made within {{ $agreement->getFields(" jangka_waktu_dilaksanakan_pembayaran") }} days from receiving the invoice, by transfer to the First Party's account with the following data:
                    <table style="border: 0">
                        <tr>
                            <td>
                            Holder Name
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
                <li>
                    The Parties will be responsible for each of the tax payments and/or for administrative requirements related to the tax, as well as paying all types of taxes on time in accordance with applicable tax provisions.
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
                PELANGGARAN DAN PENGAKHIRAN
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
                INFRINGEMENT AND TERMINATION
                </strong>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                Perjanjian ini tidak dapat diakhiri sebelum Jangka Waktu berakhir, kecuali hal tersebut telah disepakati sebelumnya, maka Pihak Kedua wajib membayar kerugian kepada Pihak Pertama dengan prorata dari sisa Jangka Waktu Perjanjian.
                </li>
                <li style="margin-bottom: 15px;">
                Apabila Pihak Kedua melakukan pelanggaran yang berkaitan pelaksanaan Perjanjian ini, maka Pihak Kedua akan diberikan peringatan sebanyak 3 (tiga) kali dengan jangka waktu masing-masing selama 7 (tujuh) hari kerja, dan jika masih terjadi pelanggaran tersebut maka Pihak Pertama berhak untuk mengakhiri Perjanjian dan Pihak Kedua wajib membayar kerugian yang dialami oleh Pihak Pertama.

                </li>
                <li>
                Apabila Perjanjian ini berakhir karena sebab sebagaimana diatur dalam Pasal 4.2 Perjanjian ini, maka segala hak dan kewajiban yang belum dipenuhi harus dipenuhi dan Perjanjian ini tetap berlaku hingga dipenuhinya hak atau kewajiban tersebut.

                </li>
                <li>
                Apabila Perjanjian berakhir dikarenakan Pekerjaan selesai, Pihak Kedua akan memberikan garansi selama 1 (satu) bulan dari pekerjaan yang telah dilaksanakan terhitung dari pekerjaan dinyatakan selesai oleh Pihak Pertama.

                </li>
                <li>
                Para Pihak setuju dan sepakat untuk mengesampingkan ketentuan Pasal 1266 KUHPerdata terhadap Perjanjian ini dalam hal diperlukan suatu putusan pengadilan untuk mengakhiri Perjanjian ini. 

                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                This Agreement cannot be terminated before the end of Time Period, unless this has been agreed in advance, in which case the Second Party is obliged to pay damages to the First Party with a pro rata portion of the remaining Time Period of the Agreement.
                </li>
                <li style="margin-bottom: 15px;">
                If the Second Party commits a violation related to the implementation of this Agreement, the Second Party will be given 3 (three) warnings, each with a period of 7 (seven) working days. If the infringement still occurs, the First Party has the right to terminate the Agreement, and the Second Party is obligated to pay the damages incurred by the First Party.

                </li>
                <li>
                If this Agreement is terminated for the reasons specified in Article 4.2 of this Agreement, then all rights and obligations that have not been fulfilled must still be fulfilled, and this Agreement will remain in effect until the fulfillment of these rights or obligations.

                </li>
                <li>
                When the Agreement expires because of the Work that has done, the Second Party will provide a guarantee for 1 (one) month from the work that has been carried out, calculated from the work being declared complete by the First Party.

                </li>
                <li>
                The Parties agree and consent to exclude the application of the provisions of Article 1266 of the Indonesian Civil Code to this Agreement, in the event that a court decision is required to terminate this Agreement.

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
                    KERAHASIAAN </strong>
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
                    CONFIDENTIALITY
                </strong>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li>
                Pihak Kedua wajib menjaga dan menyimpan segala informasi, keterangan, dan data lainnya yang diperoleh dalam rangka pelaksanaan Perjanjian ini sebagai rahasia yang tidak boleh diberitahukan kepada pihak-pihak manapun yang tidak berhak, baik dengan maksud atau tujuan apapun selama dan sesudah berlakunya Perjanjian ini, kecuali ada persetujuan tertulis terlebih dahulu dari pihak pemilik informasi rahasia atau dalam rangka memenuhi ketentuan perundang-undangan.
    
                </li>
                <li>
                Para Pihak dilarang, tanpa persetujuan tertulis dari Pihak lainnya, memberitahukan, atau memberikan informasi rahasia kepada pihak lain dan/atau pihak ketiga.
    
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li>
                The Second Party is required to maintain and store all information, statements, and other data obtained in the implementation of this Agreement as confidential and may not be disclosed to any unauthorized parties, either with any intent or purpose during and after the effective date of this Agreement, unless there is prior written consent from the party that owns the confidential information or in order to comply with statutory provisions.
    
                </li>
                <li>
                The Second Party is prohibited, without the written consent of the other Party, disclosing, providing confidential information to other parties and/or third parties.
    
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
                    FORCE MAJEURE
                </strong>
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
                    PASAL 7
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
                    ARTICLE 7
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
                            <td width="75%">{{ $agreement->getFields("nama_pihak_kedua_individu ") }}</td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" alamat_domisili_pihak_kedua_individu")  }}</td>
                        </tr>
                        <tr>
                            <td>Telephone</td>
                            <td>:</td>
                            <td>{{  $agreement->getFields(" telephone_perwakilan_pihak_kedua_individu")  }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>{{ $agreement->getFields(" email_perwakilan_pihak_kedua_individu")  }}</td>
                        </tr>
                        <tr>
                            <td>Up</td>
                            <td>:</td>
                            <td>{{  $agreement->getFields(" up_perwakilan_pihak_kedua_individu")  }}</td>
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
        <div class="col-6 pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 8
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
                    ARTICLE 8
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
                Ketidakberlakuan pasal dan ketentuan tersebut sebagaimana dimaksud pada Pasal 8.1 ini, tidak akan mempengaruhi berlakunya atau dapat dilaksanakannya setiap ketentuan lainnya dari Perjanjian ini dan Para Pihak akan segera melakukan negosiasi untuk ketentuan pengganti, jika diperlukan, yang akan dituangkan dalam Adendum yang menjadi bagian tak terpisahkan dari Perjanjian ini.

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
                    The invalidity of the articles and provisions as referred to in Article 8.1, will not affect the validity or enforceability of any other provisions of this Agreement and the Parties will immediately negotiate for replacement provisions, if necessary, which will be stated in an Addendum which is an integral part of this Agreement.
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
                    PASAL 9
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
                    ARTICLE 9
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

    <div class="row" style="min-height: 80px; ">

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