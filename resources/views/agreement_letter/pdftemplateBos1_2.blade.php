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
            <!-- Header -->
            <div class="row mb-5">
                <div class="col-5 text-center">
                    <h5><strong>PERJANJIAN KERJASAMA<br>PENYEWAAN LAPTOP</strong></h5>
                    <p class="noMargin"><strong>ANTARA</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) : '[**]' }}</strong></p>
                    <p class="noMargin"><strong>DENGAN</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_name']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_name']) : '[**]' }}</strong></p>
                    <p class="noMargin"><strong>No: {{ $nomorAgreementLetter ?? '[**]' }}</strong></p>
                </div>
                <div class="offset-2 col-5 text-center">
                    <h5><strong>COLLABORATION AGREEMENT<br>LAPTOP RENTAL</strong></h5>
                    <p class="noMargin"><strong>BETWEEN</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) : '[**]' }}</strong></p>
                    <p class="noMargin"><strong>WITH</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_name']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_name']) : '[**]' }}</strong></p>
                    <p class="noMargin"><strong>No: {{ $nomorAgreementLetter ?? '[**]' }}</strong></p>
                </div>
            </div>

            <!-- Pembukaan -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <p>
                        Perjanjian Penyewaan Laptop (selanjutnya disebut <strong>"Perjanjian"</strong>) ini ditandatangani pada tanggal {{ isset($agreementLetter->custom_fields['custom_laptop_agreement_signing_date']) ? date('d F Y', strtotime($agreementLetter->custom_fields['custom_laptop_agreement_signing_date'])) : '[**]' }}, oleh dan antara:
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        This Laptop Rental Agreement (hereinafter referred to as <strong>"Agreement"</strong>) is signed on {{ isset($agreementLetter->custom_fields['custom_laptop_agreement_signing_date']) ? date('d F Y', strtotime($agreementLetter->custom_fields['custom_laptop_agreement_signing_date'])) : '[**]' }}, by and between:
                    </p>
                </div>
            </div>

            <!-- Para Pihak -->
            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li style="margin-bottom: 20px;">
                            <strong>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) : '[**]' }}</strong>, suatu perseroan terbatas yang didirikan dan berdiri secara sah berdasarkan hukum Indonesia yang berkedudukan di {{ isset($agreementLetter->custom_fields['custom_laptop_first_party_address']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_address']) : '[**]' }}, dalam hal ini diwakili oleh <strong>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_represented_by']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_represented_by']) : '[**]' }}</strong> dalam kapasitasnya sebagai {{ isset($agreementLetter->custom_fields['custom_laptop_first_party_position']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_position']) : '[**]' }}, oleh karena itu sah bertindak untuk dan atas nama <strong>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) : '[**]' }}</strong>, (untuk selanjutnya disebut sebagai <strong>"Pihak Pertama"</strong>);
                        </li>

                        <li style="margin-bottom: 20px;">
                            @if(isset($agreementLetter->custom_fields['custom_laptop_second_party_type']) && $agreementLetter->custom_fields['custom_laptop_second_party_type'] == 'company')
                                <strong>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_name']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_name']) : '[**]' }}</strong>, suatu perseroan terbatas yang didirikan dan berdiri secara sah berdasarkan hukum Indonesia yang beralamat di {{ isset($agreementLetter->custom_fields['custom_laptop_second_party_address']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_address']) : '[**]' }}, dalam hal ini diwakili oleh {{ isset($agreementLetter->custom_fields['custom_laptop_second_party_represented_by']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_represented_by']) : '[**]' }} sebagai <strong>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_position']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_position']) : '[**]' }}</strong>, secara sah bertindak untuk dan atas nama {{ isset($agreementLetter->custom_fields['custom_laptop_second_party_name']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_name']) : '[**]' }}, (untuk selanjutnya disebut sebagai <strong>"Pihak kedua"</strong>).
                            @else
                                <strong>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_name']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_name']) : '[**]' }}</strong>, perorangan yang memiliki identitas dengan nomor {{ isset($agreementLetter->custom_fields['custom_laptop_second_party_identity_number']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_identity_number']) : '[**]' }}, yang berdomisili di {{ isset($agreementLetter->custom_fields['custom_laptop_second_party_address']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_address']) : '[**]' }}, mewakili dan bertanggung jawab untuk dan atas nama diri sendiri dan/atau {{ isset($agreementLetter->custom_fields['custom_laptop_second_party_name']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_name']) : '[**]' }}, (untuk selanjutnya disebut sebagai <strong>"Pihak kedua"</strong>).
                            @endif
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li style="margin-bottom: 20px;">
                            <strong>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) : '[**]' }}</strong>, a limited liability company established and standing legally under Indonesian law, domiciled in {{ isset($agreementLetter->custom_fields['custom_laptop_first_party_address']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_address']) : '[**]' }}, in this matter represented by <strong>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_represented_by']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_represented_by']) : '[**]' }}</strong> in his/her capacity as {{ isset($agreementLetter->custom_fields['custom_laptop_first_party_position']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_position']) : '[**]' }}, therefore legally acting for and on behalf of <strong>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) : '[**]' }}</strong>, (hereinafter referred to as <strong>"First Party"</strong>);
                        </li>

                        <li style="margin-bottom: 20px;">
                            @if(isset($agreementLetter->custom_fields['custom_laptop_second_party_type']) && $agreementLetter->custom_fields['custom_laptop_second_party_type'] == 'company')
                                <strong>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_name']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_name']) : '[**]' }}</strong>, a limited liability company established and standing legally under Indonesian law, addressed at {{ isset($agreementLetter->custom_fields['custom_laptop_second_party_address']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_address']) : '[**]' }}, in this matter represented by {{ isset($agreementLetter->custom_fields['custom_laptop_second_party_represented_by']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_represented_by']) : '[**]' }} as <strong>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_position']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_position']) : '[**]' }}</strong>, legally acting for and on behalf of {{ isset($agreementLetter->custom_fields['custom_laptop_second_party_name']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_name']) : '[**]' }}, (hereinafter referred to as <strong>"Second Party"</strong>).
                            @else
                                <strong>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_name']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_name']) : '[**]' }}</strong>, an individual with identity number {{ isset($agreementLetter->custom_fields['custom_laptop_second_party_identity_number']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_identity_number']) : '[**]' }}, domiciled in {{ isset($agreementLetter->custom_fields['custom_laptop_second_party_address']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_address']) : '[**]' }}, representing and responsible for and on behalf of himself/herself and/or {{ isset($agreementLetter->custom_fields['custom_laptop_second_party_name']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_name']) : '[**]' }}, (hereinafter referred to as <strong>"Second Party"</strong>).
                            @endif
                        </li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        Pihak Pertama dan Pihak Kedua selanjutnya secara bersama-sama di dalam Perjanjian ini akan disebut sebagai <strong>"Para Pihak"</strong>, sedangkan masing-masing disebut <strong>"Pihak"</strong>.
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        First Party and Second Party hereinafter collectively referred to as <strong>"Parties"</strong> in this Agreement, while individually referred to as <strong>"Party"</strong>.
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>Para Pihak dalam kedudukannya masing-masing sebagaimana tersebut diatas, terlebih dahulu menerangkan sebagai berikut:</p>
                    <ol>
                        <li class="margin">
                            Bahwa Pihak Pertama adalah suatu perseroan terbatas yang bergerak dalam bidang {{ isset($agreementLetter->custom_fields['custom_laptop_first_party_business_field']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_business_field']) : '[**]' }};
                        </li>
                        <li class="margin">
                            Bahwa Pihak kedua adalah 
                            @if(isset($agreementLetter->custom_fields['custom_laptop_second_party_type']) && $agreementLetter->custom_fields['custom_laptop_second_party_type'] == 'company')
                                suatu perseroan terbatas
                            @else
                                perorangan
                            @endif
                            yang memiliki usaha di bidang {{ isset($agreementLetter->custom_fields['custom_laptop_second_party_business_field']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_business_field']) : '[**]' }}.
                        </li>
                        <li class="margin">
                            Bahwa Pihak Pertama bermaksud menyewakan perangkat laptop ("<strong>Unit</strong>") kepada Pihak Kedua dan Pihak kedua dengan ini menyetujuinya dan Para Pihak sepakat untuk mengikatkan diri dalam sebuah Perjanjian dengan syarat-syarat dan ketentuan-ketentuan kerjasama dalam Perjanjian ini.
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>The Parties in their respective positions as mentioned above, first explain as follows:</p>
                    <ol>
                        <li class="margin">
                            Whereas, First Party is a limited liability company engaged in {{ isset($agreementLetter->custom_fields['custom_laptop_first_party_business_field']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_business_field']) : '[**]' }};
                        </li>
                        <li class="margin">
                            Whereas, Second Party is 
                            @if(isset($agreementLetter->custom_fields['custom_laptop_second_party_type']) && $agreementLetter->custom_fields['custom_laptop_second_party_type'] == 'company')
                                a limited liability company
                            @else
                                an individual
                            @endif
                            having business in the field of {{ isset($agreementLetter->custom_fields['custom_laptop_second_party_business_field']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_business_field']) : '[**]' }}.
                        </li>
                        <li class="margin">
                            Whereas, First Party intends to rent out laptop devices ("<strong>Unit</strong>") to Second Party and Second Party hereby agrees to it and the Parties agree to bind themselves in an Agreement with the terms and conditions of cooperation in this Agreement.
                        </li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        Berdasarkan hal-hal tersebut diatas, maka dengan ini Para Pihak telah setuju dan sepakat untuk membuat, menandatangani dan melaksanakan Perjanjian dengan syarat-syarat dan ketentuan-ketentuan yang diatur sebagai berikut:
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        Based on the above matters, the Parties have agreed and consented to make, sign and execute the Agreement with the terms and conditions as follows:
                    </p>
                </div>
            </div>

            <!-- PASAL 1 -->
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
                        <li class="margin">
                            Perjanjian ini mengatur syarat-syarat yang sifatnya umum, yaitu ketentuan-ketentuan penyewaan perangkat laptop, termasuk namun tidak terbatas kepada deskripsi Unit, sebagai berikut:
                            <ol type="a">
                                <li class="margin">
                                    Unit yang disewakan memiliki spesifikasi {{ isset($agreementLetter->custom_fields['custom_laptop_specification']) ? e($agreementLetter->custom_fields['custom_laptop_specification']) : '[sebutkan spek singkat]' }}, dengan seri {{ isset($agreementLetter->custom_fields['custom_laptop_series']) ? e($agreementLetter->custom_fields['custom_laptop_series']) : '[seri laptop]' }} keluaran tahun {{ isset($agreementLetter->custom_fields['custom_laptop_year']) ? e($agreementLetter->custom_fields['custom_laptop_year']) : '[tahun]' }}.
                                </li>
                                <li class="margin">
                                    Jumlah unit yang disewakan sebanyak {{ isset($agreementLetter->custom_fields['custom_laptop_quantity']) ? e($agreementLetter->custom_fields['custom_laptop_quantity']) : '[**]' }} unit.
                                </li>
                                <li class="margin">
                                    Kondisi laptop dalam kondisi baik dan disertai perlengkapan lengkap berupa {{ isset($agreementLetter->custom_fields['custom_laptop_accessories']) ? e($agreementLetter->custom_fields['custom_laptop_accessories']) : '[daftar aksesoris, misalnya: charger, tas, mouse]' }}.
                                </li>
                            </ol>
                        </li>
                        <li class="margin">
                            Pihak Pertama sepakat untuk memberikan kepada Pihak kedua, informasi yang diperlukan untuk melaksanakan Perjanjian ini.
                        </li>
                        @if(isset($agreementLetter->custom_fields['custom_laptop_backup_unit']) && $agreementLetter->custom_fields['custom_laptop_backup_unit'] == 'yes')
                        <li class="margin">
                            Pihak Pertama akan menyediakan 2 Unit cadangan apabila Pihak Kedua melakukan sewa Unit diatas 20 (dua puluh) unit.
                        </li>
                        @endif
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">
                            This Agreement regulates general terms and conditions, namely the provisions for renting laptop devices, including but not limited to the description of the Unit, as follows:
                            <ol type="a">
                                <li class="margin">
                                    The rented Unit has specifications {{ isset($agreementLetter->custom_fields['custom_laptop_specification']) ? e($agreementLetter->custom_fields['custom_laptop_specification']) : '[specify brief specs]' }}, with series {{ isset($agreementLetter->custom_fields['custom_laptop_series']) ? e($agreementLetter->custom_fields['custom_laptop_series']) : '[laptop series]' }} manufactured in {{ isset($agreementLetter->custom_fields['custom_laptop_year']) ? e($agreementLetter->custom_fields['custom_laptop_year']) : '[year]' }}.
                                </li>
                                <li class="margin">
                                    The number of units rented is {{ isset($agreementLetter->custom_fields['custom_laptop_quantity']) ? e($agreementLetter->custom_fields['custom_laptop_quantity']) : '[**]' }} units.
                                </li>
                                <li class="margin">
                                    The laptop is in good condition and comes with complete accessories including {{ isset($agreementLetter->custom_fields['custom_laptop_accessories']) ? e($agreementLetter->custom_fields['custom_laptop_accessories']) : '[list of accessories, for example: charger, bag, mouse]' }}.
                                </li>
                            </ol>
                        </li>
                        <li class="margin">
                            First Party agrees to provide Second Party with the information necessary to carry out this Agreement.
                        </li>
                        @if(isset($agreementLetter->custom_fields['custom_laptop_backup_unit']) && $agreementLetter->custom_fields['custom_laptop_backup_unit'] == 'yes')
                        <li class="margin">
                            First Party will provide 2 backup Units if Second Party rents more than 20 (twenty) units.
                        </li>
                        @endif
                    </ol>
                </div>
            </div>

            <!-- PASAL 2 -->
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
                        <li class="margin">
                            Perjanjian ini mulai berlaku pada dan sejak tanggal {{ isset($agreementLetter->custom_fields['custom_laptop_agreement_start_date']) ? date('d F Y', strtotime($agreementLetter->custom_fields['custom_laptop_agreement_start_date'])) : '[**]' }} sampai dengan {{ isset($agreementLetter->custom_fields['custom_laptop_agreement_end_date']) ? date('d F Y', strtotime($agreementLetter->custom_fields['custom_laptop_agreement_end_date'])) : '[**]' }} dan/atau sampai para pihak menyelesaikan kewajibannya masing-masing dan para pihak telah memperoleh masing-masing haknya ("<strong>Jangka Waktu</strong>").
                        </li>
                        <li class="margin">
                            Apabila Pihak Kedua akan perpanjangan sewa, maka Pihak Kedua akan memberitahu Pihak Pertama paling lambat 14 (empat belas) hari sebelum Jangka Waktu berakhir.
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">
                            This Agreement shall be effective from and as of {{ isset($agreementLetter->custom_fields['custom_laptop_agreement_start_date']) ? date('d F Y', strtotime($agreementLetter->custom_fields['custom_laptop_agreement_start_date'])) : '[**]' }} until {{ isset($agreementLetter->custom_fields['custom_laptop_agreement_end_date']) ? date('d F Y', strtotime($agreementLetter->custom_fields['custom_laptop_agreement_end_date'])) : '[**]' }} and/or until the parties complete their respective obligations and the parties have obtained their respective rights ("<strong>Term</strong>").
                        </li>
                        <li class="margin">
                            If Second Party will extend the rental, Second Party shall notify First Party no later than 14 (fourteen) days before the Term expires.
                        </li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 3 -->
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
                        <li class="margin">
                            Pihak Kedua akan membayarkan biaya sewa sebesar Rp.{{ isset($agreementLetter->custom_fields['custom_laptop_rental_fee_amount']) ? number_format($agreementLetter->custom_fields['custom_laptop_rental_fee_amount'], 0, ',', '.') : '[**]' }},- ({{ isset($agreementLetter->custom_fields['custom_laptop_rental_fee_words']) ? e($agreementLetter->custom_fields['custom_laptop_rental_fee_words']) : '[**]' }} Rupiah) belum termasuk pajak ("<strong>Biaya Sewa</strong>").
                        </li>
                        <li class="margin">
                            Pihak Kedua wajib melakukan pembayaran dengan skema pembayaran sebagai berikut:
                            <ol type="a">
                                <li class="margin">
                                    Pembayaran uang muka sebesar 50% (lima puluh) persen dari total Biaya Sewa wajib dibayarkan saat Perjanjian ditandatangani.
                                </li>
                                <li class="margin">
                                    Pembayaran pelunasan sebesar 50% (lima puluh) persen dari total Biaya Sewa wajib dibayarkan paling lambat pada saat serah terima Unit.
                                </li>
                            </ol>
                        </li>
                        <li class="margin">
                            Pihak Kedua wajib membayar deposit sebesar Rp {{ isset($agreementLetter->custom_fields['custom_laptop_deposit_per_unit']) ? number_format($agreementLetter->custom_fields['custom_laptop_deposit_per_unit'], 0, ',', '.') : '150.000' }},- per Unit ("<strong>Deposit</strong>") yang wajib dibayarkan paling lambat pada hari penyerahan Unit dari Pihak Pertama kepada Pihak Kedua. Deposit akan dikembalikan pada waktu penyerahan Unit saat Jangka Waktu sewa berakhir dengan ketentuan bahwa Unit dikembalikan dengan keadaan baik seperti pada awal Perjanjian.
                        </li>
                        <li class="margin">
                            Pembayaran atas Biaya Sewa dan Deposit akan dilakukan dengan cara transfer ke rekening Pihak Pertama dengan data sebagai berikut:
                            <table style="margin-left: 20px; margin-top: 10px;">
                                <tr>
                                    <td style="width: 150px;">Nama Pemilik Rekening</td>
                                    <td style="width: 20px;">:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_account_holder_name']) ? e($agreementLetter->custom_fields['custom_laptop_account_holder_name']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Nama Bank</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_bank_name']) ? e($agreementLetter->custom_fields['custom_laptop_bank_name']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Kantor Cabang</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_branch_office']) ? e($agreementLetter->custom_fields['custom_laptop_branch_office']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Nomor Rekening</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_account_number']) ? e($agreementLetter->custom_fields['custom_laptop_account_number']) : '[**]' }}</td>
                                </tr>
                            </table>
                        </li>
                        @if(isset($agreementLetter->custom_fields['custom_laptop_software_installation_fee']) && $agreementLetter->custom_fields['custom_laptop_software_installation_fee'] > 0)
                        <li class="margin">
                            Apabila Pihak Kedua membutuhkan, biaya pada Pasal 3.1 belum termasuk jasa instalasi <em>software</em> sebesar Rp {{ number_format($agreementLetter->custom_fields['custom_laptop_software_installation_fee'], 0, ',', '.') }},-.
                        </li>
                        @endif
                        @if(isset($agreementLetter->custom_fields['custom_laptop_delivery_fee']) && $agreementLetter->custom_fields['custom_laptop_delivery_fee'] > 0)
                        <li class="margin">
                            Apabila Pihak Kedua meminta Unit dikirim ke lokasi Pihak Kedua, maka Pihak Kedua akan dikenakan biaya sebesar Rp {{ number_format($agreementLetter->custom_fields['custom_laptop_delivery_fee'], 0, ',', '.') }},-.
                        </li>
                        @endif
                        <li class="margin">
                            Segala hak dan kewajiban pajak serta segala pungutan dan bea apapun, baik yang dikenakan oleh pemerintah pusat maupun oleh pemerintah daerah yang wajib dipenuhi sebagai akibat dari atau sehubungan dengan pembuatan dan pelaksanaan Perjanjian ini menjadi tanggungan dan harus dipenuhi oleh masing-masing Pihak sesuai dengan peraturan perundang-undangan perpajakan yang berlaku di Indonesia berikut dengan perubahan-perubahan dan/atau penambahannya.
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">
                            Second Party shall pay the rental fee of Rp.{{ isset($agreementLetter->custom_fields['custom_laptop_rental_fee_amount']) ? number_format($agreementLetter->custom_fields['custom_laptop_rental_fee_amount'], 0, ',', '.') : '[**]' }},- ({{ isset($agreementLetter->custom_fields['custom_laptop_rental_fee_words']) ? e($agreementLetter->custom_fields['custom_laptop_rental_fee_words']) : '[**]' }} Rupiah) excluding tax ("<strong>Rental Fee</strong>").
                        </li>
                        <li class="margin">
                            Second Party is obligated to make payment with the following payment scheme:
                            <ol type="a">
                                <li class="margin">
                                    Down payment of 50% (fifty) percent of the total Rental Fee must be paid when the Agreement is signed.
                                </li>
                                <li class="margin">
                                    Full payment of 50% (fifty) percent of the total Rental Fee must be paid no later than upon handover of the Unit.
                                </li>
                            </ol>
                        </li>
                        <li class="margin">
                            Second Party is obligated to pay a deposit of Rp {{ isset($agreementLetter->custom_fields['custom_laptop_deposit_per_unit']) ? number_format($agreementLetter->custom_fields['custom_laptop_deposit_per_unit'], 0, ',', '.') : '150,000' }},- per Unit ("<strong>Deposit</strong>") which must be paid no later than the day of handover of the Unit from First Party to Second Party. The Deposit will be returned upon handover of the Unit when the rental Term ends, provided that the Unit is returned in good condition as at the beginning of the Agreement.
                        </li>
                        <li class="margin">
                            Payment of Rental Fee and Deposit shall be made by transfer to First Party's account with the following data:
                            <table style="margin-left: 20px; margin-top: 10px;">
                                <tr>
                                    <td style="width: 150px;">Account Holder Name</td>
                                    <td style="width: 20px;">:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_account_holder_name']) ? e($agreementLetter->custom_fields['custom_laptop_account_holder_name']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Bank Name</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_bank_name']) ? e($agreementLetter->custom_fields['custom_laptop_bank_name']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Branch Office</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_branch_office']) ? e($agreementLetter->custom_fields['custom_laptop_branch_office']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Account Number</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_account_number']) ? e($agreementLetter->custom_fields['custom_laptop_account_number']) : '[**]' }}</td>
                                </tr>
                            </table>
                        </li>
                        @if(isset($agreementLetter->custom_fields['custom_laptop_software_installation_fee']) && $agreementLetter->custom_fields['custom_laptop_software_installation_fee'] > 0)
                        <li class="margin">
                            If Second Party requires, the fees stated in Article 3.1 do not include software installation services amounting to Rp {{ number_format($agreementLetter->custom_fields['custom_laptop_software_installation_fee'], 0, ',', '.') }},-.
                        </li>
                        @endif
                        @if(isset($agreementLetter->custom_fields['custom_laptop_delivery_fee']) && $agreementLetter->custom_fields['custom_laptop_delivery_fee'] > 0)
                        <li class="margin">
                            If Second Party requests the Unit to be delivered to Second Party's location, Second Party will be charged a fee of Rp {{ number_format($agreementLetter->custom_fields['custom_laptop_delivery_fee'], 0, ',', '.') }},-.
                        </li>
                        @endif
                        <li class="margin">
                            All tax rights and obligations as well as all levies and duties of any kind, whether imposed by the central government or by local government that must be fulfilled as a result of or in connection with the making and implementation of this Agreement shall be the responsibility of and must be fulfilled by each Party in accordance with applicable tax laws in Indonesia along with its amendments and/or additions.
                        </li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 4 -->
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
                        <li class="margin">
                            Hak dan Kewajiban Pihak Pertama adalah sebagai berikut:
                            <ol type="a">
                                <li class="margin">Berhak menerima Biaya Sewa dari Pihak Kedua.</li>
                                <li class="margin">Wajib memberikan Unit paling lambat 2 (dua) hari sebelum tanggal efektif Jangka Waktu.</li>
                                <li class="margin">Wajib memberikan unit yang disewakan dalam kondisi layak pakai.</li>
                                <li class="margin">Wajib melakukan perbaikan apabila terjadi kendala terhadap Unit yang disewakan.</li>
                            </ol>
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">
                            Rights and Obligations of First Party are as follows:
                            <ol type="a">
                                <li class="margin">Entitled to receive Rental Fee from Second Party.</li>
                                <li class="margin">Obligated to provide the Unit no later than 2 (two) days before the effective date of the Term.</li>
                                <li class="margin">Obligated to provide the rented unit in proper working condition.</li>
                                <li class="margin">Obligated to perform repairs if problems occur with the rented Unit.</li>
                            </ol>
                        </li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol start="2">
                        <li class="margin">
                            Hak dan Kewajiban Pihak Kedua adalah sebagai berikut:
                            <ol type="a">
                                <li class="margin">Berhak mendapatkan Unit paling lambat 2 (dua) hari sebelum tanggal efektif Jangka Waktu.</li>
                                <li class="margin">Berhak mendapatkan Unit pengganti apabila terjadi kendala teknis seperti bluescreen atau laptop tidak dapat berfungsi dengan semestinya dengan ketentuan bahwa Pihak Pertama sudah melakukan perbaikan.</li>
                                <li class="margin">Wajib membayarkan Biaya Sewa dan Deposit berdasarkan kesepakatan dalam Perjanjian ini dengan skema yang tercantum pada Pasal 3.</li>
                                <li class="margin">Wajib menjaga Unit yang disewakan dengan baik serta dilarang mengalihkan, memindahtangankan, atau menyewakan kembali Unit tersebut kepada pihak ketiga tanpa persetujuan tertulis dari Pihak Pertama.</li>
                                @if(isset($agreementLetter->custom_fields['custom_laptop_delivery_fee']) && $agreementLetter->custom_fields['custom_laptop_delivery_fee'] > 0)
                                <li class="margin">Wajib membuat video <em>unboxing</em> apabila Unit dikirimkan ke lokasi Pihak Kedua, segala bentuk kerusakan yang diakibatkan pengiriman melalui logistik dan Pihak Kedua tidak mengirimkan video <em>unboxing</em> saat membuka paket, maka seluruh biaya kerugian akan ditanggung oleh Pihak Kedua.</li>
                                @endif
                                <li class="margin">Wajib mengembalikan Unit sesuai jumlah dan kondisi yang sama sesuai di awal Perjanjian.</li>
                                <li class="margin">Wajib membebaskan Pihak Pertama dari segala bentuk tanggung jawab, dampak, dan ganti rugi akibat gugatan dan/atau tuntutan akibat dari segala tindakan dan/atau pelanggaran yang dilakukan oleh Pihak Kedua, serta membersihkan nama baik Pihak Pertama.</li>
                            </ol>
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol start="2">
                        <li class="margin">
                            Rights and Obligations of Second Party are as follows:
                            <ol type="a">
                                <li class="margin">Entitled to receive the Unit no later than 2 (two) days before the effective date of the Term.</li>
                                <li class="margin">Entitled to receive a replacement Unit if technical problems occur such as bluescreen or the laptop cannot function properly, provided that First Party has performed repairs.</li>
                                <li class="margin">Obligated to pay Rental Fee and Deposit based on the agreement in this Agreement with the scheme stated in Article 3.</li>
                                <li class="margin">Obligated to maintain the rented Unit properly and is prohibited from transferring, transferring, or re-renting the Unit to third parties without written approval from First Party.</li>
                                @if(isset($agreementLetter->custom_fields['custom_laptop_delivery_fee']) && $agreementLetter->custom_fields['custom_laptop_delivery_fee'] > 0)
                                <li class="margin">Obligated to make an <em>unboxing</em> video if the Unit is delivered to Second Party's location, any form of damage caused by delivery through logistics and Second Party does not send an <em>unboxing</em> video when opening the package, then all loss costs will be borne by Second Party.</li>
                                @endif
                                <li class="margin">Obligated to return the Unit according to the same number and condition as at the beginning of the Agreement.</li>
                                <li class="margin">Obligated to release First Party from all forms of responsibility, impact, and compensation due to lawsuits and/or demands resulting from all actions and/or violations committed by Second Party, as well as clearing First Party's good name.</li>
                            </ol>
                        </li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 5 -->
            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 5<br>KERAHASIAAN</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 5<br>CONFIDENTIALITY</strong></p>
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
            <!-- PASAL 6 -->
            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 6<br>PERNYATAAN DAN JAMINAN</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 6<br>REPRESENTATIONS AND WARRANTIES</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">Para Pihak adalah subjek hukum yang sah berdasarkan hukum Negara Republik Indonesia dan berwenang untuk membuat Perjanjian ini.</li>
                        <li class="margin">Para Kedua menjamin untuk melaksanakan semua ketentuan dalam Perjanjian ini.</li>
                        <li class="margin">Pihak Kedua dengan ini menyetujui dan menyanggupi untuk melaksanakan kewajibannya berdasarkan Perjanjian ini.</li>
                        <li class="margin">Pihak Kedua menjamin bahwa apabila timbul gugatan dan/atau tuntutan akibat dari segala tindakan dan/atau pelanggaran yang dilakukan oleh Pihak Kedua, maka Pihak Kedua membebaskan Pihak Pertama dari segala bentuk tanggung jawab, dampak, dan ganti rugi akibat gugatan dan/atau tuntutan tersebut.</li>
                        <li class="margin">Pihak Kedua menyatakan dan menjamin bahwa semua data atau informasi yang disampaikan kepada Pihak Pertama merupakan informasi yang benar.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">The Parties are legal subjects under the laws of the Republic of Indonesia and are authorized to make this Agreement.</li>
                        <li class="margin">The Parties warrant to carry out all provisions in this Agreement.</li>
                        <li class="margin">Second Party hereby agrees and undertakes to carry out its obligations under this Agreement.</li>
                        <li class="margin">Second Party warrants that if lawsuits and/or claims arise as a result of all actions and/or violations committed by Second Party, then Second Party releases First Party from all forms of responsibility, impact, and compensation due to such lawsuits and/or claims.</li>
                        <li class="margin">Second Party represents and warrants that all data or information submitted to First Party is correct information.</li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 7 -->
            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 7<br>DENDA DAN GANTI RUGI</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 7<br>PENALTIES AND COMPENSATION</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">
                            Pihak Kedua wajib membayar Biaya Sewa secara tepat waktu, apabila terjadi keterlambatan pembayaran, maka Pihak Kedua akan dikenakan denda sebesar Rp {{ isset($agreementLetter->custom_fields['custom_laptop_late_payment_penalty']) ? number_format($agreementLetter->custom_fields['custom_laptop_late_payment_penalty'], 0, ',', '.') : '[**]' }},-.
                        </li>
                        <li class="margin">
                            Apabila Pihak Kedua melakukan keterlambatan pengembalian unit, maka akan dikenakan denda keterlambatan sebesar {{ isset($agreementLetter->custom_fields['custom_laptop_late_return_penalty_percentage']) ? e($agreementLetter->custom_fields['custom_laptop_late_return_penalty_percentage']) : '[_%]' }}% dari Biaya Sewa perhari, terhitung sejak hari pertama keterlambatan hingga unit dikembalikan.
                        </li>
                        <li class="margin">
                            Apabila Pihak Kedua melakukan kelalaian dan/atau kesalahan yang mengakibatkan kerusakan Unit, maka Pihak Kedua wajib membayar denda sesuai dengan harga Unit pada saat kejadian.
                        </li>
                        <li class="margin">
                            Apabila Unit yang disewakan hilang dalam Jangka Waktu sewa, maka penyewa berkewajiban mengganti sesuai dengan harga pasar Unit pada saat kejadian, termasuk aksesoris pendukung yang turut hilang.
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">
                            Second Party is obligated to pay the Rental Fee on time, if payment is late, Second Party will be subject to a penalty of Rp {{ isset($agreementLetter->custom_fields['custom_laptop_late_payment_penalty']) ? number_format($agreementLetter->custom_fields['custom_laptop_late_payment_penalty'], 0, ',', '.') : '[**]' }},-.
                        </li>
                        <li class="margin">
                            If Second Party delays the return of the unit, a late penalty of {{ isset($agreementLetter->custom_fields['custom_laptop_late_return_penalty_percentage']) ? e($agreementLetter->custom_fields['custom_laptop_late_return_penalty_percentage']) : '[_%]' }}% of the Rental Fee per day will be charged, calculated from the first day of delay until the unit is returned.
                        </li>
                        <li class="margin">
                            If Second Party commits negligence and/or errors resulting in damage to the Unit, Second Party is obligated to pay a penalty according to the price of the Unit at the time of the incident.
                        </li>
                        <li class="margin">
                            If the rented Unit is lost during the rental Term, the tenant is obligated to replace it according to the market price of the Unit at the time of the incident, including supporting accessories that are also lost.
                        </li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 8 -->
            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 8<br>PENGAKHIRAN PERJANJIAN</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 8<br>TERMINATION OF AGREEMENT</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">
                            Pihak Pertama dapat memutuskan Perjanjian ini tanpa perlu pemberitahuan kepada Pihak Kedua, apabila:
                            <ol type="a">
                                <li class="margin">Pihak Kedua melanggar syarat-syarat dan/atau ketentuan-ketentuan dalam Perjanjian ini; dan</li>
                                <li class="margin">Terjadi pelanggaran hukum yang dilakukan oleh Pihak Kedua yang mengakibatkan Pihak Kedua tidak dapat melaksanakan kewajibannya berdasarkan Perjanjian ini.</li>
                            </ol>
                        </li>
                        <li class="margin">
                            Apabila Pihak Kedua memutuskan mengakhiri Perjanjian ini lebih awal, maka Pihak Kedua wajib membayar denda membayarkan 2 (dua) kali lipat dari Biaya Sewa yang telah ditentukan di Perjanjian ini.
                        </li>
                        <li class="margin">
                            Para Pihak setuju dan sepakat untuk mengesampingkan berlakunya ketentuan Pasal 1266 Kitab Undang-Undang Hukum Perdata terhadap Perjanjian ini dalam hal diperlukan suatu putusan pengadilan untuk mengakhiri Perjanjian ini.
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">
                            First Party may terminate this Agreement without notice to Second Party, if:
                            <ol type="a">
                                <li class="margin">Second Party violates the terms and/or provisions in this Agreement; and</li>
                                <li class="margin">A legal violation occurs committed by Second Party which results in Second Party being unable to carry out its obligations under this Agreement.</li>
                            </ol>
                        </li>
                        <li class="margin">
                            If Second Party decides to terminate this Agreement early, Second Party is obligated to pay a penalty of 2 (two) times the Rental Fee specified in this Agreement.
                        </li>
                        <li class="margin">
                            The Parties agree to set aside the application of the provisions of Article 1266 of the Civil Code to this Agreement in the event that a court decision is required to terminate this Agreement.
                        </li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 9 -->
            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 9<br>FORCE MAJEURE</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 9<br>FORCE MAJEURE</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">
                            Yang dimaksud dengan force majeure dalam Perjanjian ini adalah peristiwa yang terjadi di luar kemampuan Para Pihak untuk mengatasinya, dan bukan disebabkan karena kesalahan ataupun kelalaian Para Pihak, seperti antara lain, bencana alam, kebakaran, peperangan, huru hara, pemberontakan, wabah, epidemi, pandemi, sabotase, dan tindakan pemerintah di bidang moneter, yang secara langsung mengganggu pelaksanaan kewajiban Para Pihak dalam Perjanjian ini dan dinyatakan oleh Pemerintah sebagai force majeure.
                        </li>
                        <li class="margin">
                            Apabila terjadi force majeure sebagaimana di maksud dalam Pasal 9.1, maka Pihak yang berada dalam keadaan memaksa berkewajiban memberitahu kan Pihak lainnya dalam waktu selambat-lambatnya 7 (tujuh) hari kalender.
                        </li>
                        <li class="margin">
                            Force majeure sebagaimana dimaksud dalam Pasal ini tidak menghapuskan atau mengakhiri Perjanjian ini serta Para Pihak wajib menyelesaikan kewajibannya masing-masing.
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">
                            Force majeure in this Agreement means events that occur beyond the ability of the Parties to overcome, and are not caused by errors or negligence of the Parties, such as natural disasters, fires, wars, riots, rebellions, epidemics, pandemics, sabotage, and government actions in the monetary field, which directly interfere with the implementation of the obligations of the Parties in this Agreement and are declared by the Government as force majeure.
                        </li>
                        <li class="margin">
                            If force majeure occurs as referred to in Article 9.1, the Party in a state of force majeure is obligated to notify the other Party no later than 7 (seven) calendar days.
                        </li>
                        <li class="margin">
                            Force majeure as referred to in this Article does not eliminate or terminate this Agreement and the Parties are obligated to complete their respective obligations.
                        </li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 10 -->
            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 10<br>KORESPONDENSI</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 10<br>CORRESPONDENCE</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>Setiap pemberitahuan dan komunikasi yang dibuat berdasarkan Perjanjian ini harus dibuat secara tertulis dan memberitahukan kepada masing-masing Pihak dengan alamat sebagai berikut:</p>
                    <ol type="a">
                        <li class="margin">
                            <table style="margin-left: 20px;">
                                <tr>
                                    <td style="width: 100px;">Nama</td>
                                    <td style="width: 20px;">:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Alamat</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_address']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_address']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Telephone</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_phone']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_phone']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Email</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_email']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_email']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Up</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_attention']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_attention']) : '[**]' }}</td>
                                </tr>
                            </table>
                        </li>
                        <li class="margin">
                              <table style="margin-left: 20px;">
                                <tr>
                                    <td style="width: 100px;">Nama</td>
                                    <td style="width: 20px;">:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_name']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_name']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Alamat</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_address']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_address']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Telephone</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_phone']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_phone']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Email</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_email']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_email']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Up</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_attention']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_attention']) : '[**]' }}</td>
                                </tr>
                            </table>
                        </li>
                    </ol>
                    <p>Atau kepada alamat lain atau nomor lain sebagaimana diberitahukan dari waktu ke waktu oleh masing-masing Pihak kepada Pihak lainnya dengan cara sebagaimana disebutkan di atas.</p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>Every notice and communication made under this Agreement must be made in writing and notified to each Party at the following address:</p>
                    <ol type="a">
                        <li class="margin">
                            <table style="margin-left: 20px;">
                                <tr>
                                    <td style="width: 100px;">Name</td>
                                    <td style="width: 20px;">:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Address</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_address']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_address']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Phone</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_phone']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_phone']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Email</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_email']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_email']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Up</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_attention']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_attention']) : '[**]' }}</td>
                                </tr>
                            </table>
                        </li>
                        <li class="margin">
                              <table style="margin-left: 20px;">
                                <tr>
                                    <td style="width: 100px;">Name</td>
                                    <td style="width: 20px;">:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_name']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_name']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Address</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_address']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_address']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Phone</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_phone']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_phone']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Email</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_email']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_email']) : '[**]' }}</td>
                                </tr>
                                <tr>
                                    <td>Up</td>
                                    <td>:</td>
                                    <td>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_attention']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_attention']) : '[**]' }}</td>
                                </tr>
                            </table>
                        </li>
                    </ol>
                    <p>Or to another address or number as notified from time to time by each Party to the other Party in the manner as mentioned above.</p>
                </div>
            </div>

            <!-- PASAL 11 -->
            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 11<br>KETERPISAHAN</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 11<br>SEVERABILITY</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">
                            Apabila sebagian Pasal dalam Perjanjian ini batal demi hukum atau dibatalkan, maka pembatalan itu tidak akan membatalkan isi Pasal-Pasal lainnya atau tidak membatalkan Perjanjian ini.
                        </li>
                        <li class="margin">
                            Ketidakberlakuan pasal dan ketentuan tersebut sebagaimana dimaksud pada Pasal 11.1 ini, tidak akan mempengaruhi berlakunya atau dapat dilaksanakannya setiap ketentuan lainnya dari Perjanjian ini dan Para Pihak akan segera melakukan negosiasi untuk ketentuan pengganti, jika diperlukan, yang akan dituangkan dalam Adendum yang menjadi bagian tak terpisahkan dari Perjanjian ini.
                        </li>
                        <li class="margin">
                            Apabila seluruh isi Pasal dalam Perjanjian ini dibatalkan, maka tidak akan membatalkan Pasal Pengakhiran Perjanjian, Pasal mengenai Hukum Yang Berlaku dan Penyelesaian Perselisihan, Pasal Korespondensi dan Pasal Keterpisahan ini.
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">
                            If part of the Articles in this Agreement are null and void or cancelled, such cancellation shall not cancel the contents of other Articles or cancel this Agreement.
                        </li>
                        <li class="margin">
                            The inapplicability of such articles and provisions as referred to in this Article 11.1 shall not affect the validity or enforceability of any other provisions of this Agreement and the Parties shall immediately negotiate for replacement provisions, if necessary, which shall be set forth in an Addendum which shall be an integral part of this Agreement.
                        </li>
                        <li class="margin">
                            If the entire contents of the Articles in this Agreement are cancelled, it shall not cancel the Article on Termination of Agreement, Article on Governing Law and Dispute Resolution, Article on Correspondence and this Severability Article.
                        </li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 12 -->
            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 12<br>HUKUM YANG BERLAKU DAN PENYELESAIAN PERSELISIHAN</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 12<br>GOVERNING LAW AND DISPUTE RESOLUTION</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">
                            Pelaksanaan Perjanjian ini tunduk pada ketentuan dan peraturan perundang-undangan yang berlaku menurut Hukum Republik Indonesia.
                        </li>
                        <li class="margin">
                            Dalam hal terjadi perselisihan di antara Para Pihak mengenai pelaksanaan Perjanjian ini, maka Para Pihak dengan didasari itikad baik sepakat untuk menyelesaikannya secara musyawarah untuk mufakat.
                        </li>
                        <li class="margin">
                            Dalam hal Para Pihak tidak dapat menyelesaikan sengketa(-sengketa) dalam waktu 30 (tiga puluh) hari sejak tanggal suatu sengketa tersebut diajukan oleh suatu Pihak dan diberitahukan kepada Pihak lainnya (atau suatu jangka waktu lain yang disepakati bersama antara Para Pihak), sengketa harus diajukan ke dan secara final diselesaikan melalui Pengadilan Negeri Jakarta Barat.
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">
                            The implementation of this Agreement is subject to the provisions and laws and regulations applicable under the Law of the Republic of Indonesia.
                        </li>
                        <li class="margin">
                            In the event of a dispute between the Parties regarding the implementation of this Agreement, the Parties in good faith agree to resolve it through deliberation and consensus.
                        </li>
                        <li class="margin">
                            In the event that the Parties cannot resolve the dispute(s) within 30 (thirty) days from the date such dispute is filed by a Party and notified to the other Party (or another period of time mutually agreed upon between the Parties), the dispute must be submitted to and finally resolved through the West Jakarta District Court.
                        </li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 13 -->
            <div class="row">
                <div class="col-5 text-justify">
                    <p><strong>PASAL 13<br>LAIN LAIN</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p><strong>ARTICLE 13<br>MISCELLANEOUS</strong></p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <ol>
                        <li class="margin">
                            Setiap perubahan yang akan dilakukan serta hal-hal yang belum cukup diatur dalam Perjanjian ini akan ditetapkan kemudian secara musyawarah oleh Para Pihak serta akan dituangkan dalam perjanjian tambahan (addendum), atau perjanjian pembaruannya/perubahannya yang disepakati oleh Para Pihak dan merupakan suatu kesatuan dan bagian yang tidak dapat dipisahkan dari Perjanjian ini.
                        </li>
                        <li class="margin">
                            Seluruh addendum atau perjanjian pembaruannya/perubahannya atas Perjanjian ini sah apabila ditandatangani oleh Para Pihak.
                        </li>
                        <li class="margin">
                            Apabila berlaku peraturan perundang-undangan atau kebijakan pemerintah terhadap Perjanjian ini, maka Para Pihak akan tunduk pada peraturan perundang-undangan atau kebijakan pemerintah tersebut.
                        </li>
                        <li class="margin">
                            Semua surat-surat, dokumen-dokumen yang menjadi lampiran yang disebutkan dan turut disertakan dalam Perjanjian ini atau lampiran-lampiran/perjanjian tambahan yang akan dibuat pada waktunya nanti di kemudian hari oleh Para Pihak merupakan satu kesatuan dan bagian yang tidak dapat dipisahkan dari Perjanjian ini.
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <ol>
                        <li class="margin">
                            Any changes to be made and matters not adequately covered in this Agreement shall be determined later through deliberation by the Parties and shall be set forth in additional agreements (addendum), or renewal/amendment agreements agreed upon by the Parties and constitute an integral and inseparable part of this Agreement.
                        </li>
                        <li class="margin">
                            All addendums or renewal/amendment agreements to this Agreement are valid if signed by the Parties.
                        </li>
                        <li class="margin">
                            If laws and regulations or government policies apply to this Agreement, the Parties shall be subject to such laws and regulations or government policies.
                        </li>
                        <li class="margin">
                            All letters, documents that become attachments mentioned and included in this Agreement or attachments/additional agreements that will be made at the appropriate time in the future by the Parties constitute an integral and inseparable part of this Agreement.
                        </li>
                    </ol>
                </div>
            </div>

            <!-- Penutup -->
            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        DEMIKIANLAH, Perjanjian ini dibuat dalam rangkap 2 (dua) yang masing-masing ditandatangani oleh Para Pihak pada hari dan tanggal seperti tertulis di awal Perjanjian ini dan memiliki kekuatan hukum yang sama. Dalam hal Perjanjian ini ditandatangani secara elektronik maka setiap salinan digital akan memiliki kekuatan yang sama dan berlaku seperti penandatanganan perjanjian asli, dan tanda tangan digital akan dianggap sebuah tanda tangan asli.
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        THUS, This Agreement is made in 2 (two) copies, each of which is signed by the Parties on the day and date written at the beginning of this Agreement and has the same legal force. In the event that this Agreement is signed electronically, each digital copy shall have the same force and effect as the signing of the original agreement, and digital signatures shall be deemed an original signature.
                    </p>
                </div>
            </div>

            <!-- Tanda Tangan -->
            <div class="row mt-5 mb-2">
                <div class="col-5 text-justify">
                    <p class="noMargin"><strong>PIHAK PERTAMA / FIRST PARTY</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) : '[**]' }}</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p class="noMargin"><strong>PIHAK KEDUA / SECOND PARTY</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_name']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_name']) : '[**]' }}</strong></p>
                </div>
            </div>
            <div class="row mt-4 mb-2">
                <div class="col-3 text-justify d-flex justify-content-center align-items-center">
                    <img src="{{ asset('logo/paraf.png') }}" alt="Signature" style="with:auto; height:150px" class="left-aligned-image">
                </div>
                <div class="offset-2 col-5 text-justify">
                </div>
            </div>

            <div class="row mt-5 mb-2">
                <div class="col-5 text-justify">
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_represented_by']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_represented_by']) : '[**]' }}</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_position']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_position']) : '[**]' }}</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_represented_by']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_represented_by']) : (isset($agreementLetter->custom_fields['custom_laptop_second_party_name']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_name']) : '[**]') }}</strong></p>                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 text-center mt-3">
        <a href="{{ route('agreement-letter.edit', $agreementLetter) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Edit</a>
        <button type="button" id="downloadAgreement" class="btn btn-success"><i class="fa fa-file-pdf"></i> Download</button>
    </div>
</div>
@stop

@section('css')
<style>
    @media print {
        #printItem {
            margin-left: 50px;
            margin-right: 50px;
        }
    }
    body {
        font-family: Arial;
    }
    .scrollable {
        width: 100%;
        height: 650px;
        overflow: auto;
        border: 1px solid #ccc;
    }
    .margin {
        margin-bottom: 15px;
    }
    .noMargin {
        margin-bottom: 0px;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function () {
        $("#downloadAgreement").click(function (e) {
            e.preventDefault();
            printDocument();
        });
    });

    function printDocument() {
        let name = "{{ $nomorAgreementLetter }}_surat_perjanjian_laptop";
        let printContents = document.getElementById("printThis").innerHTML;
        let originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;

        window.addEventListener("beforeprint", (event) => {
            document.title = name;
        });

        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    }
</script>
@stop