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
                <div class="col-5 text-center">
                    <h5><strong>COLLABORATION AGREEMENT</strong></h5>
                    <p>
                        No. GTCG/INDONESIA/{{ $month }}/{{ $year }}/{{ $agreementLetter->agreement_letter_number }}
                    </p>
                </div>
                <div class="offset-2 col-5 text-center">
                    <h5><strong>PERJANJIAN KERJASAMA</strong></h5>
                    <p>
                        No. GTCG/INDONESIA/{{ $month }}/{{ $year }}/{{ $agreementLetter->agreement_letter_number }}
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                    This Collaboration Agreement ("<strong>Agreement</strong>") is made and entered, on {{ $date }} {{ $month }} {{ $year }} by and between:
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        Perjanjian Kerjasama ("<strong>Perjanjian</strong>") ini dibuat dan ditandatangani, pada tanggal {{ $date }} {{ $month }} {{ $year }}  oleh dan diantara:
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol type="I">
                        <li style="margin-bottom: 15px;">
                            <strong>{{ $company['name'] ?? '' }}</strong>, a limited liability company established under the laws of the Republic of Indonesia, domiciled in Indonesia, having its registered office at {{ $company['address'] ?? '' }}, in this matter represented by <strong>{{ $company['director'] ?? '' }}</strong>, acting in his capacity as <strong>Director</strong>, (hereinafter referred to as <strong>First Party</strong>); and
                        </li>
                        <li style="margin-bottom: 15px;">
                            <strong>{{ $agreementLetter->quote ? $agreementLetter->quote->customer->name : '' }}</strong> , a company established under the laws of Indonesia, domiciled in {{ $agreementLetter->quote ? $agreementLetter->quote->customer->address : '' }}, in this matter represented by <strong>{{ $agreementLetter->quote ? $agreementLetter->quote->customer->director : '' }}</strong> , acting in his capacity as <strong>Director</strong>, (hereinafter referred to as <strong>Second Party</strong>). 
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol type="I">
                        <li style="margin-bottom: 15px;">
                            <strong>{{ $company['name'] ?? '' }}</strong>, suatu perseroan terbatas yang didirikan dan dibentuk berdasarkan hukum di Republik Indonesia, berkedudukan di Indonesia, dengan kantor terdaftarnya di {{ $company['address'] ?? '' }}, dalam hal ini diwakili oleh {{ $company['director'] ?? '' }}, dalam kapasitasnya sebagai Direktur (untuk selanjutnya disebut sebagai <strong>Pihak Pertama</strong>); dan
                        </li>
                        <li style="margin-bottom: 15px;">
                            <strong>{{ $agreementLetter->quote ? $agreementLetter->quote->customer->name : '' }}</strong> , suatu perseroan yang didirikan dan dibentuk berdasarkan hukum di Indonesia, berkedudukan di {{ $agreementLetter->quote ? $agreementLetter->quote->customer->address : '' }},dalam hal ini diwakili oleh <strong>{{ $agreementLetter->quote ? $agreementLetter->quote->customer->director : '' }}</strong> dalam kapasitasnya sebagai <strong>Direktur</strong> (untuk selanjutnya disebut <strong>Pihak Kedua</strong>).
                        </li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        First Party and Second Party are hereinafter individually referred to as a “Party” and collectively as the “Parties”.
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        Pihak Pertama dan Pihak Kedua untuk selanjutnya secara sendiri-sendiri disebut sebagai "Pihak " dan bersama-sama sebagai "Para Pihak ".
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-left">
                    <p>
                        <strong>
                            RECITALS  
                        </strong>
                    </p>
                </div>
                <div class="offset-2 col-5 text-left">
                    <p>
                        <strong>
                            PEMBUKAAN
                        </strong>
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-left">
                    <p>
                    Whereas, First Party is the authorized service provider of digital advertising services using programmatic advertising services like Google, Facebook, DoubleClick, Criteo, Adobe, Sitescout and other providers. (hereinafter referred to as “EMC Network”)
                    </p>
                </div>
                <div class="offset-2 col-5 text-left">
                    <p>
                    Bahwa Pihak Pertama adalah penyedia jasa periklanan online resmi menggunakan periklanan berbasis programatis seperti Google, Facebook, DoubleClick, Criteo, Adobe, Sitescout and other providers. (selanjutnya disebut sebagai “Jaringan EMC”);

                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-left">
                    <p>
                    Whereas, Second Party is the owner of website please insert which conducts business activity consisted of please insert. 
                    </p>
                </div>
                <div class="offset-2 col-5 text-left">
                    <p>
                        Bahwa Pihak Kedua adalah pemilik dari situs please insert yang melakukan kegiatan usaha meliputi please insert 
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-left">
                    <p>
                    Whereas, Second Party desires to use the services provided by First Party in the form of digital advertising services (hereinafter referred to as “Services”) subject to the terms and conditions hereinafter set forth on this Agreement and Insertion Order;
                    </p>
                </div>
                <div class="offset-2 col-5 text-left">
                    <p>
                    Bahwa Pihak Kedua bermaksud untuk menggunakan jasa yang diberikan oleh Pihak Pertama yang berupa jasa periklanan digital (selanjutnya disebut sebagai “Jasa”) dengan syarat-syarat dan ketentuan-ketentuan yang selanjutnya ditentukan pada Perjanjian ini dan Insertion Order; dan
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-left">
                    <p>
                        Now, Therefore, either Party hereby agrees to the following terms and conditions:                    
                    </p>
                </div>
                <div class="offset-2 col-5 text-left">
                    <p>
                    Oleh karenanya, saat ini, masing-masing Pihak dengan ini setuju pada syarat-syarat dan ketentuan-ketentuan sebagai berikut:
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-left">
                    <strong>
                    ARTICLE 1 – SCOPE OF AGREEMENT
                    </strong>
                </div>
                <div class="offset-2 col-5 text-left">
                    <strong>
                    PASAL 1 – RUANG LINGKUP KERJASAMA
                    </strong>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-left">
                    <ol>
                        <li style="margin-bottom: 15px; margin-top: 15px;">
                            Second Party hereby appoints First Party, and First Party accepts the appointment to provide Services to the Second Party.
                        </li>
                        <li style="margin-bottom: 15px;">
                            Second Party shall provide First Party with information and/or advertisement material to be placed on EMC Network, including but not limited to images, logos, sounds, videos, words, and/or sentences (hereinafter referred to as “Application Information”), used limited to the interest of performance of the Services.
                        </li>
                        <li style="margin-bottom: 15px;">
                            The Product Information, which is displayed on EMC Network, hereinafter referred to as “Campaign”.
                        </li>
                        <li style="margin-bottom: 15px;">
                            The provided Services is stated under signed quotation no {{ $agreementLetter->quote ? $agreementLetter->quote->quote_number_result : '' }} attached together with this agreement. 
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-left">
                    <ol>
                        <li style="margin-bottom: 15px; margin-top: 15px;">
                            Pihak Kedua dengan ini menunjuk Pihak Pertama, dan Pihak Pertama menerima penunjukkan untuk memberikan Jasa kepada Pihak Kedua.
                        </li>
                        <li style="margin-bottom: 15px;">
                            Pihak Kedua akan memberikan Pihak Pertama mengenai informasi dan/atau materi iklan atas kegiatan periklanan yang akan dijalankan di  Jaringan EMC termasuk namun tidak terbatas pada gambar, logo, suara, video, kata, dan/atau kalimat (selanjutnya disebut sebagai “Informasi Aplikasi”), yang digunakan terbatas untuk kepentingan pelaksanaan Jasa.
                        </li>
                        <li style="margin-bottom: 15px;">
                            Informasi Produk yang ditampilkan pada EMC Network selanjutnya disebut sebagai “Kampanye”
                        </li>
                        <li style="margin-bottom: 15px;">
                            Jasa yang diberikan adalah yang disebutkan dalam surat penawaran {{ $agreementLetter->quote ? $agreementLetter->quote->quote_number_result : '' }} yang telah dilampirkan bersamaan dengan perjanjian ini.
                        </li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                    For the Services rendered by First Party to Second Party, Second Party will pay the service fee to First Party for every Click which has been successfully generated by EMC Network’s visitors who accesses the Campaign (hereinafter referred to as “Cost Per Click”). 
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        Atas Jasa yang diberikan oleh Pihak Pertama kepada Pihak Kedua, Pihak Kedua akan membayarkan biaya jasa kepada Pihak Pertama untuk setiap Klik yang berhasil diperoleh dari pengunjung Jaringan EMC yang mengakses Kampanye (selanjutnya disebut sebagai “Biaya Per Klik”).
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol start="5">
                        <li style="margin-bottom: 15px;">
                            The Services shall be performed up to the Cost Per click is reaching the maximum budget as stated on Insertion Order as an integral part of this Agreement (hereinafter referred to as “Insertion Order”).
                        </li>
                        <li style="margin-bottom: 15px;">
                            Either Party shall prepare and the required system and technology so the Services can be carried out and either Party has agreed entire technical and operational provisions contained in this Agreement and/or standard operational procedure of either Party.
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol start="5">
                        <li style="margin-bottom: 15px;">
                            Jasa akan dilaksanakan sampai dengan Biaya Per klik mencapai maksimal anggaran yang disebutkan pada Insertion Order yang merupakan satu kesatuan bagian tidak terpisahkan dari Perjanjian ini (selanjutnya disebut sebagai “Insertion Order”).
                        </li>
                        <li style="margin-buttom:15px;">
                            Masing-masing Pihak akan menyiapkan dan melengkapi sistem dan teknologi yang diperlukan agar dapat terhubung satu sama lain sehingga Kerjasama dapat dilaksanakan serta masing-masing Pihak telah menyepakati ketentuan-ketentuan yang bersifat teknis dan operasional sebagaimana yang termaktub dalam Perjanjian ini dan/atau standar operasional prosedur masing-masing Pihak
                        </li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 2 – RIGHTS AND OBLIGATION
                        </strong>
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 2 – HAK DAN KEWAJIBAN
                        </strong>
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li style="margin-bottom: 15px;">
                            Rights and Obligation of First Party:
                            <ol type="a">
                                <li style="margin-bottom: 15px;">
                                    First Party reserves the right to obtain payment of the Cost Per click from Second Party upon the Services provided.
                                </li>
                                <li style="margin-bottom: 15px;">
                                    First Party reserves the right to use Second Party’s Information limited for performance of the Services.
                                </li>
                                <li style="margin-bottom: 15px;">
                                    First Party is obligated to provide the Services to Second Party with provision as stipulated on this Agreement.
                                </li>
                            </ol>
                        </li>
                        <li>
                            Rights and Obligation of Second Party:
                            <ol type="a">
                                <li style="margin-bottom: 15px;">
                                    Second Party reserves the right to obtain the Services with details as explained on Article 1.
                                </li>
                                <li style="margin-bottom: 15px;">
                                    Second Party is obligated to pay the Cost Per click to First Party with amount as stated on Quotation. 
                                </li>
                                <li style="margin-bottom: 15px;">
                                    Second Party is obligated to take care of First Party’s image and reputation.
                                </li>
                            </ol>
                        </li>
                        <li style="margin-bottom: 15px;">
                            Rights and Obligation in this Article shall not waive any implementation of rights and obligation provision of the Parties as stipulated in other articles in this Agreement.
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li>
                            Hak dan Kewajiban Pihak Pertama:
                            <ol type="a">
                                <li style="margin-bottom: 15px;">
                                    Pihak Pertama berhak untuk memperoleh pembayaran Biaya Per Klik dari Pihak Kedua atas Jasa yang diberikan.
                                </li>
                                <li style="margin-bottom: 15px;">
                                    Pihak Pertama berhak untuk menggunakan Informasi Pihak Kedua terbatas hanya untuk keperluan pelaksanaan Jasa.
                                </li>
                                <li style="margin-bottom: 15px;">
                                    Pihak Pertama wajib untuk memberikan Jasa kepada Pihak Kedua dengan ketentuan sebagaimana yang dimaksud dalam Perjanjian ini.
                                </li>
                            </ol>
                        </li>
                        <li>
                            Hak dan Kewajiban Pihak Kedua:
                            <ol type="a">
                                <li style="margin-bottom: 15px;">
                                    Pihak Kedua berhak untuk memperoleh Jasa dengan rincian sebagaimana yang dijelaskan pada Pasal 1.
                                </li>
                                <li style="margin-bottom: 15px;">
                                    Pihak Kedua wajib untuk melakukan pembayaran Biaya Per klik kepada Pihak Pertama dengan nilai sebagaimana yang dimaksud pada Quotation.
                                </li>
                                <li style="margin-bottom: 15px;">
                                    Pihak Kedua wajib menjaga citra dan nama baik Pihak Pertama.
                                </li>
                            </ol>
                        </li>
                        <li style="margin-bottom: 15px;">
                            Hak dan Kewajiban sebagaimana dimaksud dalam Pasal ini tidak mengesampingkan pelaksanaan ketentuan hak dan kewajiban Para Pihak yang diatur dalam pasal lainnya dalam Perjanjian ini.
                        </li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 3 – DATA
                        </strong>
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 3 – DATA
                        </strong>
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li style="margin-bottom: 15px;">
                        The Parties agree that data computation of performance of the Services, including computation of the Cost Per Click, shall use tracker system provided by First Party.
                    </li>
                    <li style="margin-bottom: 15px;">
                        In the event of discrepancy of data of performance of the Services between First Party’s and Second Party’s data, the Parties agree that First Party’s data is deemed as valid data and shall be used by Parties
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li style="margin-bottom: 15px;">
                            Para Pihak sepakat bahwa penghitungan data atas pelaksanaan Jasa, termasuk penghitungan Biaya Per klik , akan menggunakan sistem pelacak (tracker) yang disediakan oleh Pihak Pertama.
                        </li>
                        <li style="margin-bottom: 15px;">
                            Dalam hal terjadi perbedaan data atas pelaksanaan Jasa antara data milik Pihak Pertama dan Pihak Kedua, maka Para Pihak sepakat bahwa data yang akan digunakan dan dianggap benar adalah data milik Pihak Pertama.
                        </li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 4 – PAYMENT
                        </strong>
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 4 – PEMBAYARAN
                        </strong>
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    {!! $agreementLetter->payment_term_english !!}
                </div>
                <div class="offset-2 col-5 text-justify">
                    {!! $agreementLetter->payment_term !!}
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 5 – TERM OF AGREEMENT 
                        </strong>
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 5 – JANGKA WAKTU PERJANJIAN 
                        </strong>
                    </p>
                </div>
            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                        {!! $agreementLetter->period_term !!}
                </div>
                <div class="offset-2 col-5 text-justify">
                    {!! $agreementLetter->period_term_english !!}
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 6 – DEFAULT
                        </strong>
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 6 – WANPRESTASI
                        </strong>
                    </p>
                </div>
            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li style="margin-bottom: 15px;">
                            One of the Parties can be referred to failure to perform its obligation and/or negligent according to this Agreement, in terms of breaching one or more provisions and/or conditions, do not perform provisions or obligations as stipulated in this Agreement, and/or performing provision or obligations as stipulated in this Agreement improperly (hereinafter referred to as <strong>”Default”</strong>).
                        </li>
                        <li style="margin-bottom: 15px;">
                            If either Party is breaching the Agreement in performing its obligation pursuant to this Agreement, such Party is obliged to provide recovery or remedy for the other Party who suffers losses, either material or immaterial, for Default conducted, including but not limited to, (i) clarification and recognition of liability of either Party’s negligence, and (ii) stating that the other Party is not liable for negligence conducted by one of the Parties.

                        </li>
                        <li style="margin-bottom: 15px;">
                            Indemnity or recovery as regulated on paragraph (2) above does not omit the other Party’s rights for looking for other indemnities or recoveries in accordance with prevailing laws.
                        </li>
                        <li style="margin-bottom: 15px;">
                            In terms of Default as stipulated in this Agreement is occurred, and the breaching Party do not recover or remedy such Default in 7 (seven) days upon receiving notification from the non-breaching Party, the non-breaching Party can choose whether to continue or to terminate this Agreement. In the event the non-breaching Party decides to terminate this Agreement, such decision has to be notified in written to the breaching Party.
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li style="margin-bottom: 15px;">
                            Salah satu Pihak dapat dinyatakan gagal melaksanakan kewajibannya dan/atau lalai berdasarkan Perjanjian ini, dalam hal melanggar salah satu atau lebih syarat-syarat dan/atau ketentuan, jika tidak melaksanakan ketentuan-ketentuan atau kewajiban sesuai yang diatur dalam Perjanjian ini, dan/atau melaksanakan ketentuan-ketentuan atau kewajiban sesuai yang diatur dalam Perjanjian ini namun tidak dapat terlaksana dengan semestinya (selanjutnya disebut <strong>“Wanprestasi”</strong>). 
                        </li>
                        <li style="margin-bottom: 15px;">
                            Jika masing-masing Pihak Wanprestasi dalam menjalankan kewajibannya berdasarkan Perjanjian ini, maka Pihak tersebut wajib memberikan pemulihan atau ganti rugi kepada Pihak lain yang mengalami kerugian, baik materiil atau imateriil, atas Wanprestasi yang dilakukannya, termasuk namun tidak terbatas pada, (i) klarifikasi dan pengakuan tanggung jawab atas kelalaian masing-masing Pihak; dan (ii) menyatakan Pihak lain tidak bertanggung jawab atas kelalaian yang dilakukan salah satu Pihak.
                        </li>
                        <li style="margin-bottom: 15px;">
                            Pemulihan atau ganti rugi sebagaimana diatur dalam ayat (2) di atas tidak menghapuskan hak Pihak lain untuk mencari pemulihan atau ganti rugi lain sesuai dengan hukum yang berlaku.
                        </li>
                        <li style="margin-bottom: 15px;">
                            Dalam hal suatu kejadian Wanprestasi berdasarkan Perjanjian ini terjadi, dan Pihak yang lalai tidak melakukan pemulihan atas Wanprestasi tersebut dalam waktu 7 (tujuh) hari setelah memperoleh notifikasi dari Pihak yang tidak lalai, maka Pihak yang tidak lalai dapat memilih apakah tetap meneruskan atau menghentikan Perjanjian. Apabila Pihak yang tidak lalai berkehendak 
                        </li>
                    </ol>
                </div>
            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 7 – TERMINATION
                        </strong>
                    </p>
                    <p>
                        Either Party may terminate this Agreement at any time by giving notice in writing no later than fourteen (14) calendar days prior to the termination date to the other Party:
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 7 – PENGAKHIRAN PERJANJIAN
                        </strong>
                    </p>
                    <p>
                        Setiap Pihak dapat mengakhiri Perjanjian ini setiap saat dengan memberikan pemberitahuan tertulis selambat-lambatnya 14 (empat belas) hari kalendar sebelum tanggal pengakhiran kepada yang lainnya:
                    </p>
                </div>
            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li style="margin-bottom: 15px;">
                            If in case of one of Party is declared as Default and does not conduct recovery or remedy to the non-breaching Party;
                        </li>
                        <li style="margin-bottom: 15px;">
                            if in case of Party, if Party becomes insolvent or is declared bankrupt or is unable to pay its debts as they become due, or commits or permits any act of bankruptcy or liquidated; or
                        </li>
                        <li style="margin-bottom: 15px;">
                            By consent of the Parties.
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li style="margin-bottom: 15px;">
                            Jika dalam hal salah satu Pihak dinyatakan Wanprestasi dan tidak melakukan tindakan pemulihan atas tindakan tersebut kepada Pihak yang tidak lalai;
                        </li>
                        <li style="margin-bottom: 15px;">
                            jika dalam halnya salah satu Pihak, apabila dinyatakan tidak mampu atau dinyatakan pailit atau tidak dapat membayar hutang-hutangnya pada saat jatuh tempo, atau melakukan atau membiarkan setiap tindakan yang mengakibatkan pailit atau dilikuidasi; atau
                        </li>
                        <li style="margin-bottom: 15px;">
                            Berdasarkan kesepakatan Para Pihak.
                        </li>
                    </ol>

                </div>
            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 8 – REPRESENTATIONS AND WARRANTIES
                        </strong>
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 8 – PERNYATAAN DAN JAMINAN
                        </strong>
                    </p>
                </div>
            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li style="margin-bottom: 15px;">
                            Each Party represents and warrants to the other that:
                        <ol type="a">
                            <li style="margin-bottom: 15px;">
                                If a Party is a corporation or other kinds of legal entities, it is a corporation duly organized and validly existing under the laws of Indonesia or its respective country of incorporation, with the corporate authority to conduct its business in the manner in which such business is being conducted and is to be conducted under this Agreement; 
                            </li>
                            <li style="margin-bottom: 15px;">
                                If a Party is a corporation or other kinds of legal entities, it has full corporate power and authority as well as has obtained the related licences in accordance with prevailing laws to execute, deliver, and perform this Agreement; 
                            </li>
                            <li style="margin-bottom: 15px;">
                                If a Party is a corporation or other kinds of legal entities, this Agreement has been duly authorized and executed on its behalf, is a legal, valid and binding obligation on it, and is enforceable against it in accordance with its terms; 
                            </li>
                            <li style="margin-bottom: 15px;">
                                Either Party has, and will maintain in force throughout the term of this Agreement, all required approvals necessary to perform its obligations under this Agreement; 
                            </li>
                            <li style="margin-bottom: 15px;">
                                It is not required to obtain the consent of any other party for the execution, delivery or performance of this Agreement; and the execution, delivery and performance of this Agreement will not constitute a breach of any agreement; nor will it contravene any provision of its deed of establishment or articles of association, or violate, conflict with, or result in a breach of any law or judgment binding on it or to which any of its businesses properties or assets are subject; 
                            </li>
                            <li style="margin-bottom: 15px;">
                                There are no claims, actions, suits or proceedings pending against either Party, the outcome of which could materially and adversely affect the transactions contemplated by this Agreement, and either Party is not subject to any order, writ, injunction or decree which could materially and adversely affect Party’s ability to perform its obligations contemplated by this Agreement; 
                            </li>
                            <li style="margin-bottom: 15px;">
                                There is no provision of any existing law, mortgage, indenture, contract, financing statement, agreement or resolution binding on either Party that would conflict with or any way prevent the execution, delivery, or carrying out of the terms of this Agreement or any other document or agreement referred to in this Agreement
                            </li>
                        </ol>
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li style="margin-bottom: 15px;">
                            Masing-masing Pihak menyatakan dan    menjamin kepada pihak lainnya bahwa:
                            <ol type="a">
                                <li style="margin-bottom: 15px;">
                                    Jika satu Pihak merupakan suatu perseroan atau jenis badan hukum lainnya, maka merupakan suatu perseroan yang dibentuk dan secara sah didirikan berdasarkan ketentuan hukum di Indonesia atau masing-masing negara pendiri, dengan otoritas perseroan untuk melaksanakan usahanya dalam mana usaha tersebut dilakukan dan akan dilakukan berdasarkan Perjanjian ini;
                                </li>
                                <li style="margin-bottom: 15px;">
                                    Jika suatu pihak merupakan perseroan atau jenis badan hukum lainnya, maka ia memiliki kekuatan dan otoritas perseroan penuh untuk melaksanakan, serta memiliki izin-izin terkait sesuai hukum yang berlaku untuk melaksanakan, menyerahkan, dan melaksanakan Perjanjian ini;
                                </li>
                                <li style="margin-bottom: 15px;">
                                    Jika suatu Pihak merupakan suatu perseroan atau jenis badan hukum lainnya, maka Perjanjian ini telah disahkan dan dilaksanakan untuk kepentingannya, yang sah, berlaku dan memiliki kewajiban yang mengikat didalamnya, dan dapat dilaksanakan sesuai dengan ketentuan-ketentuannya;
                                </li>
                                <li style="margin-bottom: 15px;">
                                    Setiap Pihak telah, dan akan menjaga keberlakukan selama jangka waktu Perjanjian ini, seluruh persetujuan-persetujuan yang diperlukan dalam pelaksanaan kewajiban-kewajibannya berdasarkan Perjanjian ini;
                                </li>
                                <li style="margin-bottom: 15px;">
                                    Bahwa tidak diperlukan ijin dari pihak lain manapun untuk pelaksanaan, penyerahan atau pemenuhan dari Perjanjian ini; dan pelaksanaan, penyerahan dan pemenuhan  dari Perjanjian ini tidak akan mengakibatkan pelanggaran dari perjanjian lainnya; ataupun akan bertentangan dengan ketentuan manapun pada akta pendirian, atau melanggar, bertentangan dengan, atau mengakibatkan pelanggaran terhadap setiap hukum, atau keputusan yang mengikat terhadapnya atau terhadap properti usahanya atau asetnya yang menjadi subyek;
                                <li style="margin-bottom: 15px;">
                                    Bahwa tidak terdapat tuntutan, gugatan atau perkara yang sedang berlangung terhadap masing-masing Pihak dimana hasilnya secara material dapat dan membawa pengaruh yang merugikan bagi transaksi yang timbul dari Perjanjian ini, dan masing-masing Pihak tidak sedang terganggu/terkait dengan perintah, tuntutan, panggilan atau keputusan yang dapat membawa pengaruh negatif bagi kemampuan Pihak untuk melaksanakan kewajiban-kewajibannya yang timbul dari Perjanjian ini;
                                </li>
                                <li style="margin-bottom: 15px;">
                                    Bahwa tidak terdapat ketentuan dari setiap hukum yang berlaku, hak tanggungan, perjanjian khusus, kontrak, peryataan finasial, perjanjian atau keputusan yang mengikat masing-masing Pihak yang bertentangan dengan atau sedemikian rupa menghalangi pelaksanaan, penyerahan, atau pemenuhan ketentuan-ketentuan Perjanjian ini atau dokumen lainnya atau perjanjian yang merujuk pada Perjanjian ini.
                                </li>
                            </ol>
                        </li>
                    </ol>
                </div>
            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 9 – CONFIDENTIALITY
                        </strong>
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 9 – KERAHASIAAN
                        </strong>
                    </p>
                </div>
            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li style="margin-bottom: 15px"> 
                            Either Party agrees to guarantee the confidentiality of any and all information received by such Party in connection with the performance of this Agreement during the term of this Agreement, and any other agreements which are an integral and inseparable part of this Agreement and/or received during the negotiations of this Agreement, and during the negotiations of any other agreements which are an integral and inseparable part of this Agreement, which is obtained from other Parties (hereinafter referred to as <strong>“Confidential Information”</strong>). The Parties agree not to disclose all or any part of the Confidential Information to any third party or otherwise seek to exploit all or any part of such Confidential Information without the prior written consent of the other Party.
                        </li>
                        <li style="margin-bottom: 15px"> 
                            Either Party including the employees and/or organ of company of Party agrees to not directly or indirectly disclose the Confidential Information to any third party for any reason and to only disclose such Confidential Information to the employees requiring access to such Confidential Information in order to perform its obligation.
                        </li>
                        <li style="margin-bottom: 15px"> 
                            Either Party shall ensure that its employees and/or representation of company receiving the Confidential Information shall not use such Confidential Information except in connection with the performance of either Party’s obligations, or disclose the same information to any third party without the written consent of other Party. Either Party is fully liable to the other Party for any misuse or unauthorized disclosure of information by its employees or third party. 
                        </li>
                        <li style="margin-bottom: 15px"> 
                            This confidentiality clause shall not apply to information, which at any time comes into the public domain through no fault of any Party, or it is required to be furnished to any government or public authority in accordance with any law or applicable judicial order to any Party. In such event, any Party must notify the other Party immediately of such occurrence.
                            
                        </li>
                        <li style="margin-bottom: 15px"> 
                            This confidentiality obligation shall not cease even if this Agreement has been terminated.
                        </li>
                    </ol>
                </div>

                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li style="margin-bottom: 15px"> 
                            Masing-masing Pihak setuju untuk menjamin kerahasiaan setiap dan semua informasi yang diterima oleh Pihak tersebut yang berkaitan dengan pelaksanaan Perjanjian ini selama jangka waktu Perjanjian ini, dan setiap perjanjian lain yang merupakan satu kesatuan dan bagian yang tidak terpisahkan dari Perjanjian ini dan/atau yang diterima selama perundingan Perjanjian ini dan selama perundingan perjanjian lain yang merupakan kesatuan bagian dan tidak terpisahkan dari Perjanjian ini, yang diperoleh dari Pihak lain (selanjutnya disebut sebagai <strong>“Informasi Rahasia"</strong>). Para Pihak setuju untuk tidak mengungkapkan semua atau sebagian dari Informasi Rahasia tersebut kepada pihak ketiga lainnya atau dengan cara lain berupaya untuk memanfaatkan semua atau sebagian dari Informasi Rahasia tersebut tanpa persetujuan tertulis terlebih dahulu dari Pihak lainnya.
                        </li>
                        <li style="margin-bottom: 15px"> 
                            Masing-masing Pihak termasuk karyawan dan/atau organ perseroan dari Pihak setuju untuk tidak secara langsung maupun tidak langsung mengungkapkan Informasi Rahasia kepada pihak ketiga lainnya untuk alasan apapun dan hanya mengungkapkan Informasi Rahasia kepada karyawan yang berwenang yang memerlukan akses ke Informasi Rahasia dalam melaksanakan kewajibannya.
                        </li>
                        <li style="margin-bottom: 15px"> 
                            Masing-masing Pihak menjamin bahwa setiap karyawan dan/atau perwakilan perseroan yang menerima Informasi Rahasia tersebut tidak akan mempergunakan Informasi Rahasia tersebut kecuali berkaitan dengan pelaksanaan kewajiban masing-masing Pihak, atau mengungkapkan informasi yang sama kepada pihak ketiga lainnya, tanpa persetujuan tertulisan dari Pihak lainnya. Masing-masing Pihak sepenuhnya bertanggung jawab kepada Pihak lainnya atas penyalahgunaan atau pengungkapan informasi secara tidak sah oleh karyawannya kepada pihak ketiga lainnya
                        </li>
                        <li style="margin-bottom: 15px"> 
                            Klausula kerahasiaan ini tidak berlaku bagi informasi yang setiap saat menjadi milik publik tanpa kesalahan dari pihak manapun atau diminta untuk diberikan kepada pemerintah atau penguasa umum sesuai dengan undang-undang atau perintah/penetapan peradilan yang berlaku bagi setiap Pihak. Dalam hal demikian, masing-masing Pihak harus memberitahukan Pihak lainnya segera tentang kejadian tersebut.
                        </li>
                        <li style="margin-bottom: 15px"> 
                            Kewajiban kerahasiaan ini tidak akan berakhir sekalipun Perjanjian berakhir.
                        </li>
                    </ol>
                </div>
            </div>

             
            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 10 – FORCE MAJEURE
                        </strong>
                    </p>

                    <ol>
                       <li class="margin">
                            A party (“Affected Party”) is not liable for any delay or failure to perform an obligation (other than to pay money) under this Agreement caused by:
                            <ol type="a">
                                <li class="margin">
                                    act of God; or
                                </li>
                                <li class="margin">
                                    war (including civil war), riot, insurrection, civil commotion, vandalism, looting or sabotage (other than in each case caused by its employees) terrorism, governmental act or administrative guidance, or any other reasonably unforeseeable event reasonably beyond the control of the Affected Party.  
                                </li>
                            </ol>
                       </li> 
                       <li class="margin">
                            The Affected Party must notify the other party as soon as practical of any anticipated delay or failure caused by an event referred to in point (a) above (“Event”).  For the avoidance of doubt, a work stoppage, strike or other labor action by a party’s employees may not be invoked by that party as an Event.
                        </li>
                        <li class="margin">
                            The performance of the Affected Party’s obligation is suspended for the period of delay caused by the Event
                       </li>
                       <li class="margin">
                            If:
                            <ol>
                                <li class="margin">
                                    performance of an obligation is prevented by an Event for a period of 60 (sixty) days or more; or
                                </li>
                                <li class="margin">
                                    a delay caused by the Event exceeds 60 (sixty) days,
                                </li>
                            </ol>
                            either party may terminate this Agreement at the expiration of not less than 30 (thirty) days’ notice to the other party.
                       </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 10 – KEADAAN KAHAR 
                        </strong>
                    </p>
                    <ol>
                        <li class="margin">
                            Suatu pihak (“Pihak Yang Terkena”) tidak bertanggungjawab atas keterlambatan atau kegagalan dalam pelaksanaan suatu kewajiban (selain dari pembayaran uang) berdasarkan Perjanjian ini yang diakibatkan oleh:
                            <ol type="a">
                                <li class="margin">
                                    Bencana alam; atau
                                </li>
                                <li class="margin">
                                    Perang (termasuk perang saudara), kerusuhan, huru-hara, kerusuhan sipil, vandalisme, penjarahan atau sabotase (selain dalam setiap kasus yang disebabkan oleh para karyawannya sendiri) terorisme, tindakan pemerintah atau petunjuk administrasi, atau kejadian lain yang tidak dapat diperkirakan secara wajar diluar kendali dari Pihak Yang Terkena. 
                                </li>
                            </ol>
                        </li>
                        <li class="margin">
                            Pihak Yang Terkena wajib memberitahukan kepada pihak lainnya segera pada saat yang memungkinkan dari setiap keterlambatan atau kegagalan yang dapat diantisipasi akibat dari kejadian sebagaimana tercantum pada poin (a) diatas (“Kejadian”). Untuk menghindari terjadinya keragu-raguan, penghentian pekerjaan, pemogokkan atau tindakan buruh lainnya yang dilakukan oleh karyawan dari pihak tidak dapat digunakan oleh pihak tersebut sebagai suatu Kejadian.
                        </li>
                        <li class="margin">
                            Pelaksanaan dari kewajiban Pihak Yang Terkena ditunda selama jangka waktu keterlambatan akibat Kejadian.
                        </li>
                        <li class="margin">
                            Jika:
                            <ol type="a">
                                <li class="margin">
                                    Pelaksanaan dari suatu kewajiban tertunda oleh Kejadian selama jangka waktu 60 (enam puluh) atau lebih; atau
                                </li>
                                <li class="margin">
                                    Suatu penundaan akibat Kejadian melebihi 60 (enam puluh) hari,
                                </li>
                            </ol>
                            salah satu pihak dapat mengakhiri Perjanjian ini tidak kurang 30 (tiga puluh) hari sejak saat berakhirnya pemberitahuan kepada pihak lainnya.
                        </li>
                    </ol>


                </div>
            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 11 – LIMITATION OF LIABILITY
                        </strong>
                    </p>
                    <p>
                        Notwithstanding anything contained herein, neither Party shall be liable for any indirect, incidental, special, exemplary, or consequential damages of any kind and however caused or for business interruption or loss of profits, business opportunities, or goodwill arising hereunder even if such party has been advised in writing of the possibility of such damages.
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 11 – BATASAN TANGGUNG JAWAB
                        </strong>
                    </p>
                    <p>
                        Tanpa mengabaikan segala hal yang terkandung di dalam, masing-masing Pihak tidak bertanggung jawab atas segala kerugian tidak langsung, incidental, khusus, peringatan atau konsekuensial apapun dan segala bentuk disebabkan atau untuk gangguan bisnis atau kehilangan keuntungan, peluang bisnis, atau itikad baik yang timbul berdasarkan Perjanjian ini bahkan jika pihak tersebut telah disarankan secara tertulis atas kemungkinkan kerusakan tersebut
                    </p>
                </div>


            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 12 – INDEMNIFICATION
                        </strong>
                    </p>
                    <p>
                        Either Party shall not be responsible, defend, indemnify and hold harmless the other Party of any matters or problems and against any and all claims, demands, losses, damages, costs, liabilities and expenses (including, but not limited to, attorneys’ fees and costs of lawsuit) of any kind, on account of any actual or alleged loss, damage or injury to any person or corporation or any property arising out of or in connection with its Default, or breach of any provision under this Agreement.
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 12 – JAMINAN
                        </strong>
                    </p>
                    <p>
                        Masing-masing Pihak tidak bertanggungjawab, membela, menjamin dan menanggung Pihak lainnya dari setiap hal atau masalah dan terhadap setiap dan semua tuntutan, permintaan, kerugian, kerusakan, biaya, kewajiban dan biaya (termasuk tetapi tidak terbatas kepada biaya pengacara dan biaya gugatan) apapun jenisnya, baik kerugian, kerusakan, kecelakaan yang nyata maupun yang diajukan oleh pihak ketiga yang timbul dari atau berkaitan dengan kelalaian, Wanprestasi yang dilakukannya, atau pelanggaran atas ketentuan dalam Perjanjian ini.
                    </p>
                </div>
            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 13 – ASSIGNMENT
                        </strong>
                    </p>
                    <p>
                        Unless otherwise provided in this Agreement, the rights and obligations of one Party under this Agreement shall not be assigned, delegated, or otherwise disposed of without prior written consent from the other Party. If there is assignment due to merger, acquisition or other change of control of the transferring Party, such Party shall inform to other Party in written.
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 13 – PENGALIHAN
                        </strong>
                    </p>
                    <p>
                        Kecuali dinyatakan sebaliknya dalam Perjanjian ini, hak dan kewajiban suatu Pihak dalam Perjanjian ini tidak boleh dialihkan, didelegasikan atau dengan cara lain tidak dipenuhi tanpa persetujuan tertulis terlebih dahulu dari Pihak lainnya. Apabila terdapat pengalihan dikarenakan penggabungan, akuisisi atau perubahan pengendalian dari Pihak yang mengalihkan, Pihak tersebut harus memberitahukan Pihak yang lainnya secara tertulis.
                    </p>
                </div>
            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 14 – GOVERNING LAW AND SETTLEMENT OF DISPUTE
                        </strong>
                    </p>
                    <ol>
                        <li class="margin">
                            It is agreed that this Agreement and any other documents executed in conjunction herewith, shall be governed by, construed, and enforced in accordance with the laws of Indonesia.
                        </li>
                        <li class="margin">
                            Any unresolved issues or questions of interpretation arising in connection with this Agreement shall be amicably and should such amicable solution cannot be settled within 30 (thirty) calendar days, then shall be settled through BANI Arbitration Centre, with Arbitration regulation as regulated by prevailing laws.
                        </li>
                    </ol>



                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 14 – HUKUM YANG BERLAKU DAN PENYELESAIAN PERSELISIHAN
                        </strong>
                    </p>
                    <ol>
                        <li class="margin">
                            Telah disepakati bahwa Perjanjian ini dan dokumen lainnya yang dilaksanakan bersamaan dengannya, diatur oleh, dianggap, dan dilaksanakan sesuai dengan perundang-undangan negara Indonesia.
                        </li>
                        <li class="margin">
                            Setiap masalah yang tidak dapat diselesaikan atau pertanyaan-pertanyaan dari interpretasi yang timbul dalam hubungannya dengan Perjanjian ini akan diselesaikan secara musyawarah dan dalam hal tidak tercapainya musyawarah dalam waktu 30 (tiga puluh) hari kalender, maka Para Pihak sepakat untuk menyelesaikan Sengketa melalui BANI Arbitration Center, dengan aturan Arbitrase yang diatur oleh undang-undang yang berlaku.
                        </li>
                    </ol>
                </div>
            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 15 – GOVERNING LANGUAGE 
                        </strong>
                    </p>
                    <p>
                        The Agreement is made in 2 (two) languages, English and Indonesian. The parties hereto agree that the English version of this Agreement shall be controlling for all purposes and that the Indonesian version has been prepared to fulfill Law No. 24 Year 2009 regarding National Flag, Language, Coat of Arms and Anthem Republic of Indonesia.
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 15 – BAHASA YANG BERLAKU
                        </strong>
                    </p>
                    <p>
                        Perjanjian dibuat dalam 2 (dua) bahasa yaitu Bahasa Inggris dan Bahasa Indonesia. Para pihak setuju bahwa versi Bahasa Inggris dari Perjanjian ini adalah versi yang berlaku untuk semua keperluan dan bahwa terjemahan dalam Bahasa Indonesia dipersiapkan untuk memenuhi Undang-Undang No. 24 Tahun 2009 tentang Bendera, Bahasa dan Lambang Negara serta Lagu Kebangsaan Republik Indonesia.
                    </p>
                </div>
            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 16 – INTELLECTUAL PROPERTY RIGHTS
                        </strong>
                    </p>
                    <ol>
                        <li class="margin">
                            Parties hereby agree that either Party has no rights to use any intellectual property rights which are born by performance of this Agreement and/or have been owned by other Party for other interest beyond this Agreement, except it has been agreed by Parties pursuant to this Agreement.
                        </li>
                        <li class="marginss">
                            Either party shall retain ownership of its respective pre-existing intellectual property rights.
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 16 – HAK KEKAYAAN INTELEKTUAL
                        </strong>
                    </p>
                    <ol>
                        <li class="margin">
                            Para Pihak sepakat bahwa masing-masing Pihak tidak berhak untuk menggunakan setiap hak kekayaan intelektual yang timbul dari pelaksanaan Perjanjian ini dan/atau telah dimiliki oleh Pihak lainnya untuk kepentingan lain diluar Perjanjian, kecuali yang telah disepakati oleh Para Pihak berdasarkan Perjanjian ini maupun untuk keperluan lain diluar isi dari Perjanjian ini.
                        </li>
                        <li class="marginss">
                            Masing-masing Pihak tetap mempertahankan kepemilikan atas masing-masing hak kekayan intelektual yang telah ada.
                        </li>
                    </ol>
                </div>
            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 17 – ENTIRE AGREEMENT
                        </strong>
                    </p>
                    <ol>
                        <li class="margin">
                            This Agreement and the Appendices herein constitute the entire agreement between the Parties here to and shall supersede and replace any verbal or written communication heretofore made between the parties relating to the subject matter hereof.
                        </li>
                        <li class="margin">
                            All matters not provided for or not adequately provided for in this Agreement shall be settled upon mutual agreement by the Parties.
                        </li> 
                    </ol>
                </div>

                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 17 – KESELURUHAN PERJANJIAN
                        </strong>
                    </p>
                    <ol>
                        <li class="margin">
                            Perjanjian ini beserta seluruh Lampiran-lampirannya ini mewakili seluruh kesepakatan diantara Para Pihak yang berkepentingan dan membatalkan serta sebagai pengganti semua komunikasi lisan maupun tertulis yang diadakan sebelumnya antara para pihak yang berhubungan dengan permasalahan ini.
                        </li>
                        <li class="margin">
                            Hal-hal yang tidak tercakup atau tidak tercakup secara memadai dalam Perjanjian ini akan diselesaikan secara musyawarah oleh kedua Para Pihak.
                        </li> 
                    </ol>
                </div>
            </div>            
            
            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 18 – MODIFICATION
                        </strong>
                    </p>
                    <p>
                        Any modification of this Agreement or additional obligation assumed by either Party in connection with this Agreement shall be binding only if evidenced in a writing signed by each Party.
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 18 – PERUBAHAN
                        </strong>
                    </p>
                    <p>
                        Perubahan apapun terhadap Perjanjian ini atau tambahan kewajiban yang diasumsikan oleh salah satu Pihak dalam kaitannya dengan Perjanjian ini akan mengikat hanya jika dibuktikan dalam bentuk tertulis yang ditandatangani oleh masing-masing Pihak.
                    </p>
                </div>
            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 19 – NOTICES
                        </strong>
                    </p>
                    <ol>
                        <li class="margin">
                            Each Party shall designate its employees as the officials responsible for the implementation and/ or as the authorized signature for the implementation of this Agreement (<strong>"PIC"</strong>), with the PIC data as contained in paragraph 2 below. Any changes to the PIC will be delivered through a written notice which shall be considered valid if it has been signed by the competent authorized officer of each Party. 
                        </li>
                        <li class="margin">
                            <p class="margin">
                                All notices and communications required or permitted to be delivered by one Party to another Party in accordance with the provisions of this Agreement shall be in writing and delivered in person or by mail or delivery service (courier) (each should be accompanied with a receipt) or sent via facsimile to the PIC of the other Party as follows or to other address that from time to time is notified by one Party to the other Party: 
                            </p>
                            <ol type="a">
                                <li class="margin">
                                    <p class="noMargin">
                                        <strong>
                                            {{ $company['name'] ?? '' }}
                                        </strong>
                                    </p>
                                    <p class="margin">
                                        {{ $company['address'] ?? '' }}
                                    </p>
                                    <p class="noMargin">
                                        Attention : {{ $company['address'] ?? '' }}
                                    </p>
                                    <p class="noMargin">
                                        Telephone : 08568989080
                                    </p>
                                    <p class="noMargin">
                                        Email : Eddy@emc-square.com  
                                    </p>
                                </li>
                                <li class="margin">
                                    <p class="noMargin">
                                        <strong>
                                            {{ $agreementLetter->quote ? $agreementLetter->quote->customer->name : '' }}
                                        </strong>
                                    </p>
                                    <p class="margin">
                                        {{ $agreementLetter->quote ? $agreementLetter->quote->customer->address : '' }}
                                    </p>
                                    <p class="noMargin">
                                        Attention : {{ $agreementLetter->quote ? $agreementLetter->quote->customer->director : '' }}
                                    </p>
                                    <p class="noMargin">
                                        Telephone : {{ $agreementLetter->quote ? $agreementLetter->quote->customer->phone : '' }}
                                    </p>
                                    <p class="noMargin">
                                        Email : {{ $agreementLetter->quote ? $agreementLetter->quote->customer->email : '' }}
                                    </p>
                                </li>
                            </ol>
                            <p class="margin">
                                Without limiting any other means by which a Party may be able to prove that a notice has been received by another Party, a notice will be deemed to be duly received:
                            </p>
                            <ol type="a">
                                <li class="margin">
                                    if sent by hand, when received at the address of the recipient;
                                </li>
                                <li class="margin">
                                    if sent by prepaid post or courier, 5 business days (if posted within a country to an address in the same country) or 10 business days (if posted from one country to another) after the date of posting; or
                                </li>
                                <li class="margin">
                                    If sent by facsimile or e-mail, upon receipt by the sender of an acknowledgement or transmission report generated by the fax machine or e-mail page indicating that the facsimile or e-mail has been sent in its entirety to the recipient.
                                </li>
                            </ol>
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 19 – PEMBERITAHUAN
                        </strong>
                    </p>
                    <ol>
                        <li class="margin"> 
                            Masing-masing Pihak akan menunjuk karyawannya sebagai pejabat yang bertanggung-jawab atas pelaksanaan dan/ atau sebagai authorized signature  untuk pelaksanaan Perjanjian ini (<strong>“PIC”</strong>), dengan data PIC sebagaimana tercantum pada ayat 2 di bawah ini. Setiap perubahan atas PIC akan disampaikan melalui pemberitahuan tertulis yang akan dianggap berlaku jika telah ditandatangani oleh pejabat berwenang masing-masing Pihak.
                        </li>
                        <li class="margin"> 
                            <p class="margin">
                                Semua pemberitahuan dan komunikasi yang diminta atau diijinkan untuk dikirimkan oleh satu Pihak kepada Pihak lainnya sesuai dengan ketentuan dari Perjanjian ini harus tertulis, dan dikirimkan sendiri atau melalui pos atau jasa pengiriman (kurir) (masing-masing harus dengan tanda terima) atau dikirimkan melalui faksimili dialamatkan kepada PIC Pihak lainnya sebagai berikut atau kepada alamat lain yang sewaktu-waktu diberitahukan Pihak yang satu kepada Pihak lainnya:
                            </p>
                            <ol type="a">
                                <li class="margin">
                                    <p class="noMargin">
                                        <strong>
                                            {{ $company['name'] ?? '' }}
                                        </strong>
                                    </p>
                                    <p class="margin">
                                        {{ $company['address'] ?? '' }}
                                    </p>
                                    <p class="noMargin">
                                        Attention : {{ $company['address'] ?? '' }}
                                    </p>
                                    <p class="noMargin">
                                        Telephone : 08568989080
                                    </p>
                                    <p class="noMargin">
                                        Email : Eddy@emc-square.com  
                                    </p>
                                </li>
                                <li class="margin">
                                    <p class="noMargin">
                                        <strong>
                                            {{ $agreementLetter->quote ? $agreementLetter->quote->customer->name : '' }}
                                        </strong>
                                    </p>
                                    <p class="margin">
                                        {{ $agreementLetter->quote ? $agreementLetter->quote->customer->address : '' }}
                                    </p>
                                    <p class="noMargin">
                                        Attention : {{ $agreementLetter->quote ? $agreementLetter->quote->customer->director : '' }}
                                    </p>
                                    <p class="noMargin">
                                        Telephone : {{ $agreementLetter->quote ? $agreementLetter->quote->customer->phone : '' }}
                                    </p>
                                    <p class="noMargin">
                                        Email : {{ $agreementLetter->quote ? $agreementLetter->quote->customer->email : '' }}
                                    </p>
                                </li>
                            </ol>
                            <p>
                                Tanpa membatasi cara-cara lainnya yang dengan cara itu suatu Pihak dapat membuktikan bahwa pemberitahuan tersebut telah diterima oleh Pihak lain, suatu pemberitahuan akan dianggap telah diterima:
                            </p>
                            <ol>
                                <li class="margin">
                                    jika diserahkan sendiri, dan diterima di alamat penerima;
                                </li>
                                <li class="margin">
                                    jika dikirimkan melalui pos atau kurir, 5 Hari Kerja (jika dikirimkan ke alamat dalam negara yang sama) atau 10 Hari Kerja (jika dikirimkan dari satu negara ke negara lain) setelah tanggal pengiriman; atau
                                </li>
                                <li class="margin">
                                    jika dikirimkan melalui faksimili atau surel, pada saat pengirim menerima pemberitahuan atau laporan pengiriman yang dihasilkan oleh mesin faksimili atau halaman e-mail yang mengindikasikan bahwa faksimili atau e-mail telah terkirim secara lengkap kepada penerima.
                                </li>
                            </ol>
                        </li>
                    </ol>
                </div>
            </div>
             
            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 20 – SEVERABILITY
                        </strong>
                    </p>
                    <ol>
                        <li class="margin">
                            In the event of any provision or part of this Agreement prove to be invalid, void, illegal, or unenforceable, that in no way affect, impair, or invalidate any other provision, and all other provisions of this Agreement shall remain in full force and effect.
                        </li>
                        <li class="margin">
                            Invalidation of such provision referred to paragraph (1) shall not affect the validation or performance of any other provisions of this Agreement and Parties shall negotiate for replacement of such invalid provision, if necessary, stated on Addendum which are an integral and inseparable part of this Agreement.
                        </li>
                        <li class="margin">
                            In the event of entire provisions of this Agreement prove to be null and void, then it shall not void Confidentiality Article, Termination Article, Governing Law and Settlement Dispute Article, Correspondence Article, and Severability Article.
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 20 – KETERPISAHAN
                        </strong>
                        <ol>
                            <li class="margin">
                                Dalam hal suatu ketentuan atau sebagian dari Perjanjian ini terbukti tidak berlaku, batal, tidak sah atau tidak dapat dilaksanakan, hal tersebut tidak mempengaruhi, mengakibatkan atau membatalkan ketentuan-ketentuan lain dalam Perjanjian ini yang akan tetap berlaku dan mempunyai kekuatan penuh.
                            </li>
                            <li class="margin">
                                Ketidakberlakuan ketentuan tersebut sebagaimana dimaksud pada ayat (1) tidak akan mempengaruhi berlakunya atau dapat dilaksanakannya setiap ketentuan lainnya dari Perjanjian ini dan Para Pihak akan segera melakukan negosiasi untuk ketentuan pengganti, jika diperlukan, yang dituangkan pada Addendum yang menjadi bagian tak terpisahkan dari Perjanjian ini.
                            </li>
                            <li class="margin">
                                Dalam hal seluruh isi Pasal dalam Perjanjian ini tidak berlaku, batal, tidak sah atau tidak dapat dilaksanakan, maka tidak akan membatalkan Pasal Kerahasiaan, Pasal Pengakhiran Perjanjian, Pasal mengenai hukum yang berlaku dan penyelesaian perselisihan, Pasal Korespondensi dan Pasal Keterpisahan
                            </li>
                        </ol>
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        <strong>
                            ARTICLE 21 – MISCELLANEOUS
                        </strong>
                    </p>
                    {!!
                        $agreementLetter->other_term_english
                     !!}
                    <p>
                        This Agreement is made in 2 (two) copies and signed by the respective competent authorities representing the Parties on the date at the beginning of this Agreement.
                    </p>
                    <p>
                        Signature of the following page
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        <strong>
                            PASAL 21 – LAIN-LAIN
                        </strong>
                    </p>
                    {!!
                        $agreementLetter->other_term
                     !!}
                    <p>
                        Perjanjian ini dibuat dalam rangkap 2 (dua) dan ditandatangani oleh masing-masing pejabat yang berwenang mewakili Para Pihak pada tanggal tersebut pada awal Perjanjian ini.
                    </p>
                    <p>
                        Tanda tangan pada halaman berikutnya.
                    </p>
                </div>
            </div>
             
            <div class="row mt-5 mb-5">
                <div class="col-5 text-justify">
                    <p class="noMargin">
                        <strong>
                            PIHAK PERTAMA / FIRST PARTY
                        </strong>
                    </p>
                    <p class="noMargin">
                        <strong>
                            {{ $company['name'] ?? ''}}
                        </strong>
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p class="noMargin">
                        <strong>
                        PIHAK KEDUA / SECOND PARTY
                    </strong>
                    </p>
                    <p class="noMargin">
                        <strong>
                            {{ $agreementLetter->quote ? $agreementLetter->quote->customer->name : '' }}
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
                        Name / Nama: {{ $company['director'] ?? ''}}
                    </strong>
                </p>
                <p class="noMargin">
                        <strong>
                            Position/Jabatan: Director
                        </strong>
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p class="noMargin">
                        <strong>
                            Name / Nama: {{ $agreementLetter->quote ? $agreementLetter->quote->customer->director : '' }}
                        </strong>
                    </p>
                    <p class="noMargin">
                        <strong>
                            Position/Jabatan: Direktur
                        </strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 text-center mt-3"> <!-- Penambahan class text-center dan mt-3 -->
        <a href="{{ route('agreement-letter.edit',$agreementLetter->slug) }}" class="btn btn-primary"><i class="fa fa-edit"></i>Edit</a>
        <button type="button" id="downloadWorkOrder" class="btn btn-success"><i class="fa fa-file-pdf"></i> {{__('Download')}}</button>
    </div>
</div>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        updateCustomerField();

        $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih Quote'
        });

        $("#downloadWorkOrder").click(function (e) 
        { 
            e.preventDefault();
            prinsts();
            
        });

        $(".select2").on("change", updateCustomerField);
    });


    function updateCustomerField() 
    {
        // Mendapatkan nilai dari atribut data-customer
        var customerName = $(".select2").find("option:selected").data("customer");
        
        // Menampilkan nilai tersebut di elemen dengan id "customer"
        $("#customer").val(customerName);
    }
    function prinsts() 
    {
        let name = "{{ $nomorAgreementLetter }}"+"_surat_perjanjian";
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
    @media print 
    {
        #printItem
        {
            margin-left : 50px;
            margin-right : 50px;
        }
    }
   body 
   {
        font-family: Arial;
        /* font-size : 12px; */
        /* padding: 20px; */
        /* background-color: #f4f4f4; */
    }
    .container {
        /* background-color: #fff; */
        padding: 10px;
        border-radius: 5px;
    }
    .select2-selection__rendered 
    {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single 
    {
        height: 35px !important;
    }
    .select2-selection__arrow {
        height: 34px !important;
    }
    hr {
        border: 1px solid black;
        border-radius: 5px;
    }
    .select2-selection__rendered 
    {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single 
    {
        height: 35px !important;
    }
    .select2-selection__arrow {
        height: 34px !important;
    }

    /* li */
    .margin 
    {
        margin-bottom: 15px;
    }
    .noMargin 
    {
        margin-bottom: 0px;
    }
    .scrollable 
    {
      width: 100%;
      height: 650px;
      overflow: auto;
      border: 1px solid #ccc;
    }
</style>
@stop
