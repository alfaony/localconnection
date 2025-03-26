<div class="card-body p-4" id="printItem">
    <div class="row">
        <div class="col-12">
            <div class="text-center ">
                <p class="text-center "><strong>
                        FORMULIR PELANGGAN/CUSTOMER FORM
                    </strong></p>
            </div>

            <div class="pl-5 mt-0">
                <p class="mt-3">Identitas pelanggan/customer identity</p>
                <div class="row">
                    <div class="col-4">
                        <p class=" ">Nama/name</p>
                    </div>
                    <div class="col-6">
                        <p class=" ">: {{ $agreement->getFields("nama ") }} </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <p class=" ">KTP/Identity number</p>
                    </div>
                    <div class="col-6">
                        <p class=" ">: {{ $agreement->getFields("ktp ") }} </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <p class=" ">Alamat/address</p>
                    </div>
                    <div class="col-6">
                        <p class=" ">: {{ $agreement->getFields("alamat ") }} </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <p class=" ">Jangka waktu/time period</p>
                    </div>
                    <div class="col-6">
                        <p class=" ">: {{ $agreement->getFields("jangka_waktu ") }}</p>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <tr>
                        <th>
                            No.
                        </th>
                        <th>
                            Package Name
                        </th>
                        <th>
                            Package Details
                        </th>
                    </tr>
                    <tr>
                        <td>
                            1
                        </td>
                        <td>
                            {{ $agreement->getFields("nama_paket ") }}
                        </td>
                        <td>
                            {{ $agreement->getFields("detail_paket ") }}
                        </td>
                    </tr>
                </table>
            </div>
            <div class="row mb-2">
                <div class="col-4">
                    <p class=" ">
                        <strong>Alamat pemasangan/installation address:</strong>
                    </p>
                </div>
                <div class="col-6">
                    <p class=" ">: {{ $agreement->getFields("alamat_pemasangan ") }}</p>
                </div>
            </div>

            <table style="border: 0">
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
                        Holder Name
                    </td>
                    <td>
                        : {{ $agreement->getFields(" holder_name") }}
                    </td>
                </tr>
                <tr>
                    <td>
                        Account Number
                    </td>
                    <td>
                        : {{ $agreement->getFields("account_number ") }}
                    </td>
                </tr>
                <tr>
                    <td>
                        Branch Office
                    </td>
                    <td>
                        : {{ $agreement->getFields(" branch_office") }}
                    </td>
                </tr>
            </table>

            <div class="row mt-5 mb-1 text-center">
                <div class="col-5">
                    <p class=" ">
                        Pelanggan/Customer
                    </p>
                </div>

                <!-- Margin TTD -->
                <div class="offset-2 col-11 mb-1 mt-1">
                @if($agreement->getSignature(1))
                    <img src="{{ Storage::url('public/'.$agreement->getSignature(1)->signature) }}" class="img-thumbnail img-signature">
                @else
                    <div style="min-height: 80px; "></div>
                @endif
                </div>

                <div class="col-5">
                    <p class=" ">
                        <strong>{{ $agreement->getFields(" nama") }}</strong>
                    </p>
                </div>
            </div>

            <div style="page-break-after: always;"></div>

            <table class="table table-bordered" style="border-width: 2px; border-color: black;">
                <tr>
                    <td style="width: 50%;">
                        <p class="text-center small-header mb-0">
                            SYARAT DAN KETENTUAN
                        </p>
                        <p class="text-center small-header mb-0">
                            TERMS AND CONDITIONS
                        </p>

                        <ol>
                            <li class="mb-0 small-text">
                                <p class="mb-0 small-text">
                                    Ruang Lingkup Berlangganan
                                </p>
                                <p class="mb-0 small-text">
                                    <i>
                                        Scope of Subscriptions
                                    </i>
                                </p>
                                <ol type="a">
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Perusahaan akan menyediakan jaringan layanan internet sesuai dengan paket
                                            yang dipilih oleh Pelanggan.
                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Company will provide internet service according to the package which is
                                            selected by the Customer.</i>
                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Pelanggan akan diberikan sewa perangkat selama pelaksanaan Ketentuan ini.
                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Customer will provide equipment rental for the duration of this
                                            Agreement.</i>
                                        </p>
                                    </li>
                                </ol>
                            </li>
                            <li class="mb-0 small-text">
                                <p class="mb-0 small-text">
                                    Jangka Waktu
                                </p>
                                <p class="mb-0 small-text">
                                    <i>
                                        Time Period
                                    </i>
                                </p>
                                <ol type="a">
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Ketentuan ini mulai berlaku sesuai dengan ketentuan dalam formulir

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>This Agreement shall be effective as of the date of signing the form.</i>

                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Jangka waktu dapat diperpanjang sesuai dengan kesepakatan Para Pihak.
                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The term may be extended based on the mutual agreement of the Parties.</i>
                                    </li>
                                </ol>
                            </li>
                            <li class="mb-0 small-text">
                                <p class="mb-0 small-text">
                                    Hak dan Kewajiban Perusahaan
                                </p>
                                <p class="mb-0 small-text">
                                    <i>Rights and Obligations of Company</i>
                                </p>
                                <ol>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Perusahaan berhak menerima pembayaran sesuai dengan ketentuan Poin 5 pada
                                            Ketentuan ini.
                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Company is entitled to receive payment in accordance with the terms
                                            outlined in Point 5 of this Agreement.</i>
                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Perusahaan akan memberikan pelayanan sesuai dengan paket yang dipilih oleh
                                            Pelanggan.
                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Company will provide services according to the package selected by the Customer.</i>
                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Perusahaan akan menyediakan layanan pelanggan.
                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Company will provide the customer support services.</i>
                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Perusahaan akan menyediakan perangkat sebagai alat pendukung dalam
                                            menyediakan layanan internet kepada Pelanggan.
                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Company will provide equipment as a supporting tool in delivering
                                            internet services to the Customer.</i>
                                        </p>
                                    </li>
                                </ol>
                            </li>
                            <li class="mb-0 small-text">
                                <p class="mb-0 small-text">
                                    Hak dan Kewajiban Pelanggan
                                </p>
                                <ol>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Pelanggan berhak menerima layanan sesuai dengan paket yang dipilih oleh
                                            Pelanggan.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Customer has the rights to receive services according to the package
                                            selected.</i>

                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Pelanggan wajib menjaga perangkat yang dipasang di lokasi Pelanggan.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Customer is required to maintain the equipment that is installed at
                                            their location.</i>

                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Pelanggan wajib membayar layanan secara tepat waktu.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Customer is obligated to make timely payments for the services.</i>
                                    </li>
                                </ol>
                            </li>
                            <li class="mb-0 small-text">
                                <p class="mb-0 small-text">
                                    Biaya dan Tata Cara Pembayaran
                                </p>
                                <ol>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Pelanggan wajib melakukan pembayaran sesuai dengan paket layanan paling
                                            lambat pada tanggal yang tertera dalam formulir.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Customer is required to make payment according to the service package no
                                            later than the date specified in the form.</i>

                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Apabila Pelanggan melakukan keterlambatan pembayaran, maka Perusahaan berhak
                                            memutus sementara layanan internet sampai dengan Pelanggan membayarkan
                                            layanan yang telah disepakati dengan tambahan 2x24 jam dari tanggal
                                            pembayaran untuk koneksi internet dapat digunakan oleh Pelanggan.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>If the Customer delays payment, the Company has the right to temporarily
                                            suspend the internet service until the Customer settles the outstanding
                                            payment, with an additional 2x24 hours from the payment date for the
                                            internet connection to be restored.</i>
                                        </p>
                                    </li>
                                </ol>
                            </li>
                            <li class="mb-0 small-text">
                                <p class="mb-0 small-text">
                                    Kerahasiaan
                                </p>
                                <p class="mb-0 small-text">
                                    <i>Confidentiality</i>
                                </p>
                                <p class="mb-0 small-text">
                                    Untuk hal-hal yang telah disepakati Para Pihak dalam Syarat dan Ketentuan ini, Para
                                    Pihak masing-masing wajib menjaga kerahasiaan isi dan ketentuan dalam Syarat dan
                                    Ketentuan ini dan seluruh informasi atau data, baik secara lisan, elektronik atau
                                    tertulis yang diterima dari Pihak lainnya, dan tidak akan memberikan hal tersebut
                                    kepada pihak ketiga tanpa pemberitahuan dan persetujuan tertulis terlebih dahulu
                                    dari Pihak lainnya, kecuali diharuskan oleh hukum dan atas dasar instrumen
                                    pemerintah.

                                </p>
                                <p class="mb-0 small-text">
                                    <i>Otherwise agreed upon by the Parties in this Terms and Conditions, each Party is
                                    obligated to maintain the confidentiality of the contents and provisions of this
                                    Terms and Conditions, as well as any information or data, whether verbally,
                                    electronic, or written that is received from the other Party, neither Party shall
                                    disclose such information to any third party without prior written notice and
                                    consent from the other Party, unless when required by law or based on government
                                    instruments.</i>

                                </p>
                            </li>
                            <li class="mb-0 small-text">
                                <p class="mb-0 small-text">
                                    Pernyataan dan Jaminan

                                </p>
                                <ol>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Para Pihak adalah subjek hukum yang sah berdasarkan hukum Negara Republik
                                            Indonesia dan berwenang untuk membuat Syarat dan Ketentuan ini.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Parties are legal entities recognized under the laws of the Republic of
                                            Indonesia and are authorized to enter into these Terms and Conditions.</i>

                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Para Pihak menjamin untuk melaksanakan semua ketentuan dalam Syarat dan
                                            Ketentuan ini.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Parties guarantee to fulfill all provisions of this Terms and
                                            Conditions.</i>

                                        </p>
                                    </li>

                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Para Pihak menyatakan dan menjamin bahwa semua data atau informasi yang
                                            disampaikan kepada Perusahaan merupakan informasi yang benar.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Parties declare and guarantee that all data or information provided to
                                            the Company is accurate.</i>

                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Pelanggan akan menjamin dan bertanggung jawab atas perangkat yang dipasang
                                            di lokasi Pelanggan. Pelanggan wajib membayar ganti rugi sebesar Rp
                                            300.000,- apabila ditemukan perangkat rusak dan/atau hilang.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Customer shall guarantee and be responsible for the equipment installed
                                            at their location. The Customer is required to pay a compensation fee of IDR
                                            300,000 if the equipment is found to be damaged and/or lost.</i>

                                        </p>
                                        <p class="mb-0 small-text">
                                            Pelanggan dengan ini menyetujui dan menyanggupi untuk melaksanakan
                                            kewajibannya berdasarkan Syarat dan Ketentuan ini.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Customer hereby agrees and undertakes to implement the obligations under
                                            these Terms and Conditions.</i>

                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Pelanggan menjamin bahwa apabila timbul gugatan dan/atau tuntutan akibat
                                            dari segala tindakan dan/atau pelanggaran yang dilakukan oleh Pelanggan,
                                            maka Pelanggan membebaskan Perusahaan dari segala bentuk tanggung jawab,
                                            dampak, dan ganti rugi akibat gugatan dan/atau tuntutan tersebut.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Customer guarantees that if any lawsuit and/or claim arises due to any
                                            actions and/or violations committed by the Customer, the Customer shall
                                            indemnify the Company from any form of liability, impact, and compensation
                                            resulting from such lawsuit and/or claim.</i>

                                        </p>
                                    </li>
                                </ol>
                            </li>
                            <li class="mb-0 small-text">
                                <p class="mb-0 small-text">
                                    Pengakhiran Berlangganan

                                </p>
                                <p class="mb-0 small-text">
                                    <i>Termination of Subscriptions</i>
                                </p>
                                <ol type="a">
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Perusahaan dapat memutuskan Syarat dan Ketentuan ini tanpa perlu
                                            pemberitahuan kepada Pelanggan, apabila:

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Company may terminate this Terms and Conditions without any notice to
                                            the Customer, if:</i>
                                        </p>
                                        <ol class="i">
                                            <li class="mb-0 small-text">
                                                <p class="mb-0 small-text">
                                                    Pelanggan melanggar syarat-syarat dan/atau ketentuan-ketentuan dalam
                                                    Syarat dan Ketentuan ini; dan
                                                </p>
                                                <p class="mb-0 small-text">
                                                    <i>The Customer violates the terms and/or provisions of this Terms and
                                                    Conditions; and</i>
                                                </p>
                                            </li>
                                            <li class="mb-0 small-text">
                                                <p class="mb-0 small-text">
                                                    Terjadi pelanggaran hukum yang dilakukan oleh Pelanggan yang
                                                    mengakibatkan Pelanggan tidak dapat melaksanakan kewajibannya
                                                    berdasarkan Syarat dan Ketentuan ini.

                                                </p>
                                                <p class="mb-0 small-text">
                                                    <i>There has been a legal violation committed by the Customer, which
                                                    results in the Customer being unable to fulfill its obligations
                                                    under these Terms and Conditions.</i>
                                                </p>
                                            </li>
                                        </ol>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Apabila Pelanggan melakukan pemutusan layanan ini sebelum jangka waktu
                                            berakhir, maka Pelanggan akan dikenakan denda secara prorata berdasarkan
                                            sisa jangka waktu layanan.
                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>If the Customer terminates this service before the end of the term, the
                                            Customer will be subject to a prorated penalty based on the remaining
                                            service term.</i>
                                        </p>
                                    </li>
                            </li>
                        </ol>
                        </li>
                        </ol>
                    </td>
                    <td style="width: 50%;">
                        <ol start="8">
                            <li class="mb-0 small-text">
                                <ol type="a" start="3">
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Apabila Pelanggan tidak memperpanjang layanan, maka Pelanggan wajib
                                            memberitahukan paling lambat 60 (enam) puluh hari sebelum tanggal efektif
                                            jangka waktu berakhir dan Pelanggan wajib mengembalikan perangkat yang
                                            dipasang di lokasi Pelanggan.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>If the Customer does not renew the service, the Customer must notify the
                                            Company no later than 60 (sixty) days before the effective end date of the
                                            term and is required to return the equipment installed at the Customer's
                                            location.</i>

                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Para Pihak setuju dan sepakat untuk mengesampingkan berlakunya ketentuan
                                            Pasal 1266 Kitab Undang-Undang Hukum Perdata terhadap Syarat dan Ketentuan
                                            ini dalam hal diperlukan suatu putusan pengadilan untuk mengakhiri Syarat
                                            dan Ketentuan ini.
                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>The Parties agree and consent to exclude the application of the provisions
                                            of Article 1266 of the Indonesian Civil Code to these Terms and Conditions,
                                            in the event that a court decision is required to terminate these Terms and
                                            Conditions.</i>
                                        </p>
                                    </li>
                                </ol>
                            </li>
                            <li class="mb-0 small-text">
                                <p class="mb-0 small-text">
                                    Force Majeure
                                </p>
                                <p class="mb-0 small-text">
                                    Force Majeure
                                </p>
                                <ol type="a">
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Yang dimaksud dengan force majeure dalam Syarat dan Ketentuan ini adalah
                                            peristiwa yang terjadi di luar kemampuan Para Pihak untuk mengatasinya, dan
                                            bukan disebabkan karena kesalahan ataupun kelalaian Para Pihak, seperti
                                            antara lain, bencana alam, kebakaran, peperangan, huru hara, pemberontakan,
                                            wabah, epidemi, pandemi, sabotase, dan tindakan pemerintah di bidang
                                            moneter, yang secara langsung mengganggu pelaksanaan kewajiban Para Pihak
                                            dalam Syarat dan Ketentuan ini dan dinyatakan oleh Pemerintah sebagai force
                                            majeure.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>
                                                Force majeure in this Terms and Conditions means an event that occurs beyond
                                                the ability of the Parties to overcome it, and is not caused by the fault or
                                                negligence of the Parties, such as, among others, natural disasters, fires,
                                                wars, riots, rebellions, plagues, epidemics, pandemics, sabotage, and
                                                government actions in the monetary sector, which directly disrupt the
                                                implementation of the Parties' obligations in this Terms and Conditions and
                                                are declared by the Government as force majeure.
                                            </i>

                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Apabila terjadi force majeure sebagaimana di maksud dalam huruf a di atas,
                                            maka Pihak yang berada dalam keadaan memaksa berkewajiban memberitahu kan
                                            Pihak lainnya dalam waktu selambat-lambatnya 7 (tujuh) hari kalender.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>
                                                In the event of force majeure as in letter a above, the Party in force must
                                                notify the other Party by 7 (seven) calendar days.
                                            </i>

                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Force majeure sebagaimana dimaksud dalam Pasal ini tidak menghapuskan atau
                                            mengakhiri Syarat dan Ketentuan ini serta Para Pihak wajib menyelesaikan
                                            kewajibannya masing-masing.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>
                                                Force majeure as referred to in this Article, does not cancel or terminate
                                                these Terms and Conditions, and the Parties are still obligated to fulfill
                                                their respective obligations.
                                            </i>

                                        </p>
                                    </li>
                                </ol>
                            </li>
                            <li class="mb-0 small-text">
                                <p class="mb-0 small-text">
                                    Layanan Pelanggan

                                </p>
                                <p class="mb-0 small-text">
                                    <i>
                                        Customer Service
                                    </i>
                                </p>
                                <p class="mb-0 small-text">
                                    Pelanggan dapat menghubungi layanan pelanggan apabila terjadi kendala, melalui:

                                </p>
                                <p class="mb-0 small-text">
                                    <i>
                                        The Customer shall contact customer service if there is any trouble, through:
                                    </i>
                                </p>
                                <table style="border: 0;">
                                    <tr>
                                        <td style="width: 25px;">Telephone</td>
                                        <td style="width: auto;">: {{ $agreement->getFields(" telephon") }}</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 25px;">Email</td>
                                        <td style="width: auto;">: {{ $agreement->getFields(" email") }}</td>
                                    </tr>
                                </table>
                            </li>
                            <li class="mb-0 small-text">
                                <p class="mb-0 small-text">
                                    Keterpisahan
                                </p>
                                <p class="mb-0 small-text">
                                    Severability
                                </p>
                                <ol type="a">
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Apabila sebagian Poin dalam Syarat dan Ketentuan ini batal demi hukum atau
                                            dibatalkan, maka pembatalan itu tidak akan membatalkan isi Pasal-Pasal
                                            lainnya atau tidak membatalkan Syarat dan Ketentuan ini.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>
                                                If some of the Points in these Terms and Conditions are null and void or
                                                cancelled, then the cancellation will not cancel the contents of the other
                                                Articles or cancel these Terms and Conditions.
                                            </i>

                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Ketidakberlakuan pasal dan ketentuan tersebut sebagaimana dimaksud pada Poin
                                            11, tidak akan mempengaruhi berlakunya atau dapat dilaksanakannya setiap
                                            ketentuan lainnya dari Syarat dan Ketentuan ini dan Para Pihak akan segera
                                            melakukan negosiasi untuk ketentuan pengganti, jika diperlukan, yang akan
                                            dituangkan dalam Adendum yang menjadi bagian tak terpisahkan dari Syarat dan
                                            Ketentuan ini.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>
                                                The invalidity of the articles and provisions as referred to in Point 11,
                                                will not affect the validity or enforceability of any other provisions of
                                                this Terms and Conditions and the Parties will immediately negotiate for
                                                replacement provisions, if necessary, which will be stated in an Addendum
                                                which is an integral part of this Terms and Conditions.
                                            </i>

                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Apabila seluruh isi dalam Syarat dan Ketentuan ini dibatalkan, maka tidak
                                            akan membatalkan Pasal Pengakhiran Syarat dan Ketentuan, Pasal mengenai
                                            Hukum Yang Berlaku dan Penyelesaian Perselisihan, dan Pasal Keterpisahan
                                            ini.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>
                                                If the entire contents of the Points in this Terms and Conditions are
                                                cancelled, then it will not cancel the Termination of Terms and Conditions
                                                Article, the Article on Governing Law and Dispute Resolution and this
                                                Severability Article.
                                            </i>
                                        </p>
                                    </li>
                                </ol>
                            </li>
                            <li class="mb-0 small-text">
                                <p class="mb-0 small-text">
                                    Hukum yang Berlaku dan Penyelesaian Perselisihan

                                </p>
                                <p class="mb-0 small-text">
                                    <i>
                                        Governing Law and Dispute Resolution
                                    </i>
                                </p>
                                <ol>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Pelaksanaan Syarat dan Ketentuan ini tunduk pada ketentuan dan peraturan
                                            perundang-undangan yang berlaku menurut Hukum Republik Indonesia.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>
                                                The implementation of these Terms and Conditions is subject to the
                                                provisions and regulations applicable according to the Laws of the Republic
                                                of Indonesia.
                                            </i>
                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Dalam hal terjadi perselisihan di antara Para Pihak mengenai pelaksanaan
                                            Perjanjian ini, maka Para Pihak dengan didasari itikad baik sepakat untuk
                                            menyelesaikannya secara musyawarah untuk mufakat.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>
                                                In the event of a dispute between the Parties regarding the implementation
                                                of this Agreement, the Parties in good faith agree to resolve it through
                                                deliberation to reach a consensus.
                                            </i>

                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Dalam hal Para Pihak tidak dapat menyelesaikan sengketa(-sengketa) dalam
                                            waktu 30 (tiga puluh) hari sejak tanggal suatu sengketa tersebut diajukan
                                            oleh suatu Pihak dan diberitahukan kepada Pihak lainnya (atau suatu jangka
                                            waktu lain yang disepakati bersama antara Para Pihak), sengketa harus
                                            diajukan ke dan secara final diselesaikan melalui Pengadilan Negeri Jakarta
                                            Barat.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>
                                                In the event that the Parties are unable to resolve the dispute(s) within 30
                                                (thirty) days from the date a dispute is submitted by a Party and notified
                                                to the other Party (or another period mutually agreed upon by the Parties),
                                                the dispute must be submitted to and finally resolved through the West
                                                Jakarta District Court.
                                            </i>
                                        </p>
                                    </li>
                                </ol>
                            </li>
                            <li class="mb-0 small-text">
                                <p class="mb-0 small-text">
                                    Lain-lain

                                </p>
                                <p class="mb-0 small-text">
                                    <i>
                                        Others
                                    </i>
                                </p>
                                <ol type="a">
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Perusahaan tidak bertanggung jawab atas keterlambatan koneksi dan/atau akses
                                            atas layanan yang bukan karena kesalahan dan/atau kelalaian Perusahaan.
                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>
                                                The Company is not responsible for any delays in connection and/or access to
                                                services that are not caused by the Company's fault and/or negligence.
                                            </i>

                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Apabila koneksi terputus secara tiba-tiba, maka Pelanggan tidak dapat
                                            menuntut kerugian baik secara materiil maupun immateriil kepada Perusahaan.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>
                                                If the connection is suddenly interrupted, the Customer cannot claim any
                                                material or immaterial losses from the Company.
                                            </i>

                                        </p>
                                    </li>
                                    <li class="mb-0 small-text">
                                        <p class="mb-0 small-text">
                                            Demikian Pelanggan dengan ini menyetujui dan mengikuti seluruh syarat dan
                                            ketentuan ini.

                                        </p>
                                        <p class="mb-0 small-text">
                                            <i>
                                                Thus, the Customer hereby agrees and follows all these terms and conditions.
                                            </i>
                                        </p>
                                    </li>
                                </ol>
                            </li>
                        </ol>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>