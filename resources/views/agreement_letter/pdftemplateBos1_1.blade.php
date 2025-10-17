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
                    <h5><strong>PERJANJIAN KERJASAMA<br>SEWA STUDIO LIVE COMMERCE</strong></h5>
                    <p class="noMargin"><strong>ANTARA</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : '[**]' }} </strong></p>
                    <p class="noMargin"><strong>DENGAN</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</strong></p>
                    <p class="noMargin"><strong>No: {{ $nomorAgreementLetter ?? '[**]' }}</strong></p>
                </div>
                <div class="offset-2 col-5 text-center">
                    <h5><strong>COLLABORATION AGREEMENT<br>LIVE COMMERCE STUDIO RENTAL</strong></h5>
                    <p class="noMargin"><strong>BETWEEN</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : '[**]' }} </strong></p>
                    <p class="noMargin"><strong>WITH</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</strong></p>
                    <p class="noMargin"><strong>No: {{ $nomorAgreementLetter ?? '[**]' }}</strong></p>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-5 text-justify">
                    <p>
                        Perjanjian Sewa Studio Live Commerce ("<strong>Perjanjian</strong>") ini ditandatangani pada tanggal {{ isset($agreementLetter->custom_fields['custom_agreement_signing_date']) ? date('d F Y', strtotime($agreementLetter->custom_fields['custom_agreement_signing_date'])) : '[**]' }}, oleh dan antara:
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        This Live Commerce Studio Rental Agreement ("<strong>Agreement</strong>") is signed on {{ isset($agreementLetter->custom_fields['custom_agreement_signing_date']) ? date('d F Y', strtotime($agreementLetter->custom_fields['custom_agreement_signing_date'])) : '[**]' }}, by and between:
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li>
                            <p style="margin-bottom: 15px;">
                                <strong>{{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : '[**]' }}</strong>, suatu perseroan terbatas yang didirikan dan berdiri secara sah berdasarkan hukum Indonesia yang berkedudukan di {{ isset($agreementLetter->custom_fields['custom_first_party_address']) ? e($agreementLetter->custom_fields['custom_first_party_address']) : '[**]' }}, dalam hal ini diwakili oleh <strong>{{ isset($agreementLetter->custom_fields['custom_first_party_represented_by']) ? e($agreementLetter->custom_fields['custom_first_party_represented_by']) : '[**]' }}</strong> dalam kapasitasnya sebagai <strong>{{ isset($agreementLetter->custom_fields['custom_first_party_position']) ? e($agreementLetter->custom_fields['custom_first_party_position']) : '[**]' }}</strong>, oleh karena itu sah bertindak untuk dan atas nama {{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : '[**]' }}, (untuk selanjutnya disebut sebagai <strong>"Pihak Pertama"</strong>);
                            </p>
                        </li>
                        <li>
                            @if(isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'company')
                            <p style="margin-bottom: 15px;">
                                <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</strong>, suatu perseroan terbatas yang didirikan dan berdiri secara sah berdasarkan hukum Indonesia yang beralamat di {{ isset($agreementLetter->custom_fields['custom_second_party_address']) ? e($agreementLetter->custom_fields['custom_second_party_address']) : '[**]' }}, dalam hal ini diwakili oleh <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_represented_by']) ? e($agreementLetter->custom_fields['custom_second_party_represented_by']) : '[**]' }}</strong> sebagai <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_position']) ? e($agreementLetter->custom_fields['custom_second_party_position']) : '[**]' }}</strong>, secara sah bertindak untuk dan atas nama {{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}, (untuk selanjutnya disebut sebagai <strong>"Pihak Kedua"</strong>).
                            </p>
                            @else
                            <p style="margin-bottom: 15px;">
                                <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</strong>, perorangan yang memiliki identitas dengan nomor {{ isset($agreementLetter->custom_fields['custom_second_party_identity_number']) ? e($agreementLetter->custom_fields['custom_second_party_identity_number']) : '[**]' }}, yang berdomisili di {{ isset($agreementLetter->custom_fields['custom_second_party_address']) ? e($agreementLetter->custom_fields['custom_second_party_address']) : '[**]' }}, mewakili dan bertanggung jawab untuk dan atas nama diri sendiri dan/atau {{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}, (untuk selanjutnya disebut sebagai <strong>"Pihak Kedua"</strong>).
                            </p>
                            @endif
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li>
                            <p style="margin-bottom: 15px;">
                                <strong>{{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : '[**]' }}</strong>, a limited liability company established and standing legally under Indonesian law, domiciled in {{ isset($agreementLetter->custom_fields['custom_first_party_address']) ? e($agreementLetter->custom_fields['custom_first_party_address']) : '[**]' }}, in this matter represented by <strong>{{ isset($agreementLetter->custom_fields['custom_first_party_represented_by']) ? e($agreementLetter->custom_fields['custom_first_party_represented_by']) : '[**]' }}</strong> in his/her capacity as <strong>{{ isset($agreementLetter->custom_fields['custom_first_party_position']) ? e($agreementLetter->custom_fields['custom_first_party_position']) : '[**]' }}</strong>, therefore legally acting for and on behalf of {{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : '[**]' }}, (hereinafter referred to as <strong>"First Party"</strong>);
                            </p>
                        </li>
                        <li>
                            @if(isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'company')
                            <p style="margin-bottom: 15px;">
                                <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</strong>, a limited liability company established and standing legally under Indonesian law, addressed at {{ isset($agreementLetter->custom_fields['custom_second_party_address']) ? e($agreementLetter->custom_fields['custom_second_party_address']) : '[**]' }}, in this matter represented by <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_represented_by']) ? e($agreementLetter->custom_fields['custom_second_party_represented_by']) : '[**]' }}</strong> as <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_position']) ? e($agreementLetter->custom_fields['custom_second_party_position']) : '[**]' }}</strong>, legally acting for and on behalf of {{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}, (hereinafter referred to as <strong>"Second Party"</strong>).
                            </p>
                            @else
                            <p style="margin-bottom: 15px;">
                                <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</strong>, an individual with identity number {{ isset($agreementLetter->custom_fields['custom_second_party_identity_number']) ? e($agreementLetter->custom_fields['custom_second_party_identity_number']) : '[**]' }}, domiciled in {{ isset($agreementLetter->custom_fields['custom_second_party_address']) ? e($agreementLetter->custom_fields['custom_second_party_address']) : '[**]' }}, representing and responsible for and on behalf of himself/herself and/or {{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}, (hereinafter referred to as <strong>"Second Party"</strong>).
                            </p>
                            @endif
                        </li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        Pihak Pertama dan Pihak Kedua selanjutnya secara bersama-sama di dalam Perjanjian ini akan disebut sebagai "Para Pihak", sedangkan masing-masing disebut "Pihak".
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        First Party and Second Party hereinafter collectively referred to as "Parties" in this Agreement, while individually referred to as "Party".
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-left">
                    <p><strong>PEMBUKAAN</strong></p>
                </div>
                <div class="offset-2 col-5 text-left">
                    <p><strong>RECITALS</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li>
                            <p>Bahwa Pihak Pertama adalah suatu perseroan terbatas yang bergerak dalam bidang {{ isset($agreementLetter->custom_fields['custom_first_party_business_field']) ? e($agreementLetter->custom_fields['custom_first_party_business_field']) : '[**]' }};</p>
                        </li>
                        <li>
                            <p>Bahwa Pihak kedua adalah suatu perseroan terbatas atau perorangan yang memiliki usaha di bidang {{ isset($agreementLetter->custom_fields['custom_second_party_business_field']) ? e($agreementLetter->custom_fields['custom_second_party_business_field']) : '[**]' }}.</p>
                        </li>
                        <li>
                            <p>Bahwa Pihak Pertama bermaksud menyewakan studio termasuk peralatan pendukung untuk keperluan live commerce ("Unit Sewa") kepada Pihak Kedua dan Pihak kedua dengan ini menyetujuinya dan Para Pihak sepakat untuk mengikatkan diri dalam sebuah Perjanjian dengan syarat-syarat dan ketentuan-ketentuan kerjasama dalam Perjanjian ini.</p>
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                     <ol>
                        <li>
                            <p>Whereas, First Party is a limited liability company engaged in {{ isset($agreementLetter->custom_fields['custom_first_party_business_field']) ? e($agreementLetter->custom_fields['custom_first_party_business_field']) : '[**]' }};</p>
                        </li>
                        <li>
                            <p>Whereas, Second Party is a limited liability company or individual having business in the field of {{ isset($agreementLetter->custom_fields['custom_second_party_business_field']) ? e($agreementLetter->custom_fields['custom_second_party_business_field']) : '[**]' }}.</p>
                        </li>
                        <li>                            
                            <p>Whereas, First Party intends to rent out studio including supporting equipment for live commerce purposes ("Rental Unit") to Second Party and Second Party hereby agrees to it and the Parties agree to bind themselves in an Agreement with the terms and conditions of cooperation in this Agreement.</p>
                        </li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                </div>
                <div class="offset-2 col-5 text-justify">
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                </div>
                <div class="offset-2 col-5 text-justify">
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>Berdasarkan hal-hal tersebut diatas, maka dengan ini Para Pihak telah setuju dan sepakat untuk membuat, menandatangani dan melaksanakan Perjanjian dengan syarat-syarat dan ketentuan-ketentuan yang diatur sebagai berikut:</p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>Based on the above matters, the Parties have agreed and consented to make, sign and execute the Agreement with the terms and conditions as follows:</p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 1<br>RUANG LINGKUP PERJANJIAN</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 1<br>SCOPE OF AGREEMENT</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li>
                            <p>Perjanjian ini mengatur syarat-syarat yang sifatnya umum, yaitu ketentuan-ketentuan penyewaan Unit Sewa, termasuk namun tidak terbatas kepada deskripsi Unit Sewa, sebagai berikut:</p>
                            <ol type="a">
                                @if(isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'company')
                                <li class="margin">Pihak Pertama akan menyediakan studio dilengkapi dengan 1 (satu) host dan 1 (satu) konten pra-live dengan standar Pihak Pertama. Pihak Kedua dapat memilih opsi tidak menggunakan host sesuai kebutuhan.</li>
                                @else
                                <li class="margin">Pihak Pertama akan menyediakan studio live streaming siap pakai, internet dedicated, ring light & peralatan standar, waktu fleksibel per jam, tidak termasuk gudang atau fulfillment.</li>
                                @endif
                                <li class="margin">Apabila Pihak Kedua membutuhkan tim laporan, maka Pihak Pertama akan menyediakan tim untuk melakukan memberikan laporan GMV (Gross Merchandise Value) dan laporan live streaming.</li>
                                @if(isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'company')
                                <li class="margin">Pihak Pertama akan menyediakan ponsel jenis Iphone tipe 10 - 13, 2 (dua) unit lighting, properti display, microphone dan meja serta kursi sesuai standar yang dimiliki oleh Pihak Pertama.</li>
                                <li class="margin">Pihak Pertama akan menyediakan gudang penyimpanan produk milik Pihak Kedua apabila diperlukan dan Pihak Kedua wajib membayar biaya tambahan.</li>
                                @endif
                            </ol>
                        </li>
                        <li class="margin">Pihak Kedua akan mendapatkan jadwal setiap hari {{ isset($agreementLetter->custom_fields['custom_schedule_days']) ? e($agreementLetter->custom_fields['custom_schedule_days']) : '[**]' }} pukul {{ isset($agreementLetter->custom_fields['custom_schedule_start_time']) ? e($agreementLetter->custom_fields['custom_schedule_start_time']) : '[**]' }} sampai dengan pukul {{ isset($agreementLetter->custom_fields['custom_schedule_end_time']) ? e($agreementLetter->custom_fields['custom_schedule_end_time']) : '[**]' }} WIB.</li>
                        <li class="margin">Pihak Kedua akan mendapatkan jadwal live maksimal 2 (dua) jam yang akan dilaksanakan pada hari yang telah disepakati oleh Para Pihak secara terpisah.</li>
                        <li class="margin">Pihak Pertama sepakat untuk memberikan kepada Pihak kedua, informasi yang diperlukan untuk melaksanakan Perjanjian ini.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li>
                            <p>This Agreement regulates general terms and conditions, namely the provisions for renting the Rental Unit, including but not limited to the description of the Rental Unit, as follows:</p>
                            <ol type="a">
                                @if(isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'company')
                                <li class="margin">First Party will provide studio equipped with 1 (one) host and 1 (one) pre-live content with First Party's standard. Second Party may choose the option not to use a host according to needs.</li>
                                @else
                                <li class="margin">First Party will provide ready-to-use live streaming studio, dedicated internet, ring light & standard equipment, flexible time per hour, excluding warehouse or fulfillment.</li>
                                @endif
                                <li class="margin">If Second Party requires a reporting team, First Party will provide a team to provide GMV (Gross Merchandise Value) reports and live streaming reports.</li>
                                @if(isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'company')
                                <li class="margin">First Party will provide iPhone type 10 - 13, 2 (two) lighting units, display properties, microphone and table and chairs according to the standards owned by First Party.</li>
                                <li class="margin">First Party will provide warehouse storage for Second Party's products if necessary and Second Party is obliged to pay additional fees.</li>
                                @endif
                            </ol>
                        </li>
                        <li class="margin">Second Party will get a schedule every {{ isset($agreementLetter->custom_fields['custom_schedule_days']) ? e($agreementLetter->custom_fields['custom_schedule_days']) : '[**]' }} at {{ isset($agreementLetter->custom_fields['custom_schedule_start_time']) ? e($agreementLetter->custom_fields['custom_schedule_start_time']) : '[**]' }} until {{ isset($agreementLetter->custom_fields['custom_schedule_end_time']) ? e($agreementLetter->custom_fields['custom_schedule_end_time']) : '[**]' }} WIB.</li>
                        <li class="margin">Second Party will get a maximum live schedule of 2 (two) hours which will be carried out on the day agreed upon by the Parties separately.</li>
                        <li class="margin">First Party agrees to provide Second Party with the information necessary to carry out this Agreement.</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 2<br>JANGKA WAKTU</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 2<br>TERM OF AGREEMENT</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">Perjanjian ini mulai berlaku pada dan sejak tanggal {{ isset($agreementLetter->custom_fields['custom_agreement_start_date']) ? date('d F Y', strtotime($agreementLetter->custom_fields['custom_agreement_start_date'])) : '[**]' }} sampai dengan {{ isset($agreementLetter->custom_fields['custom_agreement_end_date']) ? date('d F Y', strtotime($agreementLetter->custom_fields['custom_agreement_end_date'])) : '[**]' }} dan/atau sampai para pihak menyelesaikan kewajibannya masing-masing dan para pihak telah memperoleh masing-masing haknya ("<strong>Jangka Waktu</strong>").</li>
                        <li class="margin">Apabila Pihak Kedua akan perpanjangan sewa, maka Pihak Kedua akan memberitahu Pihak Pertama paling lambat 14 (empat belas) hari sebelum Jangka Waktu berakhir.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">This Agreement shall be effective from and as of {{ isset($agreementLetter->custom_fields['custom_agreement_start_date']) ? date('d F Y', strtotime($agreementLetter->custom_fields['custom_agreement_start_date'])) : '[**]' }} until {{ isset($agreementLetter->custom_fields['custom_agreement_end_date']) ? date('d F Y', strtotime($agreementLetter->custom_fields['custom_agreement_end_date'])) : '[**]' }} and/or until the parties complete their respective obligations and the parties have obtained their respective rights ("<strong>Term</strong>").</li>
                        <li class="margin">If Second Party will extend the rental, Second Party shall notify First Party no later than 14 (fourteen) days before the Term expires.</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 3<br>BIAYA DAN TATA CARA PEMBAYARAN</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 3<br>FEES AND PAYMENT PROCEDURES</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        @if(isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'company')
                        <li class="margin">Pihak Kedua akan membayarkan biaya Unit Sewa sebesar Rp.{{ isset($agreementLetter->custom_fields['custom_rental_fee_amount']) ? number_format($agreementLetter->custom_fields['custom_rental_fee_amount'], 0, ',', '.') : '[**]' }},- ({{ isset($agreementLetter->custom_fields['custom_rental_fee_words']) ? e($agreementLetter->custom_fields['custom_rental_fee_words']) : '[**]' }} Rupiah) per jam / Rp.3.000.000,- (tiga juta Rupiah) per bulan belum termasuk pajak ("<strong>Biaya Sewa</strong>").</li>
                        @else
                        <li class="margin">Pihak Kedua akan membayarkan biaya Unit Sewa sebesar Rp.{{ isset($agreementLetter->custom_fields['custom_rental_fee_amount']) ? number_format($agreementLetter->custom_fields['custom_rental_fee_amount'], 0, ',', '.') : '[**]' }},- ({{ isset($agreementLetter->custom_fields['custom_rental_fee_words']) ? e($agreementLetter->custom_fields['custom_rental_fee_words']) : '[**]' }} Rupiah) per jam belum termasuk pajak ("<strong>Biaya Sewa</strong>").</li>
                        @endif
                        <li>
                            <p>
                                Pihak Kedua wajib melakukan pembayaran dengan skema pembayaran sebagai berikut:
                            </p>
                            <ol type="a">  
                                <li>
                                    <p>
                                        Pembayaran uang muka sebesar 50% (lima puluh) persen dari total Biaya Sewa wajib dibayarkan saat Perjanjian ditandatangani.
                                    </p>
                                </li>
                                <li>
                                    <p>
                                        Pembayaran pelunasan sebesar 50% (lima puluh) persen dari total Biaya Sewa wajib dibayarkan paling lambat 1 (hari) sebelum Jangka Waktu efektif berlaku.
                                    </p>
                                </li>
                            </ol>
                            <p>
                                Pihak Kedua wajib melakukan pembayaran penuh paling lambat 2 (dua) hari sebelum Unit Sewa digunakan
                            </p>
                        </li>
                        <li class="margin">Pihak Kedua wajib membayar deposit sebesar Rp 500.000,- ("<strong>Deposit</strong>") yang wajib dibayarkan paling lambat sebelum Jangka Waktu berlaku. Deposit akan dikembalikan setelah Jangka Waktu sewa selesai, dengan ketentuan bahwa tidak ada kerusakan dan/atau pelanggaran ketentuan sewa.</li>
                        <li class="margin">Para Pihak sepakat bahwa Pihak Pertama akan mendapatkan persentase atas produk yang dijual sebesar {{ isset($agreementLetter->custom_fields['custom_commission_percentage']) ? e($agreementLetter->custom_fields['custom_commission_percentage']) : '[**]' }}% atau Rp {{ isset($agreementLetter->custom_fields['custom_fee_per_product']) ? number_format($agreementLetter->custom_fields['custom_fee_per_product'], 0, ',', '.') : '[**]' }} per produk. Persentase akan dibayarkan oleh Pihak Kedua paling lambat tanggal {{ isset($agreementLetter->custom_fields['custom_commission_payment_date']) ? e($agreementLetter->custom_fields['custom_commission_payment_date']) : '[**]' }} pada bulan berjalan.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        @if(isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'company')
                        <li class="margin">Second Party shall pay the Rental Fee of Rp.{{ isset($agreementLetter->custom_fields['custom_rental_fee_amount']) ? number_format($agreementLetter->custom_fields['custom_rental_fee_amount'], 0, ',', '.') : '[**]' }},- ({{ isset($agreementLetter->custom_fields['custom_rental_fee_words']) ? e($agreementLetter->custom_fields['custom_rental_fee_words']) : '[**]' }} Rupiah) per hour excluding tax ("<strong>Rental Fee</strong>"). The Rental Fee will be based on the number of hours used by Second Party, and will be charged at the rate of Rp.3.000.000,- (three billion Rupiah) per month.</li>
                        @else
                        <li class="margin">Second Party shall pay the Rental Unit fee of Rp.{{ isset($agreementLetter->custom_fields['custom_rental_fee_amount']) ? number_format($agreementLetter->custom_fields['custom_rental_fee_amount'], 0, ',', '.') : '[**]' }},- ({{ isset($agreementLetter->custom_fields['custom_rental_fee_words']) ? e($agreementLetter->custom_fields['custom_rental_fee_words']) : '[**]' }} Rupiah) per hour excluding tax ("<strong>Rental Fee</strong>").</li>
                        @endif
                        <li>
                            <p>
                                Pihak Kedua wajib melakukan pembayaran dengan skema pembayaran sebagai berikut:
                            </p>
                            <ol type="a">  
                                <li>
                                    <p>
                                        Initial payment of 50% of the total Rental Fee will be made when the Agreement is signed.
                                    </p>
                                </li>
                                <li>
                                    <p>
                                        The remaining 50% of the total Rental Fee will be paid 1 (one) day before the Effective Date of the rental period.
                                    </p>
                                </li>
                            </ol>
                            <p>
                                Second Party is obligated to make full payment no later than 2 (two) days before the Rental Unit is used.
                            </p>
                        </li>
                        <li class="margin">Second Party is obligated to pay a deposit of Rp 500,000,- ("<strong>Deposit</strong>") which must be paid no later than before the Term takes effect. The Deposit will be returned after the rental Term is completed, provided that there is no damage and/or violation of rental provisions.</li>
                        <li class="margin">The Parties agree that First Party will receive a percentage of products sold amounting to {{ isset($agreementLetter->custom_fields['custom_commission_percentage']) ? e($agreementLetter->custom_fields['custom_commission_percentage']) : '[**]' }}% or Rp {{ isset($agreementLetter->custom_fields['custom_fee_per_product']) ? number_format($agreementLetter->custom_fields['custom_fee_per_product'], 0, ',', '.') : '[**]' }} per product. The percentage shall be paid by Second Party no later than the {{ isset($agreementLetter->custom_fields['custom_commission_payment_date']) ? e($agreementLetter->custom_fields['custom_commission_payment_date']) : '[**]' }} of the current month.</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol start="5">
                        <li class="margin">Pembayaran Pasal 3.2, 3.3 dan 3.4 akan dilakukan dengan cara transfer ke rekening Pihak Pertama dengan data sebagai berikut:
                            <table style="margin-left: 20px; margin-top: 10px;">
                                <tr>
                                    <td style="width: 150px;">Nama Pemilik Rekening</td>
                                    <td style="width: 20px;">:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_account_holder_name']) ? e($agreementLetter->custom_fields['custom_account_holder_name']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Nama Bank</td>
                                    <td>
                                        :
                                    </td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_bank_name']) ? e($agreementLetter->custom_fields['custom_bank_name']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Kantor Cabang</td>
                                    <td>
                                        :
                                    </td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_branch_office']) ? e($agreementLetter->custom_fields['custom_branch_office']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Nomor Rekening</td>
                                    <td>
                                        :
                                    </td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_account_number']) ? e($agreementLetter->custom_fields['custom_account_number']) : '[**]' }}</td>
                                </tr>
                            </table>
                        </li>
                        <li class="margin">Biaya yang tercantum pada Pasal 3.1 belum termasuk biaya pengiriman produk termasuk jasa handling dan proses kirim ke logistik dengan tambahan biaya Rp {{ isset($agreementLetter->custom_fields['custom_handling_fee_per_product']) ? number_format($agreementLetter->custom_fields['custom_handling_fee_per_product'], 0, ',', '.') : '[**]' }} per produk.</li>
                        <li class="margin">Biaya yang tercantum pada Pasal 3.1 belum termasuk biaya penyimpanan barang, barang yang dijual dapat dititipkan di gudang studio dengan biaya Rp {{ isset($agreementLetter->custom_fields['custom_warehouse_fee']) ? number_format($agreementLetter->custom_fields['custom_warehouse_fee'], 0, ',', '.') : '[**]' }} untuk tiap ukuran {{ isset($agreementLetter->custom_fields['custom_warehouse_size']) ? e($agreementLetter->custom_fields['custom_warehouse_size']) : '[**]' }} m3.</li>
                        <li class="margin">Segala hak dan kewajiban pajak serta segala pungutan dan bea apapun, baik yang dikenakan oleh pemerintah pusat maupun oleh pemerintah daerah yang wajib dipenuhi sebagai akibat dari atau sehubungan dengan pembuatan dan pelaksanaan Perjanjian ini menjadi tanggungan dan harus dipenuhi oleh masing-masing Pihak sesuai dengan peraturan perundang-undangan perpajakan yang berlaku di Indonesia berikut dengan perubahan-perubahan dan/atau penambahannya.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol start="5">
                        <li class="margin">Payment of Article 3.2, 3.3 and 3.4 shall be made by transfer to First Party's account with the following data:
                            <table style="margin-left: 20px; margin-top: 10px;">
                                <tr>
                                    <td style="width: 150px;">Name of Account Holder</td>
                                    <td style="width: 20px;">
                                        :
                                    </td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_account_holder_name']) ? e($agreementLetter->custom_fields['custom_account_holder_name']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Name of Bank</td>
                                    <td>
                                        :
                                    </td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_bank_name']) ? e($agreementLetter->custom_fields['custom_bank_name']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Branch Office</td>
                                    <td>
                                        :
                                    </td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_branch_office']) ? e($agreementLetter->custom_fields['custom_branch_office']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Account Number</td>
                                    <td>
                                        :
                                    </td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_account_number']) ? e($agreementLetter->custom_fields['custom_account_number']) : '[**]' }}</td>
                                </tr>
                            </table>
                        </li>
                        <li class="margin">The fees stated in Article 3.1 do not include product shipping costs including handling services and shipping process to logistics with an additional fee of Rp {{ isset($agreementLetter->custom_fields['custom_handling_fee_per_product']) ? number_format($agreementLetter->custom_fields['custom_handling_fee_per_product'], 0, ',', '.') : '[**]' }} per product.</li>
                        <li class="margin">The fees stated in Article 3.1 do not include goods storage costs, goods sold can be deposited in the studio warehouse at a cost of Rp {{ isset($agreementLetter->custom_fields['custom_warehouse_fee']) ? number_format($agreementLetter->custom_fields['custom_warehouse_fee'], 0, ',', '.') : '[**]' }} for each size of {{ isset($agreementLetter->custom_fields['custom_warehouse_size']) ? e($agreementLetter->custom_fields['custom_warehouse_size']) : '[**]' }} m3.</li>
                        <li class="margin">All tax rights and obligations as well as all levies and duties of any kind, whether imposed by the central government or by local government that must be fulfilled as a result of or in connection with the making and implementation of this Agreement shall be the responsibility of and must be fulfilled by each Party in accordance with applicable tax laws in Indonesia along with its amendments and/or additions.</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 4<br>HAK DAN KEWAJIBAN</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 4<br>RIGHTS AND OBLIGATIONS</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">Hak dan Kewajiban Pihak Pertama adalah sebagai berikut:
                            <ol type="a">
                                <li class="margin">Berhak menerima Biaya Sewa dari Pihak Kedua.</li>
                                <li class="margin">Berhak menggunakan gudang penyimpanan produk milik Pihak Kedua.</li>
                                <li class="margin">Berhak untuk menolak pergantian jadwal apabila Pihak Kedua menginfokan pergantian jadwal pada h-1 sebelum Unit Sewa digunakan.</li>
                                <li class="margin">Berhak menolak kondisi produk yang mudah terbakar, mengandung bahan berbahaya dan/atau melanggar hukum apabila produk akan ditempatkan di gudang Pihak Pertama.</li>
                                <li class="margin">Wajib menyediakan Unit Sewa yang layak untuk dapat digunakan oleh Pihak Kedua.</li>
                                <li class="margin">Wajib memberikan slot Unit Sewa sesuai jadwal yang telah disepakati Para Pihak.</li>
                                <li class="margin">Akan memberikan laporan GMV (Gross Merchandise Value) dan laporan live streaming pada akhir bulan setiap bulan berjalan.</li>
                            </ol>
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">Rights and Obligations of First Party are as follows:
                            <ol type="a">
                                <li class="margin">Entitled to receive Rental Fee from Second Party.</li>
                                <li class="margin">Entitled to use warehouse storage for Second Party's products.</li>
                                <li class="margin">Entitled to reject schedule changes if Second Party informs of schedule changes on h-1 before the Rental Unit is used.</li>
                                <li class="margin">Entitled to reject product conditions that are flammable, contain hazardous materials and/or violate the law if products are to be placed in First Party's warehouse.</li>
                                <li class="margin">Obligated to provide a proper Rental Unit that can be used by Second Party.</li>
                                <li class="margin">Obligated to provide Rental Unit slots according to the schedule agreed upon by the Parties.</li>
                                <li class="margin">Will provide GMV (Gross Merchandise Value) reports and live streaming reports at the end of each current month.</li>
                            </ol>
                        </li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol start="2">
                        <li class="margin">Hak dan Kewajiban Pihak Kedua adalah sebagai berikut:
                            <ol type="a">
                                <li class="margin">Berhak mendapatkan Unit Sewa sesuai jadwal yang telah disepakati Para Pihak secara terpisah.</li>
                                <li class="margin">Berhak mendapatkan Unit pengganti apabila terjadi kendala teknis seperti bluescreen atau ponsel tidak dapat berfungsi dengan semestinya.</li>
                                <li class="margin">Wajib menyediakan contoh produk yang dapat digunakan sebagai contoh produk saat live pada Unit Sewa.</li>
                                <li class="margin">Wajib mendukung pengiriman produk milik Pihak Kedua dan seluruh biaya pengiriman menjadi tanggung jawab Pihak Kedua.</li>
                                <li class="margin">Wajib menyediakan Unit Sewa sesuai dengan jadwal yang telah disepakati secara terpisah oleh Para Pihak.</li>
                                <li class="margin">Wajib membayarkan Biaya Sewa dan Deposit berdasarkan kesepakatan dalam Perjanjian ini dengan skema yang tercantum pada Pasal 3.</li>
                                <li class="margin">Wajib menjaga Unit Sewa yang disewakan dengan baik serta dilarang mengalihkan, memindahtangankan, atau menyewakan kembali Unit Sewa tersebut kepada pihak ketiga tanpa persetujuan tertulis dari Pihak Pertama.</li>
                                <li class="margin">Wajib mengembalikan Unit sesuai jumlah dan kondisi yang sama sesuai di awal Perjanjian.</li>
                                <li class="margin">Wajib membebaskan Pihak Pertama dari segala bentuk tanggung jawab, dampak, dan ganti rugi akibat gugatan dan/atau tuntutan akibat dari segala tindakan dan/atau pelanggaran yang dilakukan oleh Pihak Kedua, serta membersihkan nama baik Pihak Pertama.</li>
                            </ol>
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol start="2">
                        <li class="margin">Rights and Obligations of Second Party are as follows:
                            <ol type="a">
                                <li class="margin">Entitled to get Rental Unit according to the schedule agreed upon by the Parties separately.</li>
                                <li class="margin">Entitled to get a replacement Unit if there are technical problems such as bluescreen or the phone cannot function properly.</li>
                                <li class="margin">Obligated to provide product samples that can be used as product examples during live on the Rental Unit.</li>
                                <li class="margin">Obligated to support the delivery of Second Party's products and all shipping costs are the responsibility of Second Party.</li>
                                <li class="margin">Obligated to provide Rental Unit according to the schedule agreed upon separately by the Parties.</li>
                                <li class="margin">Obligated to pay Rental Fee and Deposit based on the agreement in this Agreement with the scheme stated in Article 3.</li>
                                <li class="margin">Obligated to maintain the rented Rental Unit properly and is prohibited from transferring, transferring, or re-renting the Rental Unit to third parties without written approval from First Party.</li>
                                <li class="margin">Obligated to return the Unit according to the same number and condition as at the beginning of the Agreement.</li>
                                <li class="margin">Obligated to release First Party from all forms of responsibility, impact, and compensation due to lawsuits and/or demands resulting from all actions and/or violations committed by Second Party, as well as clearing First Party's good name.</li>
                            </ol>
                        </li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 5<br>OVERTIME DAN PENAMBAHAN FASILITAS</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 5<br>OVERTIME AND ADDITIONAL FACILITIES</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">Apabila Pihak Kedua menggunakan Unit Sewa melebihi waktu yang telah ditentukan, maka akan dikenakan biaya overtime sebesar Rp {{ isset($agreementLetter->custom_fields['custom_overtime_fee_per_hour']) ? number_format($agreementLetter->custom_fields['custom_overtime_fee_per_hour'], 0, ',', '.') : '[**]' }} per jam.</li>
                        <li class="margin">Penambahan fasilitas di luar standar Unit Sewa yang disediakan dapat dilakukan sesuai permintaan Pihak Kedua dengan biaya tambahan yang akan disepakati kemudian oleh Para Pihak.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">If Second Party uses the Rental Unit beyond the specified time, an overtime fee of Rp {{ isset($agreementLetter->custom_fields['custom_overtime_fee_per_hour']) ? number_format($agreementLetter->custom_fields['custom_overtime_fee_per_hour'], 0, ',', '.') : '[**]' }} per hour will be charged.</li>
                        <li class="margin">Addition of facilities outside the standard Rental Unit provided can be done according to Second Party's request with additional fees to be agreed upon later by the Parties.</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 6<br>KERAHASIAAN</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 6<br>CONFIDENTIALITY</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>Para Pihak wajib menjaga dan menyimpan segala informasi, keterangan, dan data lainnya yang diperoleh dalam rangka pelaksanaan Perjanjian ini sebagai rahasia yang tidak boleh diberitahukan kepada pihak-pihak manapun yang tidak berhak, baik yang berupa badan hukum maupun perseorangan, kecuali:</p>
                    <ol type="a">
                        <li class="margin">Kepada instansi pemerintah yang berwenang mengatur atau mengeluarkan izin tentang hal-hal yang diperjanjikan dalam Perjanjian ini;</li>
                        <li class="margin">Diperintahkan oleh badan peradilan atau instansi Pemerintah lainnya yang berhubungan dengan penegakkan hukum secara tertulis, resmi, dan merupakan putusan final; dan</li>
                        <li class="margin">Menurut peraturan perundang-undangan yang berlaku di Indonesia, informasi dan/atau keterangan tersebut harus diberikan kepada pihak lain yang disebutkan dengan jelas dalam peraturan perundang-undangan tersebut.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>The Parties are obligated to maintain and keep all information, details, and other data obtained in the context of implementing this Agreement as confidential which may not be disclosed to any unauthorized parties, whether legal entities or individuals, except:</p>
                    <ol type="a">
                        <li class="margin">To government agencies authorized to regulate or issue permits regarding matters agreed upon in this Agreement;</li>
                        <li class="margin">Ordered by a judicial body or other Government agency related to law enforcement in writing, officially, and constitutes a final decision; and</li>
                        <li class="margin">According to laws and regulations applicable in Indonesia, such information and/or details must be provided to other parties clearly stated in such laws and regulations.</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 7<br>PERNYATAAN, JAMINAN DAN LARANGAN</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 7<br>REPRESENTATIONS, WARRANTIES AND PROHIBITIONS</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">Para Pihak adalah subjek hukum yang sah berdasarkan hukum Negara Republik Indonesia dan berwenang untuk membuat Perjanjian ini.</li>
                        <li class="margin">Pihak Kedua dengan ini menyetujui dan menyanggupi untuk melaksanakan kewajibannya berdasarkan Perjanjian ini.</li>
                        <li class="margin">Pihak Kedua dilarang untuk vape dan merokok di dalam Unit Sewa, membawa makanan dan minuman yang dapat merusak atau mengotori barang dan/atau Unit Sewa milik Pihak Pertama.</li>
                        <li class="margin">Pihak Kedua menjamin bahwa apabila timbul gugatan dan/atau tuntutan akibat dari segala tindakan dan/atau pelanggaran yang dilakukan oleh Pihak Kedua, maka Pihak Kedua membebaskan Pihak Pertama dari segala bentuk tanggung jawab, dampak, dan ganti rugi akibat gugatan dan/atau tuntutan tersebut.</li>
                        <li class="margin">Pihak Kedua menjamin tidak akan merusak barang, tidak akan memindahkan alat tanpa izin, atau menggunakan studio untuk kegiatan live streaming yang mengandung unsur judi, pornografi, SARA, maupun pelanggaran hukum lainnya.</li>
                        <li class="margin">Pihak Kedua menyatakan akan bertanggung jawab atas kerusakan pada peralatan termasuk studio.</li>
                        <li class="margin">Pihak Kedua menyatakan dan menjamin bahwa semua data atau informasi yang disampaikan kepada Pihak Pertama merupakan informasi yang benar.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">The Parties are legal subjects under the laws of the Republic of Indonesia and are authorized to make this Agreement.</li>
                        <li class="margin">Second Party hereby agrees and undertakes to carry out its obligations under this Agreement.</li>
                        <li class="margin">Second Party is prohibited from vaping and smoking inside the Rental Unit, bringing food and beverages that can damage or dirty the goods and/or Rental Unit belonging to First Party.</li>
                        <li class="margin">Second Party warrants that if lawsuits and/or claims arise as a result of all actions and/or violations committed by Second Party, then Second Party releases First Party from all forms of responsibility, impact, and compensation due to such lawsuits and/or claims.</li>
                        <li class="margin">Second Party warrants that it will not damage goods, will not move equipment without permission, or use the studio for live streaming activities containing elements of gambling, pornography, SARA, or other legal violations.</li>
                        <li class="margin">Second Party states that it will be responsible for damage to equipment including the studio.</li>
                        <li class="margin">Second Party represents and warrants that all data or information submitted to First Party is correct information.</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 8<br>DENDA DAN GANTI RUGI</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 8<br>PENALTIES AND COMPENSATION</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">Pihak Kedua wajib membayar Biaya Sewa secara tepat waktu, apabila terjadi keterlambatan pembayaran, maka Pihak Kedua akan dikenakan denda sebesar Rp {{ isset($agreementLetter->custom_fields['custom_late_payment_penalty']) ? number_format($agreementLetter->custom_fields['custom_late_payment_penalty'], 0, ',', '.') : '[**]' }}.</li>
                        <li class="margin">Apabila Pihak Kedua melakukan keterlambatan pembayaran pelunasan, maka akan dikenakan denda keterlambatan sebesar {{ isset($agreementLetter->custom_fields['custom_penalty_percentage']) ? e($agreementLetter->custom_fields['custom_penalty_percentage']) : '[**]' }}% dari tagihan Biaya Sewa, terhitung sejak hari pertama keterlambatan pembayaran.</li>
                        <li class="margin">Apabila Pihak Kedua melakukan kelalaian dan/atau kesalahan yang mengakibatkan kerusakan Unit Sewa, maka Pihak Kedua wajib membayar denda sesuai dengan harga Unit Sewa pada saat kejadian.</li>
                        <li class="margin">Apabila Unit Sewa yang disewakan hilang dalam Jangka Waktu sewa, maka penyewa berkewajiban mengganti sesuai dengan harga pasar Unit Sewa pada saat kejadian, termasuk aksesoris pendukung yang turut hilang.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">Second Party is obligated to pay the Rental Fee on time, if payment is late, Second Party will be subject to a penalty of Rp {{ isset($agreementLetter->custom_fields['custom_late_payment_penalty']) ? number_format($agreementLetter->custom_fields['custom_late_payment_penalty'], 0, ',', '.') : '[**]' }}.</li>
                        <li class="margin">If Second Party delays payment of the settlement, a late penalty of {{ isset($agreementLetter->custom_fields['custom_penalty_percentage']) ? e($agreementLetter->custom_fields['custom_penalty_percentage']) : '[**]' }}% of the Rental Fee bill will be charged, calculated from the first day of payment delay.</li>
                        <li class="margin">If Second Party commits negligence and/or errors resulting in damage to the Rental Unit, Second Party is obligated to pay a penalty according to the price of the Rental Unit at the time of the incident.</li>
                        <li class="margin">If the rented Rental Unit is lost during the rental Term, the tenant is obligated to replace it according to the market price of the Rental Unit at the time of the incident, including supporting accessories that are also lost.</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 9<br>PENGAKHIRAN PERJANJIAN</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 9<br>TERMINATION OF AGREEMENT</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">Pihak Pertama dapat memutuskan Perjanjian ini tanpa perlu pemberitahuan kepada Pihak Kedua, apabila:
                            <ol type="a">
                                <li class="margin">Pihak Kedua melanggar syarat-syarat dan/atau ketentuan-ketentuan dalam Perjanjian ini; dan</li>
                                <li class="margin">Terjadi pelanggaran hukum yang dilakukan oleh Pihak Kedua yang mengakibatkan Pihak Kedua tidak dapat melaksanakan kewajibannya berdasarkan Perjanjian ini.</li>
                            </ol>
                        </li>
                        <li class="margin">Apabila Pihak Kedua memutuskan mengakhiri Perjanjian ini lebih awal, maka Pihak Kedua wajib membayar denda membayarkan 2 (dua) kali lipat dari Biaya Sewa yang telah ditentukan di Perjanjian ini.</li>
                        <li class="margin">Para Pihak setuju dan sepakat untuk mengesampingkan berlakunya ketentuan Pasal 1266 Kitab Undang-Undang Hukum Perdata terhadap Perjanjian ini dalam hal diperlukan suatu putusan pengadilan untuk mengakhiri Perjanjian ini.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">First Party may terminate this Agreement without notice to Second Party, if:
                            <ol type="a">
                                <li class="margin">Second Party violates the terms and/or provisions in this Agreement; and</li>
                                <li class="margin">A legal violation occurs committed by Second Party which results in Second Party being unable to carry out its obligations under this Agreement.</li>
                            </ol>
                        </li>
                        <li class="margin">If Second Party decides to terminate this Agreement early, Second Party is obligated to pay a penalty of 2 (two) times the Rental Fee specified in this Agreement.</li>
                        <li class="margin">The Parties agree to set aside the application of the provisions of Article 1266 of the Civil Code to this Agreement in the event that a court decision is required to terminate this Agreement.</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 10<br>FORCE MAJEURE</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 10<br>FORCE MAJEURE</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">Yang dimaksud dengan force majeure dalam Perjanjian ini adalah peristiwa yang terjadi di luar kemampuan Para Pihak untuk mengatasinya, dan bukan disebabkan karena kesalahan ataupun kelalaian Para Pihak, seperti antara lain, bencana alam, kebakaran, peperangan, huru hara, pemberontakan, wabah, epidemi, pandemi, sabotase, dan tindakan pemerintah di bidang moneter, yang secara langsung mengganggu pelaksanaan kewajiban Para Pihak dalam Perjanjian ini dan dinyatakan oleh Pemerintah sebagai force majeure.</li>
                        <li class="margin">Apabila terjadi force majeure sebagaimana di maksud Pasal 10.1 di atas, maka Pihak yang berada dalam keadaan memaksa berkewajiban memberitahu kan Pihak lainnya dalam waktu selambat-lambatnya 7 (tujuh) hari kalender.</li>
                        <li class="margin">Force majeure sebagaimana dimaksud dalam Pasal ini tidak menghapuskan atau mengakhiri Perjanjian ini serta Para Pihak wajib menyelesaikan kewajibannya masing-masing.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">Force majeure in this Agreement means events that occur beyond the ability of the Parties to overcome, and are not caused by errors or negligence of the Parties, such as natural disasters, fires, wars, riots, rebellions, epidemics, pandemics, sabotage, and government actions in the monetary field, which directly interfere with the implementation of the obligations of the Parties in this Agreement and are declared by the Government as force majeure.</li>
                        <li class="margin">If force majeure occurs as referred to in Article 10.1 above, the Party in a state of force majeure is obligated to notify the other Party no later than 7 (seven) calendar days.</li>
                        <li class="margin">Force majeure as referred to in this Article does not eliminate or terminate this Agreement and the Parties are obligated to complete their respective obligations.</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 11<br>KORESPONDENSI</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 11<br>CORRESPONDENCE</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>Setiap pemberitahuan dan komunikasi yang dibuat berdasarkan Perjanjian ini harus dibuat secara tertulis dan memberitahukan kepada masing-masing Pihak dengan alamat sebagai berikut:</p>
                    <ol type="a">
                        <li class="margin">
                            <table style="margin-left: 20px; margin-top: 10px;">
                                <tbody>
                                    <tr>
                                        <td style="width: 150px;">Nama</td>
                                        <td style="width: 20px;">
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : '[**]' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Alamat</td>
                                        <td>
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_first_party_address']) ? e($agreementLetter->custom_fields['custom_first_party_address']) : '[**]' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Telephone</td>
                                        <td>
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_first_party_phone']) ? e($agreementLetter->custom_fields['custom_first_party_phone']) : '[**]' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td>
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_first_party_email']) ? e($agreementLetter->custom_fields['custom_first_party_email']) : '[**]' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Up</td>
                                        <td>
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_first_party_attention']) ? e($agreementLetter->custom_fields['custom_first_party_attention']) : '[**]' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </li>
                        <li class="margin">
                            <table style="margin-left: 20px; margin-top: 10px;">
                                <tbody>
                                    <tr>
                                        <td style="width: 150px;">Nama</td>
                                        <td style="width: 20px;">
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Alamat</td>
                                        <td>
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_second_party_address']) ? e($agreementLetter->custom_fields['custom_second_party_address']) : '[**]' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Telephone</td>
                                        <td>
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_second_party_phone']) ? e($agreementLetter->custom_fields['custom_second_party_phone']) : '[**]' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td>
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_second_party_email']) ? e($agreementLetter->custom_fields['custom_second_party_email']) : '[**]' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Up</td>
                                        <td>
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_second_party_attention']) ? e($agreementLetter->custom_fields['custom_second_party_attention']) : '[**]' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </li>
                    </ol>
                    <p>Atau kepada alamat lain atau nomor lain sebagaimana diberitahukan dari waktu ke waktu oleh masing-masing Pihak kepada Pihak lainnya dengan cara sebagaimana disebutkan di atas.</p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>Every notice and communication made under this Agreement must be made in writing and notified to each Party at the following address:</p>
                    <ol type="a">
                        <li class="margin">
                            <table style="margin-left: 20px; margin-top: 10px;">
                                <tbody>
                                    <tr>
                                        <td style="width: 150px;">Name</td>
                                        <td style="width: 20px;">
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : '[**]' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Address</td>
                                        <td>
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_first_party_address']) ? e($agreementLetter->custom_fields['custom_first_party_address']) : '[**]' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Telephone</td>
                                        <td>
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_first_party_phone']) ? e($agreementLetter->custom_fields['custom_first_party_phone']) : '[**]' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td>
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_first_party_email']) ? e($agreementLetter->custom_fields['custom_first_party_email']) : '[**]' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Attention</td>
                                        <td>
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_first_party_attention']) ? e($agreementLetter->custom_fields['custom_first_party_attention']) : '[**]' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </li>
                        <li class="margin">
                            <table style="margin-left: 20px; margin-top: 10px;">
                                <tbody>
                                    <tr style="width: 150px;">
                                        <td style="width: 150px;">Name</td>
                                        <td style="width: 20px;">
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Address</td>
                                        <td>
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_second_party_address']) ? e($agreementLetter->custom_fields['custom_second_party_address']) : '[**]' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Telephone</td>
                                        <td>
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_second_party_phone']) ? e($agreementLetter->custom_fields['custom_second_party_phone']) : '[**]' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td>
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_second_party_email']) ? e($agreementLetter->custom_fields['custom_second_party_email']) : '[**]' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Attention</td>
                                        <td>
                                            :
                                        </td>
                                        <td>{{ isset($agreementLetter->custom_fields['custom_second_party_attention']) ? e($agreementLetter->custom_fields['custom_second_party_attention']) : '[**]' }}</td>
                                </tbody>
                            </table>
                        </li>
                    </ol>
                    <p>Or to another address or number as notified from time to time by each Party to the other Party in the manner as mentioned above.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 12<br>KETERPISAHAN</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 12<br>SEVERABILITY</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">Apabila sebagian Pasal dalam Perjanjian ini batal demi hukum atau dibatalkan, maka pembatalan itu tidak akan membatalkan isi Pasal-Pasal lainnya atau tidak membatalkan Perjanjian ini.</li>
                        <li class="margin">Ketidakberlakuan pasal dan ketentuan tersebut sebagaimana dimaksud pada Pasal 12.1 ini, tidak akan mempengaruhi berlakunya atau dapat dilaksanakannya setiap ketentuan lainnya dari Perjanjian ini dan Para Pihak akan segera melakukan negosiasi untuk ketentuan pengganti, jika diperlukan, yang akan dituangkan dalam Adendum yang menjadi bagian tak terpisahkan dari Perjanjian ini.</li>
                        <li class="margin">Apabila seluruh isi Pasal dalam Perjanjian ini dibatalkan, maka tidak akan membatalkan Pasal Pengakhiran Perjanjian, Pasal mengenai Hukum Yang Berlaku dan Penyelesaian Perselisihan, Pasal Korespondensi dan Pasal Keterpisahan ini.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">If part of the Articles in this Agreement are null and void or cancelled, such cancellation shall not cancel the contents of other Articles or cancel this Agreement.</li>
                        <li class="margin">The inapplicability of such articles and provisions as referred to in this Article 12.1 shall not affect the validity or enforceability of any other provisions of this Agreement and the Parties shall immediately negotiate for replacement provisions, if necessary, which shall be set forth in an Addendum which shall be an integral part of this Agreement.</li>
                        <li class="margin">If the entire contents of the Articles in this Agreement are cancelled, it shall not cancel the Article on Termination of Agreement, Article on Governing Law and Dispute Resolution, Article on Correspondence and this Severability Article.</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 13<br>HUKUM YANG BERLAKU DAN PENYELESAIAN PERSELISIHAN</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 13<br>GOVERNING LAW AND DISPUTE RESOLUTION</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">Pelaksanaan Perjanjian ini tunduk pada ketentuan dan peraturan perundang-undangan yang berlaku menurut Hukum Republik Indonesia.</li>
                        <li class="margin">Dalam hal terjadi perselisihan di antara Para Pihak mengenai pelaksanaan Perjanjian ini, maka Para Pihak dengan didasari itikad baik sepakat untuk menyelesaikannya secara musyawarah untuk mufakat.</li>
                        <li class="margin">Dalam hal Para Pihak tidak dapat menyelesaikan sengketa(-sengketa) dalam waktu 30 (tiga puluh) hari sejak tanggal suatu sengketa tersebut diajukan oleh suatu Pihak dan diberitahukan kepada Pihak lainnya (atau suatu jangka waktu lain yang disepakati bersama antara Para Pihak), sengketa harus diajukan ke dan secara final diselesaikan melalui Pengadilan Negeri Jakarta Barat.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">The implementation of this Agreement is subject to the provisions and laws and regulations applicable under the Law of the Republic of Indonesia.</li>
                        <li class="margin">In the event of a dispute between the Parties regarding the implementation of this Agreement, the Parties in good faith agree to resolve it through deliberation and consensus.</li>
                        <li class="margin">In the event that the Parties cannot resolve the dispute(s) within 30 (thirty) days from the date such dispute is filed by a Party and notified to the other Party (or another period of time mutually agreed upon between the Parties), the dispute must be submitted to and finally resolved through the West Jakarta District Court.</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 14<br>LAIN LAIN</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 14<br>MISCELLANEOUS</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">Setiap perubahan yang akan dilakukan serta hal-hal yang belum cukup diatur dalam Perjanjian ini akan ditetapkan kemudian secara musyawarah oleh Para Pihak serta akan dituangkan dalam perjanjian tambahan (addendum), atau perjanjian pembaruannya/perubahannya yang disepakati oleh Para Pihak dan merupakan suatu kesatuan dan bagian yang tidak dapat dipisahkan dari Perjanjian ini.</li>
                        <li class="margin">Seluruh addendum atau perjanjian pembaruannya/perubahannya atas Perjanjian ini sah apabila ditandatangani oleh Para Pihak.</li>
                        <li class="margin">Apabila berlaku peraturan perundang-undangan atau kebijakan pemerintah terhadap Perjanjian ini, maka Para Pihak akan tunduk pada peraturan perundang-undangan atau kebijakan pemerintah tersebut.</li>
                        <li class="margin">Semua surat-surat, dokumen-dokumen yang menjadi lampiran yang disebutkan dan turut disertakan dalam Perjanjian ini atau lampiran-lampiran/perjanjian tambahan yang akan dibuat pada waktunya nanti di kemudian hari oleh Para Pihak merupakan satu kesatuan dan bagian yang tidak dapat dipisahkan dari Perjanjian ini.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">Any changes to be made and matters not adequately covered in this Agreement shall be determined later through deliberation by the Parties and shall be set forth in additional agreements (addendum), or renewal/amendment agreements agreed upon by the Parties and constitute an integral and inseparable part of this Agreement.</li>
                        <li class="margin">All addendums or renewal/amendment agreements to this Agreement are valid if signed by the Parties.</li>
                        <li class="margin">If laws and regulations or government policies apply to this Agreement, the Parties shall be subject to such laws and regulations or government policies.</li>
                        <li class="margin">All letters, documents that become attachments mentioned and included in this Agreement or attachments/additional agreements that will be made at the appropriate time in the future by the Parties constitute an integral and inseparable part of this Agreement.</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>DEMIKIANLAH, Perjanjian ini dibuat dalam rangkap 2 (dua) yang masing-masing ditandatangani oleh Para Pihak pada hari dan tanggal seperti tertulis di awal Perjanjian ini dan memiliki kekuatan hukum yang sama. Dalam hal Perjanjian ini ditandatangani secara elektronik maka setiap salinan digital akan memiliki kekuatan yang sama dan berlaku seperti penandatanganan perjanjian asli, dan tanda tangan digital akan dianggap sebuah tanda tangan asli.</p>
                    <p>Tanda tangan pada halaman berikutnya.</p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>THUS, This Agreement is made in 2 (two) copies, each of which is signed by the Parties on the day and date written at the beginning of this Agreement and has the same legal force. In the event that this Agreement is signed electronically, each digital copy shall have the same force and effect as the signing of the original agreement, and digital signatures shall be deemed an original signature.</p>
                    <p>Signature on the following page.</p>
                </div>
            </div>

            <div class="row mt-5 mb-3">
                <div class="col-5 text-justify">
                    <p class="noMargin">
                        <strong>PIHAK PERTAMA / FIRST PARTY</strong>
                    </p>
                    <p class="noMargin">
                        <strong>{{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : '[**]' }}</strong>
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p class="noMargin">
                        <strong>PIHAK KEDUA / SECOND PARTY</strong>
                    </p>
                    <p class="noMargin">
                        <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</strong>
                    </p>
                </div>
            </div>

            <div class="row mt-2 mb-3">
                <div class="col-2 text-center d-flex justify-content-center align-items-center">
                    <img src="{{ asset('logo/paraf.png') }}" alt="Signature" style="with:auto; height:150px" class="left-aligned-image">
                </div>
                <div class="offset-2 col-5 text-center">
                </div>
            </div>

            <div class="row mt-3 mb-5">
                <div class="col-4 text-justify">
                    <p class="noMargin">
                        <strong>{{ isset($agreementLetter->custom_fields['custom_first_party_represented_by']) ? e($agreementLetter->custom_fields['custom_first_party_represented_by']) : '[**]' }}</strong>
                    </p>
                    <p class="noMargin">
                        <strong>{{ isset($agreementLetter->custom_fields['custom_first_party_position']) ? e($agreementLetter->custom_fields['custom_first_party_position']) : '[**]' }}</strong>
                    </p>
                </div>
                <div class="offset-3 col-5 text-justify">
                    <p class="noMargin">
                        <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_represented_by']) ? e($agreementLetter->custom_fields['custom_second_party_represented_by']) : (isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]') }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-12 text-center mt-3">
        <a href="{{ route('agreement-letter.edit', $agreementLetter) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Edit</a>
        <button type="button" id="downloadAgreement" class="btn btn-success"><i class="fa fa-file-pdf"></i> Download</button>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        updateCustomerField();

        $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih Quote'
        });

        $("#downloadAgreement").click(function (e) 
        { 
            console.log("hereeee");
            
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