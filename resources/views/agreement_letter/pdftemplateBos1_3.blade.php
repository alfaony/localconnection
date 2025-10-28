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
                    <h5><strong>PERJANJIAN KERJASAMA</strong></h5>
                    <p class="noMargin"><strong>ANTARA</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? strtoupper(e($agreementLetter->custom_fields['custom_first_party_company_name'])) : 'PT. [**]' }}</strong></p>
                    <p class="noMargin"><strong>DENGAN</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? strtoupper(e($agreementLetter->custom_fields['custom_second_party_name'])) : '[**]' }}</strong></p>
                    <p class="noMargin"><strong>No: {{ $agreementLetter->number_result ?? '[**]' }}</strong></p>
                </div>
                <div class="offset-2 col-5 text-center">
                    <h5><strong>COOPERATION AGREEMENT</strong></h5>
                    <p class="noMargin"><strong>BETWEEN</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? strtoupper(e($agreementLetter->custom_fields['custom_first_party_company_name'])) : 'PT. [**]' }}</strong></p>
                    <p class="noMargin"><strong>WITH</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? strtoupper(e($agreementLetter->custom_fields['custom_second_party_name'])) : '[**]' }}</strong></p>
                    <p class="noMargin"><strong>No: {{ $agreementLetter->number_result ?? '[**]' }}</strong></p>
                </div>
            </div>

            <!-- Pembukaan -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <p>
                        Perjanjian Kerjasama (selanjutnya disebut <strong>"Perjanjian"</strong>) ini ditandatangani pada tanggal <strong>{{ \Carbon\Carbon::parse($agreementLetter->date)->format('d F Y') }}</strong>, oleh dan antara:
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        The Cooperation Agreement (hereinafter referred to as the <strong>"Agreement"</strong>) is signed on <strong>{{ \Carbon\Carbon::parse($agreementLetter->date)->format('d F Y') }}</strong>, by and between:
                    </p>
                </div>
            </div>

            <!-- Para Pihak -->
            <div class="row">
                <div class="col-5 text-justify">
                    <p style="margin-bottom: 20px;">
                        <strong>{{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : '[**]' }}</strong>, suatu perseroan terbatas yang didirikan dan berdiri secara sah berdasarkan hukum Indonesia yang berkedudukan di {{ isset($agreementLetter->custom_fields['custom_first_party_address']) ? e($agreementLetter->custom_fields['custom_first_party_address']) : '[**]' }}, dalam hal ini diwakili oleh <strong>{{ isset($agreementLetter->custom_fields['custom_first_party_represented_by']) ? e($agreementLetter->custom_fields['custom_first_party_represented_by']) : '[**]' }}</strong> dalam kapasitasnya sebagai {{ isset($agreementLetter->custom_fields['custom_first_party_position']) ? e($agreementLetter->custom_fields['custom_first_party_position']) : '[**]' }}, oleh karena itu sah bertindak untuk dan atas nama <strong>{{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : '[**]' }}</strong>, (untuk selanjutnya disebut sebagai <strong>"Pihak Pertama"</strong>);
                    </p>

                    <p style="margin-bottom: 20px;">
                        @if(isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'company')
                            <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</strong>, suatu perseroan terbatas yang didirikan dan berdiri secara sah berdasarkan hukum Indonesia yang beralamat di {{ isset($agreementLetter->custom_fields['custom_second_party_address']) ? e($agreementLetter->custom_fields['custom_second_party_address']) : '[**]' }}, dalam hal ini diwakili oleh {{ isset($agreementLetter->custom_fields['custom_second_party_represented_by']) ? e($agreementLetter->custom_fields['custom_second_party_represented_by']) : '[**]' }} sebagai <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_position']) ? e($agreementLetter->custom_fields['custom_second_party_position']) : '[**]' }}</strong>, secara sah bertindak untuk dan atas nama {{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}, (untuk selanjutnya disebut sebagai <strong>"Pihak kedua"</strong>).
                        @else
                            <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</strong>, individu yang beralamat di {{ isset($agreementLetter->custom_fields['custom_second_party_address']) ? e($agreementLetter->custom_fields['custom_second_party_address']) : '[**]' }}, bertindak untuk dan atas nama diri sendiri, (untuk selanjutnya disebut sebagai <strong>"Pihak kedua"</strong>).
                        @endif
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p style="margin-bottom: 20px;">
                        <strong>{{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : '[**]' }}</strong>, a limited liability company legally established and standing under Indonesian law domiciled in {{ isset($agreementLetter->custom_fields['custom_first_party_address']) ? e($agreementLetter->custom_fields['custom_first_party_address']) : '[**]' }}, in this case represented by <strong>{{ isset($agreementLetter->custom_fields['custom_first_party_represented_by']) ? e($agreementLetter->custom_fields['custom_first_party_represented_by']) : '[**]' }}</strong> its capacity as {{ isset($agreementLetter->custom_fields['custom_first_party_position']) ? e($agreementLetter->custom_fields['custom_first_party_position']) : '[**]' }}, therefore acting for and on behalf of <strong>{{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : '[**]' }}</strong>, (hereinafter referred to as the <strong>"First Party"</strong>);
                    </p>

                    <p style="margin-bottom: 20px;">
                        @if(isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'company')
                            <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</strong>, a limited liability company legally established and standing under Indonesian law domiciled in {{ isset($agreementLetter->custom_fields['custom_second_party_address']) ? e($agreementLetter->custom_fields['custom_second_party_address']) : '[**]' }}, in this case represented by {{ isset($agreementLetter->custom_fields['custom_second_party_represented_by']) ? e($agreementLetter->custom_fields['custom_second_party_represented_by']) : '[**]' }} its capacity as <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_position']) ? e($agreementLetter->custom_fields['custom_second_party_position']) : '[**]' }}</strong>, therefore acting for and on behalf of <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</strong>, (hereinafter referred to as the <strong>"Second Party"</strong>).
                        @else
                            <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</strong>, an individual domiciled in {{ isset($agreementLetter->custom_fields['custom_second_party_address']) ? e($agreementLetter->custom_fields['custom_second_party_address']) : '[**]' }}, acting for and on behalf of himself/herself, (hereinafter referred to as the <strong>"Second Party"</strong>).
                        @endif
                    </p>
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
                        The First Party and the Second Party will hereinafter be referred to collectively in this Agreement as the <strong>"Parties"</strong>, while each is referred to as a <strong>"Party"</strong>.
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        Para Pihak dalam kedudukannya masing-masing sebagaimana tersebut diatas, terlebih dahulu menerangkan sebagai berikut:
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        The Parties in their respective standing as mentioned above, first explain as follows:
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        Bahwa <strong>PT Gema Teknologi Cahaya Gemilang</strong> ("Pihak Pertama") adalah perusahaan yang bergerak di Bidang Teknologi Informasi dan Penyedia Solusi Otomasi Digital n8n di Indonesia.
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        That <strong>PT Gema Teknologi Cahaya Gemilang</strong> ("First Party") is a company engaged in Information Technology and a provider of n8n Digital Automation Solutions in Indonesia.
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        Bahwa <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '______' }}</strong> ("Pihak Kedua") adalah 
                        @if(isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'individual')
                            individu
                        @else
                            entitas
                        @endif
                        yang memiliki kompetensi dalam memberikan pelatihan, workshop, atau edukasi terkait penggunaan Otomasi Digital dan Sistem n8n.
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        That <strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '______' }}</strong> ("Second Party") is an 
                        @if(isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'individual')
                            individual
                        @else
                            entity
                        @endif
                        competent in providing Training, Workshops, or Educational Sessions related to Digital Automation and n8n Systems.
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-5 text-justify">
                    <p>
                        Para pihak sepakat untuk menjalin kerja sama dalam Program Afiliasi Edukasi n8n, dengan tujuan memperluas adopsi teknologi otomasi di Indonesia melalui kegiatan edukasi, pelatihan, dan promosi bersama.
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        Both parties agree to establish a cooperation under the n8n Education Affiliate Program to expand the adoption of automation technology in Indonesia through joint education, training, and promotional activities.
                    </p>
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
                        Based on the above, the Parties hereby agree and consent to create, sign and implement the Agreement with the terms and conditions which are arranged as follows:
                    </p>
                </div>
            </div>

            <!-- PASAL 1 -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>PASAL 1</strong></h6>
                    <h6><strong>RUANG LINGKUP PERJANJIAN</strong></h6>
                    <p>Ruang lingkup kerja sama ini mencakup:</p>
                    <ol>
                        <li>Para Pihak sepakat bahwa Pihak Kedua akan melakukan Promosi dan edukasi penggunaan sistem otomasi n8n melalui kelas, pelatihan, atau webinar.</li>
                        <li>Para Pihak sepakat bahwa Pihak Pertama akan memberikan akun n8n Pro gratis kepada Pihak Kedua untuk keperluan pelatihan atau lembaga selama masa kerja sama.</li>
                        <li>Para Pihak sepakat bahwa Pihak Pertama akan membayarkan komisi sebesar <strong>{{ isset($agreementLetter->custom_fields['custom_commission_percentage']) ? $agreementLetter->custom_fields['custom_commission_percentage'] : '20' }}%</strong> dari setiap transaksi pembelian akun berbayar oleh murid/peserta yang mendaftar menggunakan kode referral Pihak Kedua.</li>
                        <li>Para Pihak sepakat bahwa materi promosi dapat diakses dan digunakan secara mandiri oleh Pihak Kedua melalui situs resmi Pihak Pertama di https://boc.co.id.</li>
                        <li>Paket layanan dalam kerja sama ini terdiri dari dua jenis:
                            <ol type="a">
                                <li>Paket n8n Pro, diberikan kepada penyelenggara seminar atau pengajar selama masa kerja sama.</li>
                                <li>Paket n8n Free, diberikan kepada peserta seminar yang mendaftar melalui link atau kode afiliasi milik penyelenggara/pengajar.</li>
                            </ol>
                        </li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>ARTICLE 1</strong></h6>
                    <h6><strong>SCOPE OF AGREEMENT</strong></h6>
                    <p>The scope of this cooperation includes:</p>
                    <ol>
                        <li>The Parties agree that Second Party will undertake the Promoting and educating the use of n8n automation systems through classes, training sessions, or webinars.</li>
                        <li>The Parties agree that First Party will Providing a free n8n Pro account to the Second Party for training or institutional use during the cooperation period.</li>
                        <li>The Parties agree that First Party will paying a <strong>{{ isset($agreementLetter->custom_fields['custom_commission_percentage']) ? $agreementLetter->custom_fields['custom_commission_percentage'] : '20' }}%</strong> commission on every paid account purchase made by students/participants who register using the Second Party's referral code.</li>
                        <li>The Parties agree that the promotional materials may be accessed and used independently by the Second Party through the First Party's official website at https://boc.co.id.</li>
                        <li>The service packages under this cooperation consist of two types:
                            <ol type="a">
                                <li>n8n Pro Package, provided to seminar organizers or trainers during the cooperation period.</li>
                                <li>n8n Free Package, provided to seminar participants who register through the organizer's/trainer's affiliate link or code.</li>
                            </ol>
                        </li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 2 -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>PASAL 2</strong></h6>
                    <h6><strong>JANGKA WAKTU</strong></h6>
                    <p>
                        Perjanjian ini mulai berlaku selama <strong>{{ isset($agreementLetter->custom_fields['custom_cooperation_duration_months']) ? '[' . $agreementLetter->custom_fields['custom_cooperation_duration_months'] . ']' : '[**]' }}</strong> bulan terhitung efektif sejak tanggal <strong>{{ isset($agreementLetter->custom_fields['custom_cooperation_start_date']) ? \Carbon\Carbon::parse($agreementLetter->custom_fields['custom_cooperation_start_date'])->format('d F Y') : '[**]' }}</strong> sampai dengan tanggal <strong>{{ isset($agreementLetter->custom_fields['custom_cooperation_end_date']) ? \Carbon\Carbon::parse($agreementLetter->custom_fields['custom_cooperation_end_date'])->format('d F Y') : '[**]' }}</strong> dan dapat diperpanjang berdasarkan hasil review dan evaluasi yang dilakukan oleh Pihak Pertama.
                    </p>
                    <p>
                        Evaluasi awal dilakukan setiap 30 (tiga puluh) hari dalam 3 bulan pertama, dan jika kerja sama diperpanjang, evaluasi berikutnya dilakukan setiap 3 (tiga) bulan.
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>ARTICLE 2</strong></h6>
                    <h6><strong>TIME PERIOD</strong></h6>
                    <p>
                        This agreement is valid for <strong>{{ isset($agreementLetter->custom_fields['custom_cooperation_duration_months']) ? '[' . $agreementLetter->custom_fields['custom_cooperation_duration_months'] . ']' : '[**]' }}</strong> months effective from <strong>{{ isset($agreementLetter->custom_fields['custom_cooperation_start_date']) ? \Carbon\Carbon::parse($agreementLetter->custom_fields['custom_cooperation_start_date'])->format('d F Y') : '[**]' }}</strong> until <strong>{{ isset($agreementLetter->custom_fields['custom_cooperation_end_date']) ? \Carbon\Carbon::parse($agreementLetter->custom_fields['custom_cooperation_end_date'])->format('d F Y') : '[**]' }}</strong> and may be extended based on the review and evaluation conducted by the First Party.
                    </p>
                    <p>
                        The initial evaluation shall be carried out every thirty (30) days during the first three months, and if the cooperation is extended, subsequent evaluations shall be conducted every three (3) months thereafter.
                    </p>
                </div>
            </div>

            <!-- PASAL 3 -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>PASAL 3</strong></h6>
                    <h6><strong>HAK DAN KEWAJIBAN</strong></h6>
                    <p><strong>Hak dan Kewajiban Pihak Pertama:</strong></p>
                    <ol>
                        <li>Memberikan akun n8n Pro gratis selama masa kerja sama.</li>
                        <li>Menyediakan link/kode afiliasi untuk digunakan oleh Pihak Kedua.</li>
                        <li>Membayar komisi <strong>{{ isset($agreementLetter->custom_fields['custom_commission_percentage']) ? $agreementLetter->custom_fields['custom_commission_percentage'] : '20' }}%</strong> atas setiap pembelian berbayar dari peserta yang menggunakan link/kode afiliasi.</li>
                        <li>Mengizinkan Pihak Kedua mengunduh materi promosi secara mandiri melalui situs resmi https://boc.co.id.</li>
                    </ol>

                    <p class="mt-3"><strong>Hak dan Kewajiban Pihak Kedua:</strong></p>
                    <ol>
                        <li>Melaksanakan kegiatan edukasi dan pelatihan sesuai rencana yang disetujui.</li>
                        <li>Mempromosikan link/kode afiliasi dengan etika dan cara yang sesuai hukum.</li>
                        <li>Melaporkan data peserta yang membeli akun n8n melalui kode afiliasi.</li>
                        <li>Tidak menyalahgunakan fasilitas atau melakukan kegiatan di luar perjanjian.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>ARTICLE 3</strong></h6>
                    <h6><strong>RIGHTS AND OBLIGATIONS</strong></h6>
                    <p><strong>First Party's Rights and Obligations:</strong></p>
                    <ol>
                        <li>Provide a free n8n Pro account during the cooperation period.</li>
                        <li>Provide an affiliate link/code for the Second Party's use.</li>
                        <li>Pay a <strong>{{ isset($agreementLetter->custom_fields['custom_commission_percentage']) ? $agreementLetter->custom_fields['custom_commission_percentage'] : '20' }}%</strong> commission on each paid purchase made via the Second Party's link/code.</li>
                        <li>Allowing the Second Party to independently download promotional materials through the official website https://boc.co.id.</li>
                    </ol>

                    <p class="mt-3"><strong>Second Party's Rights and Obligations:</strong></p>
                    <ol>
                        <li>Conduct educational or training activities as per agreed plans.</li>
                        <li>Promote the affiliate link/code ethically and lawfully.</li>
                        <li>Report participant data who purchased n8n accounts via the affiliate code.</li>
                        <li>Refrain from misuse of facilities or activities beyond this agreement.</li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 4 -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>PASAL 4</strong></h6>
                    <h6><strong>BIAYA DAN PEMBAYARAN</strong></h6>
                    <ol>
                        <li>Pihak Kedua berhak menerima komisi sebesar <strong>{{ isset($agreementLetter->custom_fields['custom_commission_percentage']) ? $agreementLetter->custom_fields['custom_commission_percentage'] : '20' }}%</strong> dari transaksi pembelian akun berbayar yang dilakukan peserta dengan kode afiliasi miliknya.</li>
                        <li>Komisi dapat ditarik apabila saldo mencapai minimal <strong>{{ isset($agreementLetter->custom_fields['custom_minimum_withdrawal_amount']) ? 'Rp' . number_format($agreementLetter->custom_fields['custom_minimum_withdrawal_amount'], 0, ',', '.') : 'Rp500.000' }}</strong> (lima ratus ribu rupiah).</li>
                        <li>Pembayaran dilakukan oleh Pihak Pertama paling lambat <strong>{{ isset($agreementLetter->custom_fields['custom_payment_duration_days']) ? $agreementLetter->custom_fields['custom_payment_duration_days'] : '7' }}</strong> (tujuh) hari kerja setelah verifikasi permintaan penarikan komisi.</li>
                        <li>Pihak Pertama berhak menahan pembayaran apabila ditemukan indikasi kecurangan (fraud) atau pelanggaran terhadap ketentuan afiliasi.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>ARTICLE 4</strong></h6>
                    <h6><strong>FEES AND PAYMENT METHOD</strong></h6>
                    <ol>
                        <li>The Second Party is entitled to receive a <strong>{{ isset($agreementLetter->custom_fields['custom_commission_percentage']) ? $agreementLetter->custom_fields['custom_commission_percentage'] : '20' }}%</strong> commission from every paid account purchase made using their affiliate code.</li>
                        <li>Commissions can be withdrawn once the accumulated balance reaches a minimum of <strong>{{ isset($agreementLetter->custom_fields['custom_minimum_withdrawal_amount']) ? 'IDR ' . number_format($agreementLetter->custom_fields['custom_minimum_withdrawal_amount'], 0, ',', '.') : 'IDR 500,000' }}</strong> (five hundred thousand rupiah).</li>
                        <li>Payment shall be made by the First Party no later than <strong>{{ isset($agreementLetter->custom_fields['custom_payment_duration_days']) ? $agreementLetter->custom_fields['custom_payment_duration_days'] : '7' }}</strong> (seven) working days after verification of the withdrawal request.</li>
                        <li>The First Party reserves the right to withhold payment in case of suspected fraud or violation of the affiliate terms.</li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 5 -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>PASAL 5</strong></h6>
                    <h6><strong>HAK KEKAYAAN INTELEKTUAL</strong></h6>
                    <p>
                        Segala hak kekayaan intelektual atas merek, logo, desain, materi pelatihan, serta konten promosi yang disediakan oleh Pihak Pertama tetap menjadi milik Pihak Pertama.
                    </p>
                    <p>
                        Pihak Kedua hanya berhak menggunakan materi dan merek tersebut untuk tujuan edukasi, promosi, dan kegiatan yang telah disetujui bersama.
                    </p>
                    <p>
                        Pihak Kedua dilarang menggandakan, menjual, atau memodifikasi materi tanpa izin tertulis dari Pihak Pertama.
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>ARTICLE 5</strong></h6>
                    <h6><strong>INTELLECTUAL PROPERTY RIGHTS</strong></h6>
                    <p>
                        All intellectual property rights related to trademarks, logos, designs, training materials, and promotional content provided by the First Party remain the exclusive property of the First Party.
                    </p>
                    <p>
                        The Second Party is granted the right to use such materials and trademarks solely for educational, promotional, and jointly approved activities.
                    </p>
                    <p>
                        The Second Party is prohibited from reproducing, selling, or modifying the materials without prior written consent from the First Party.
                    </p>
                </div>
            </div>

            <!-- PASAL 6 -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>PASAL 6</strong></h6>
                    <h6><strong>KERAHASIAAN</strong></h6>
                    <p>
                        Kedua belah pihak wajib menjaga kerahasiaan semua informasi, data pelanggan, strategi, maupun materi internal yang diperoleh selama kerja sama ini berlangsung.
                    </p>
                    <p>
                        Pelanggaran terhadap pasal ini dapat mengakibatkan penghentian kerja sama dan/atau tuntutan hukum sesuai peraturan yang berlaku.
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>ARTICLE 6</strong></h6>
                    <h6><strong>CONFIDENTIALITY</strong></h6>
                    <p>
                        Both parties shall maintain the confidentiality of all information, customer data, strategies, and internal materials obtained during the cooperation.
                    </p>
                    <p>
                        Any breach of this clause may result in termination of the cooperation and/or legal action as permitted by applicable law.
                    </p>
                </div>
            </div>

            <!-- PASAL 7 -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>PASAL 7</strong></h6>
                    <h6><strong>PERNYATAAN DAN JAMINAN</strong></h6>
                    <ol>
                        <li>Para Pihak adalah subjek hukum yang sah berdasarkan hukum Negara Republik Indonesia dan berwenang untuk membuat Perjanjian ini.</li>
                        <li>Para Pihak menjamin untuk melaksanakan semua ketentuan dalam Perjanjian ini.</li>
                        <li>Para Pihak dengan ini menyetujui dan menyanggupi untuk melaksanakan kewajibannya berdasarkan Perjanjian ini.</li>
                        <li>Para Pihak menyatakan dan menjamin bahwa semua data atau informasi yang disampaikan kepada Pihak Pertama merupakan informasi yang benar.</li>
                        <li>Pihak Kedua menjamin bahwa seluruh kegiatan promosi dan edukasi akan dilakukan dengan cara yang etis dan tidak merugikan reputasi Pihak Pertama.</li>
                        <li>Pihak Pertama menjamin akan menyediakan akun n8n Pro dan sistem afiliasi yang berfungsi sesuai ketentuan program, tanpa kewajiban menyediakan dukungan teknis individual. Dukungan penggunaan dapat diperoleh melalui support resmi di https://boc.co.id.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>ARTICLE 7</strong></h6>
                    <h6><strong>REPRESENTATIONS AND WARRANTIES</strong></h6>
                    <ol>
                        <li>The Parties are legal entities recognized under the laws of the Republic of Indonesia and are authorized to enter into this Agreement.</li>
                        <li>The Parties guarantee to fulfill all provisions of this Agreement.</li>
                        <li>The Parties hereby agree and undertake to implement the obligations under this Agreement.</li>
                        <li>The Parties declare and guarantee that all data or information provided to the First Party is accurate.</li>
                        <li>The Second Party guarantees that all promotional and educational activities will be conducted ethically and without harming the reputation of the First Party.</li>
                        <li>The First Party guarantees to provide the n8n Pro account and affiliate system as stipulated in the program terms, without the obligation to provide individual technical support. Usage guidance and promotional documentation can be accessed via the official website https://boc.co.id.</li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 8 - EVALUASI & PENGAKHIRAN PERJANJIAN -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>PASAL 8</strong></h6>
                    <h6><strong>EVALUASI & PENGAKHIRAN PERJANJIAN</strong></h6>
                    <p>
                        Para Pihak sepakat bahwa pelaksanaan kerja sama ini akan dievaluasi secara berkala oleh Pihak Pertama untuk menilai efektivitas, kepatuhan terhadap ketentuan perjanjian, serta pencapaian hasil yang diharapkan.
                    </p>
                    <p>
                        Dalam setiap periode evaluasi, Pihak Pertama akan melakukan penilaian terhadap kinerja Pihak Kedua guna memastikan keberlanjutan dan peningkatan mutu kerja sama, dengan mempertimbangkan indikator-indikator berikut:
                    </p>
                    <ol>
                        <li>Jumlah pendaftar akun Free;</li>
                        <li>Persentase peserta yang melakukan upgrade ke paket berbayar;</li>
                        <li>Total komisi yang dihasilkan;</li>
                        <li>Aktivitas pada akun-akun yang melakukan registrasi melalui link afiliasi;</li>
                        <li>Feedback dan hasil pelaksanaan kelas pelatihan.</li>
                    </ol>
                    <p>
                        Para Pihak sepakat bahwa Perjanjian ini dapat diakhiri sebelum jangka waktu berakhir apabila terjadi kondisi tertentu yang menyebabkan kerja sama tidak dapat dilanjutkan, baik karena pelanggaran ketentuan perjanjian, alasan hukum, maupun berdasarkan hasil evaluasi yang dilakukan oleh Pihak Pertama, sebagaimana diatur dalam pasal ini.
                    </p>
                    <ol>
                        <li>Pihak Pertama dapat memutuskan Perjanjian ini tanpa perlu pemberitahuan kepada Pihak Kedua, apabila:
                            <ol type="a">
                                <li>Pihak Kedua melanggar syarat-syarat dan/atau ketentuan-ketentuan dalam Perjanjian ini; dan</li>
                                <li>Terjadi pelanggaran hukum yang dilakukan oleh Pihak Kedua yang mengakibatkan Pihak Kedua tidak dapat melaksanakan kewajibannya berdasarkan Perjanjian ini.</li>
                            </ol>
                        </li>
                        <li>Apabila Pihak Kedua memutuskan mengakhiri Perjanjian ini lebih awal, maka Pihak Kedua wajib memberitahukan Pihak Pertama secara tertulis paling lambat 30 (tiga puluh) hari sebelum tanggal efektif pengakhiran.</li>
                        <li>Apabila kerja sama dihentikan, akun partner akan dikembalikan ke <strong>Paket Free</strong>, dan seluruh hak akses Pro akan dicabut otomatis oleh sistem.</li>
                        <li>Para Pihak setuju dan sepakat untuk mengesampingkan berlakunya ketentuan Pasal 1266 Kitab Undang-Undang Hukum Perdata terhadap Perjanjian ini dalam hal diperlukan suatu putusan pengadilan untuk mengakhiri Perjanjian ini.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>ARTICLE 8</strong></h6>
                    <h6><strong>EVALUATION & TERMINATION OF AGREEMENT</strong></h6>
                    <p>
                        The Parties agree that the implementation of this cooperation shall be periodically evaluated by the First Party to assess its effectiveness, compliance with the terms of this Agreement, and achievement of the expected results.
                    </p>
                    <p>
                        During each evaluation period, the First Party shall review the performance of the Second Party to ensure the continuity and improvement of the cooperation, taking into account the following indicators:
                    </p>
                    <ol>
                        <li>The number of Free account registrations;</li>
                        <li>The percentage of participants upgrading to paid packages;</li>
                        <li>The total commissions generated;</li>
                        <li>The activity level of accounts registered through the affiliate link;</li>
                        <li>Feedback and outcomes from training sessions conducted.</li>
                    </ol>
                    <p>
                        The Parties agree that this Agreement may be terminated prior to the expiration of its term if certain conditions arise that prevent the continuation of the cooperation, whether due to a breach of contractual terms, legal reasons, or based on the evaluation results conducted by the First Party, as stipulated in this Article.
                    </p>
                    <ol>
                        <li>The First party may terminate this Agreement without any notice to the Second Party, if:
                            <ol type="a">
                                <li>The Second Party violates the terms and/or provisions of this Agreement; and</li>
                                <li>There has been a legal violation committed by the Second Party, which results in the Second Party being unable to fulfill its obligations under this Agreement.</li>
                            </ol>
                        </li>
                        <li>When the Second Party decides to terminate this Agreement earlier, the Second Party should notify the First Party through written letter at least 30 (thirty) days prior to the effective termination date.</li>
                        <li>In the event the partnership is terminated, the partner's account shall be reverted to the <strong>Free Package</strong>, and all Pro access rights shall be automatically revoked by the system.</li>
                        <li>The Parties agree and consent to exclude the application of the provisions of Article 1266 of the Indonesian Civil Code to this Agreement, in the event that a court decision is required to terminate this Agreement.</li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 9 - FORCE MAJEURE -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>PASAL 9</strong></h6>
                    <h6><strong>FORCE MAJEURE</strong></h6>
                    <ol>
                        <li>Yang dimaksud dengan force majeure dalam Perjanjian ini adalah peristiwa yang terjadi di luar kemampuan Para Pihak untuk mengatasinya, dan bukan disebabkan karena kesalahan ataupun kelalaian Para Pihak, seperti antara lain, bencana alam, kebakaran, peperangan, huru hara, pemberontakan, wabah, epidemi, pandemi, sabotase, dan tindakan pemerintah di bidang moneter, yang secara langsung mengganggu pelaksanaan kewajiban Para Pihak dalam Perjanjian ini dan dinyatakan oleh Pemerintah sebagai force majeure.</li>
                        <li>Apabila terjadi force majeure sebagaimana di maksud dalam ayat 1 di atas, maka Pihak yang berada dalam keadaan memaksa berkewajiban memberitahukan Pihak lainnya dalam waktu selambat-lambatnya 7 (tujuh) hari kalender.</li>
                        <li>Force majeure sebagaimana dimaksud dalam Pasal ini tidak menghapuskan atau mengakhiri Perjanjian ini serta Para Pihak wajib menyelesaikan kewajibannya masing-masing.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>ARTICLE 9</strong></h6>
                    <h6><strong>FORCE MAJEURE</strong></h6>
                    <ol>
                        <li>Force majeure in this Agreement means an event that occurs beyond the ability of the Parties to overcome it, and is not caused by the fault or negligence of the Parties, such as, among others, natural disasters, fires, wars, riots, rebellions, plagues, epidemics, pandemics, sabotage, and government actions in the monetary sector, which directly disrupt the implementation of the Parties' obligations in this Agreement and are declared by the Government as force majeure.</li>
                        <li>In the event of force majeure as in paragraph 1 above, the Party in force must notify the other Party by 7 (seven) calendar days.</li>
                        <li>Force majeure, as referred to in this Article, does not cancel or terminate this Agreement, and the Parties are still obligated to fulfill their respective obligations.</li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 10 - KORESPONDENSI -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>PASAL 10</strong></h6>
                    <h6><strong>KORESPONDENSI</strong></h6>
                    <p>
                        Setiap pemberitahuan dan komunikasi yang dibuat berdasarkan Perjanjian ini harus dibuat secara tertulis dan memberitahukan kepada masing-masing Pihak dengan surat elektronik (email) atau surat tertulis ke alamat sebagai berikut:
                    </p>

                    <p><strong>1. Pihak Pertama:</strong></p>
                    <p class="noMargin">A. Nama: {{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : '[**]' }}</p>
                    <p class="noMargin">B. Alamat: {{ isset($agreementLetter->custom_fields['custom_first_party_address']) ? e($agreementLetter->custom_fields['custom_first_party_address']) : '[**]' }}</p>
                    <p class="noMargin">C. Telephone: {{ isset($agreementLetter->custom_fields['custom_first_party_telephone']) ? e($agreementLetter->custom_fields['custom_first_party_telephone']) : '[**]' }}</p>
                    <p class="noMargin">D. Email: {{ isset($agreementLetter->custom_fields['custom_first_party_email']) ? e($agreementLetter->custom_fields['custom_first_party_email']) : '[**]' }}</p>
                    <p class="margin">E. Up: {{ isset($agreementLetter->custom_fields['custom_first_party_up']) ? e($agreementLetter->custom_fields['custom_first_party_up']) : '[**]' }}</p>

                    <p><strong>2. Pihak Kedua:</strong></p>
                    <p class="noMargin">A. Nama: {{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</p>
                    <p class="noMargin">B. Alamat: {{ isset($agreementLetter->custom_fields['custom_second_party_address']) ? e($agreementLetter->custom_fields['custom_second_party_address']) : '[**]' }}</p>
                    <p class="noMargin">C. Telephone: {{ isset($agreementLetter->custom_fields['custom_second_party_telephone']) ? e($agreementLetter->custom_fields['custom_second_party_telephone']) : '[**]' }}</p>
                    <p class="noMargin">D. Email: {{ isset($agreementLetter->custom_fields['custom_second_party_email']) ? e($agreementLetter->custom_fields['custom_second_party_email']) : '[**]' }}</p>
                    <p class="margin">E. Up: {{ isset($agreementLetter->custom_fields['custom_second_party_up']) ? e($agreementLetter->custom_fields['custom_second_party_up']) : '[**]' }}</p>

                    <p>Atau kepada alamat lain atau nomor lain sebagaimana diberitahukan dari waktu ke waktu oleh masing-masing Pihak kepada Pihak lainnya dengan cara sebagaimana disebutkan di atas.</p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>ARTICLE 10</strong></h6>
                    <h6><strong>CORRESPONDENCE</strong></h6>
                    <p>
                        Any notice and communication made under this Agreement must be made in writing and notified to each Party via electronic mail (email) or written letter at the following address:
                    </p>

                    <p><strong>1. First Party:</strong></p>
                    <p class="noMargin">A. Name: {{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : '[**]' }}</p>
                    <p class="noMargin">B. Address: {{ isset($agreementLetter->custom_fields['custom_first_party_address']) ? e($agreementLetter->custom_fields['custom_first_party_address']) : '[**]' }}</p>
                    <p class="noMargin">C. Telephone: {{ isset($agreementLetter->custom_fields['custom_first_party_telephone']) ? e($agreementLetter->custom_fields['custom_first_party_telephone']) : '[**]' }}</p>
                    <p class="noMargin">D. Email: {{ isset($agreementLetter->custom_fields['custom_first_party_email']) ? e($agreementLetter->custom_fields['custom_first_party_email']) : '[**]' }}</p>
                    <p class="margin">E. Up: {{ isset($agreementLetter->custom_fields['custom_first_party_up']) ? e($agreementLetter->custom_fields['custom_first_party_up']) : '[**]' }}</p>

                    <p><strong>2. Second Party:</strong></p>
                    <p class="noMargin">A. Name: {{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</p>
                    <p class="noMargin">B. Address: {{ isset($agreementLetter->custom_fields['custom_second_party_address']) ? e($agreementLetter->custom_fields['custom_second_party_address']) : '[**]' }}</p>
                    <p class="noMargin">C. Telephone: {{ isset($agreementLetter->custom_fields['custom_second_party_telephone']) ? e($agreementLetter->custom_fields['custom_second_party_telephone']) : '[**]' }}</p>
                    <p class="noMargin">D. Email: {{ isset($agreementLetter->custom_fields['custom_second_party_email']) ? e($agreementLetter->custom_fields['custom_second_party_email']) : '[**]' }}</p>
                    <p class="margin">E. Up: {{ isset($agreementLetter->custom_fields['custom_second_party_up']) ? e($agreementLetter->custom_fields['custom_second_party_up']) : '[**]' }}</p>

                    <p>Or to another address or other number as notified from time to time by each Party to the other Party in the manner as stated above.</p>
                </div>
            </div>

            <!-- PASAL 11 - KETERPISAHAN -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>PASAL 11</strong></h6>
                    <h6><strong>KETERPISAHAN</strong></h6>
                    <ol>
                        <li>Apabila sebagian Pasal dalam Perjanjian ini batal demi hukum atau dibatalkan, maka pembatalan itu tidak akan membatalkan isi Pasal-Pasal lainnya atau tidak membatalkan Perjanjian ini.</li>
                        <li>Ketidakberlakuan pasal dan ketentuan tersebut sebagaimana dimaksud pada Pasal 11.1 ini, tidak akan mempengaruhi berlakunya atau dapat dilaksanakannya setiap ketentuan lainnya dari Perjanjian ini dan Para Pihak akan segera melakukan negosiasi untuk ketentuan pengganti, jika diperlukan, yang akan dituangkan dalam Adendum yang menjadi bagian tak terpisahkan dari Perjanjian ini.</li>
                        <li>Apabila seluruh isi Pasal dalam Perjanjian ini dibatalkan, maka tidak akan membatalkan Pasal Pengakhiran Perjanjian, Pasal mengenai Hukum Yang Berlaku dan Penyelesaian Perselisihan, Pasal Korespondensi dan Pasal Keterpisahan ini.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>ARTICLE 11</strong></h6>
                    <h6><strong>SEVERABILITY</strong></h6>
                    <ol>
                        <li>If some of the Articles in this Agreement are null and void or cancelled, then the cancellation will not cancel the contents of the other Articles or cancel this Agreement.</li>
                        <li>The invalidity of the articles and provisions as referred to in Article 11.1, will not affect the validity or enforceability of any other provisions of this Agreement and the Parties will immediately negotiate for replacement provisions, if necessary, which will be stated in an Addendum which is an integral part of this Agreement.</li>
                        <li>If the entire contents of the Articles in this Agreement are cancelled, then it will not cancel the Termination of Agreement Article, the Article on Governing Law and Dispute Resolution, the Correspondence Article and this Severability Article.</li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 12 - HUKUM YANG BERLAKU DAN PENYELESAIAN PERSELISIHAN -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>PASAL 12</strong></h6>
                    <h6><strong>HUKUM YANG BERLAKU DAN PENYELESAIAN PERSELISIHAN</strong></h6>
                    <ol>
                        <li>Pelaksanaan Perjanjian ini tunduk pada ketentuan dan peraturan perundang-undangan yang berlaku menurut Hukum Republik Indonesia.</li>
                        <li>Dalam hal terjadi perselisihan di antara Para Pihak mengenai pelaksanaan Perjanjian ini, maka Para Pihak dengan didasari itikad baik sepakat untuk menyelesaikannya secara musyawarah untuk mufakat.</li>
                        <li>Dalam hal Para Pihak tidak dapat menyelesaikan sengketa(-sengketa) dalam waktu 30 (tiga puluh) hari sejak tanggal suatu sengketa tersebut diajukan oleh suatu Pihak dan diberitahukan kepada Pihak lainnya (atau suatu jangka waktu lain yang disepakati bersama antara Para Pihak), sengketa harus diajukan ke dan secara final diselesaikan melalui Pengadilan Negeri Jakarta Barat.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>ARTICLE 12</strong></h6>
                    <h6><strong>GOVERNING LAW AND DISPUTE RESOLUTION</strong></h6>
                    <ol>
                        <li>The implementation of this Agreement is subject to the provisions and regulations applicable according to the Laws of the Republic of Indonesia.</li>
                        <li>In the event of a dispute between the Parties regarding the implementation of this Agreement, the Parties in good faith agree to resolve it through deliberation to reach a consensus.</li>
                        <li>In the event that the Parties are unable to resolve the dispute(s) within 30 (thirty) days from the date a dispute is submitted by a Party and notified to the other Party (or another period mutually agreed upon by the Parties), the dispute must be submitted to and finally resolved through the West Jakarta District Court.</li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 13 - LAMPIRAN-LAMPIRAN -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>PASAL 13</strong></h6>
                    <h6><strong>LAMPIRAN-LAMPIRAN</strong></h6>
                    <p>Lampiran-lampiran yang menjadi bagian tidak terpisahkan dari perjanjian ini meliputi:</p>
                    <ol>
                        <li>Lampiran 1 - Struktur Komisi & Benefit Partner</li>
                        <li>Lampiran 2 - Panduan Partner Edukasi n8n</li>
                        <li>Lampiran 3 - Formulir Aktivasi Partner</li>
                        <li>Lampiran 4 - Daftar Partner & Periode Kerja Sama</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>ARTICLE 13</strong></h6>
                    <h6><strong>APPENDIX</strong></h6>
                    <p>The appendices forming an integral part of this Agreement include:</p>
                    <ol>
                        <li>Appendix 1 - Commission Structure & Partner Benefits</li>
                        <li>Appendix 2 - n8n Education Partner Guidelines</li>
                        <li>Appendix 3 - Partner Activation Form</li>
                        <li>Appendix 4 - List of Partners & Cooperation Periods</li>
                    </ol>
                </div>
            </div>

            <!-- PASAL 14 - LAIN LAIN -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>PASAL 14</strong></h6>
                    <h6><strong>LAIN LAIN</strong></h6>
                    <ol>
                        <li>Setiap perubahan yang akan dilakukan serta hal-hal yang belum cukup diatur dalam Perjanjian ini akan ditetapkan kemudian secara musyawarah oleh Para Pihak serta akan dituangkan dalam perjanjian tambahan (addendum), atau perjanjian pembaruannya/perubahannya yang disepakati oleh Para Pihak dan merupakan suatu kesatuan dan bagian yang tidak dapat dipisahkan dari Perjanjian ini.</li>
                        <li>Seluruh addendum atau perjanjian pembaruannya/perubahannya atas Perjanjian ini sah apabila ditandatangani oleh Para Pihak.</li>
                        <li>Apabila berlaku peraturan perundang-undangan atau kebijakan pemerintah terhadap Perjanjian ini, maka Para Pihak akan tunduk pada peraturan perundang-undangan atau kebijakan pemerintah tersebut.</li>
                        <li>Semua surat-surat, dokumen-dokumen yang menjadi lampiran yang disebutkan dan turut disertakan dalam Perjanjian ini atau lampiran-lampiran/perjanjian tambahan yang akan dibuat pada waktunya nanti di kemudian hari oleh Para Pihak merupakan satu kesatuan dan bagian yang tidak dapat dipisahkan dari Perjanjian ini.</li>
                    </ol>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>ARTICLE 14</strong></h6>
                    <h6><strong>OTHERS</strong></h6>
                    <ol>
                        <li>Any changes to be made and matters that are not sufficiently regulated in this Agreement will be determined later through deliberation by the Parties and will be stated in an additional agreement (addendum), or an agreement for renewal/amendment thereof agreed upon by the Parties and will constitute an integral and inseparable part of this Agreement.</li>
                        <li>All addendums or agreements for renewal/amendment thereof to this Agreement are valid if signed by the Parties.</li>
                        <li>If applicable laws or government policies apply to this Agreement, then the Parties will be subject to such regulations or government policies.</li>
                        <li>All letters, documents which are attachments mentioned and included in this Agreement or attachments/additional agreements which will be made at the time by the Parties are an integral part and cannot be separated from this Agreement.</li>
                    </ol>
                </div>
            </div>

             <!-- PENUTUP -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <p>
                        Demikian perjanjian ini dibuat dan ditandatangani pada hari dan tanggal sebagaimana tersebut diatas, dibuat rangkap 2 (dua) masing-masing bermeterai cukup serta memiliki kekuatan hukum yang mengikat.
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        Thus this agreement is made and signed on the day and date as stated above, made in 2 (two) copies, each with stamped duty and having the same legal binding.
                    </p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <p>
                        Demikian perjanjian ini dibuat dan ditandatangani pada hari dan tanggal sebagaimana tersebut diatas, dibuat rangkap 2 (dua) masing-masing bermeterai cukup serta memiliki kekuatan hukum yang mengikat.
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p>
                        Thus this agreement is made and signed on the day and date as stated above, made in 2 (two) copies, each with stamped duty and having the same legal binding.
                    </p>
                </div>
            </div>
            
            <div class="row mt-4 mb-1">
                <div class="col-5 text-justify">
                    <h6>
                        <strong>
                            PENANDATANGANAN
                        </strong>
                    </h6>
                    <p>
                        Perjanjian ini ditandatangani secara elektronik pada tanggal {{ \Carbon\Carbon::parse($agreementLetter->date)->format('d F Y') }} oleh para pihak di bawah ini:
                    </p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6>
                        <strong>
                            SIGNATURES
                        </strong>
                    </h6>
                    <p>
                        This Agreement is executed electronically on {{ \Carbon\Carbon::parse($agreementLetter->date)->format('d F Y') }} by the parties below:
                    </p>
                </div>
            </div>

            <!-- Tanda Tangan -->
            <div class="row mt-0 mb-2">
                <div class="col-5 text-justify">
                    <p class="noMargin"><strong>PIHAK PERTAMA / FIRST PARTY</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : '[**]' }}</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p class="noMargin"><strong>PIHAK KEDUA / SECOND PARTY</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}</strong></p>
                </div>
            </div>
            <div class="row mt-4 mb-2">
                <div class="col-3 text-justify d-flex justify-content-center align-items-center">
                    @if(file_exists(public_path('logo/paraf.png')))
                    <img src="{{ asset('logo/paraf.png') }}" alt="Signature" style="width:auto; height:150px" class="left-aligned-image">
                    @endif
                </div>
                <div class="offset-2 col-5 text-justify">
                </div>
            </div>

            <div class="row mt-5 mb-2">
                <div class="col-5 text-justify">
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_first_party_represented_by']) ? e($agreementLetter->custom_fields['custom_first_party_represented_by']) : '[**]' }}</strong></p>
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_first_party_position']) ? e($agreementLetter->custom_fields['custom_first_party_position']) : '[**]' }}</strong></p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <p class="noMargin"><strong>
                        @if(isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'company')
                            {{ isset($agreementLetter->custom_fields['custom_second_party_represented_by']) ? e($agreementLetter->custom_fields['custom_second_party_represented_by']) : (isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]') }}
                        @else
                            {{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '[**]' }}
                        @endif
                    </strong></p>
                    @if(isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'company')
                    <p class="noMargin"><strong>{{ isset($agreementLetter->custom_fields['custom_second_party_position']) ? e($agreementLetter->custom_fields['custom_second_party_position']) : '' }}</strong></p>
                    @endif
                </div>
            </div>

            <div class="page-break-after"></div>
            <!-- LAMPIRAN 1 -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>LAMPIRAN 1 - STRUKTUR KOMISI & BENEFIT PARTNER</strong></h6>
                    <ul>
                        <li>Komisi: {{ isset($agreementLetter->custom_fields['custom_commission_percentage']) ? $agreementLetter->custom_fields['custom_commission_percentage'] : '20' }}% dari setiap pembelian akun n8n berbayar.</li>
                        <li>Ambang penarikan: {{ isset($agreementLetter->custom_fields['custom_minimum_withdrawal_amount']) ? 'Rp' . number_format($agreementLetter->custom_fields['custom_minimum_withdrawal_amount'], 0, ',', '.') : 'Rp500.000' }}.</li>
                        <li>Benefit tambahan: Akun n8n Pro gratis senilai Rp85.000/bulan.</li>
                    </ul>

                    <h6 class="mt-3"><strong>LAMPIRAN 3 - FORMULIR AKTIVASI PARTNER</strong></h6>
                    <p class="noMargin">Nama Partner: {{ isset($agreementLetter->custom_fields['custom_partner_name']) ? e($agreementLetter->custom_fields['custom_partner_name']) : '_____' }}</p>
                    <p class="noMargin">Email: {{ isset($agreementLetter->custom_fields['custom_partner_email']) ? e($agreementLetter->custom_fields['custom_partner_email']) : '_____' }}</p>
                    <p class="noMargin">Nomor Telepon: {{ isset($agreementLetter->custom_fields['custom_partner_phone']) ? e($agreementLetter->custom_fields['custom_partner_phone']) : '_____' }}</p>
                    <p class="noMargin">Tanggal Mulai: {{ isset($agreementLetter->custom_fields['custom_cooperation_start_date']) ? \Carbon\Carbon::parse($agreementLetter->custom_fields['custom_cooperation_start_date'])->format('d F Y') : '_____' }}</p>
                    <p class="noMargin">Kode Referral: {{ isset($agreementLetter->custom_fields['custom_referral_code']) ? e($agreementLetter->custom_fields['custom_referral_code']) : '_____' }}</p>
                    <p class="noMargin">Bank / E-Wallet: {{ isset($agreementLetter->custom_fields['custom_bank_ewallet_name']) ? e($agreementLetter->custom_fields['custom_bank_ewallet_name']) : '_____' }}</p>
                    <p class="margin">Nomor Rekening: {{ isset($agreementLetter->custom_fields['custom_account_number']) ? e($agreementLetter->custom_fields['custom_account_number']) : '_____' }}</p>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>APPENDIX 1 - COMMISSION STRUCTURE & PARTNER BENEFITS</strong></h6>
                    <ul>
                        <li>Commission: {{ isset($agreementLetter->custom_fields['custom_commission_percentage']) ? $agreementLetter->custom_fields['custom_commission_percentage'] : '20' }}% for every paid n8n account purchase.</li>
                        <li>Withdrawal threshold: {{ isset($agreementLetter->custom_fields['custom_minimum_withdrawal_amount']) ? 'IDR ' . number_format($agreementLetter->custom_fields['custom_minimum_withdrawal_amount'], 0, ',', '.') : 'IDR 500,000' }}.</li>
                        <li>Additional benefit: Free n8n Pro account worth IDR 85,000/month.</li>
                    </ul>

                    <h6 class="mt-3"><strong>APPENDIX 3 - PARTNER ACTIVATION FORM</strong></h6>
                    <p class="noMargin">Partner Name: {{ isset($agreementLetter->custom_fields['custom_partner_name']) ? e($agreementLetter->custom_fields['custom_partner_name']) : '_____' }}</p>
                    <p class="noMargin">Email: {{ isset($agreementLetter->custom_fields['custom_partner_email']) ? e($agreementLetter->custom_fields['custom_partner_email']) : '_____' }}</p>
                    <p class="noMargin">Phone: {{ isset($agreementLetter->custom_fields['custom_partner_phone']) ? e($agreementLetter->custom_fields['custom_partner_phone']) : '_____' }}</p>
                    <p class="noMargin">Start Date: {{ isset($agreementLetter->custom_fields['custom_cooperation_start_date']) ? \Carbon\Carbon::parse($agreementLetter->custom_fields['custom_cooperation_start_date'])->format('d F Y') : '_____' }}</p>
                    <p class="noMargin">Referral Code: {{ isset($agreementLetter->custom_fields['custom_referral_code']) ? e($agreementLetter->custom_fields['custom_referral_code']) : '_____' }}</p>
                    <p class="noMargin">Bank / E-Wallet: {{ isset($agreementLetter->custom_fields['custom_bank_ewallet_name']) ? e($agreementLetter->custom_fields['custom_bank_ewallet_name']) : '_____' }}</p>
                    <p class="margin">Account Number: {{ isset($agreementLetter->custom_fields['custom_account_number']) ? e($agreementLetter->custom_fields['custom_account_number']) : '_____' }}</p>
                </div>
            </div>
            <!-- LAMPIRAN 2 - PANDUAN PARTNER EDUKASI N8N -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>LAMPIRAN 2 - PANDUAN PARTNER EDUKASI N8N</strong></h6>
                    <p><strong>Partner wajib:</strong></p>
                    <ul>
                        <li>Mengarahkan peserta untuk mendaftar melalui link/kode afiliasi.</li>
                        <li>Menggunakan materi resmi dan panduan dari PT Gema.</li>
                        <li>Melaporkan pelaksanaan kegiatan.</li>
                    </ul>
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>APPENDIX 2 - N8N EDUCATION PARTNER GUIDELINES</strong></h6>
                    <p><strong>Partner must:</strong></p>
                    <ul>
                        <li>Direct participants to register via the provided affiliate link/code.</li>
                        <li>Use official materials and guidelines from GTCG.</li>
                        <li>Submit activity reports accordingly.</li>
                    </ul>
                </div>
            </div>

            <!-- LAMPIRAN 4 - DAFTAR PARTNER & PERIODE KERJA SAMA -->
            <!-- LAMPIRAN 4 - DAFTAR PARTNER & PERIODE KERJA SAMA (Updated) -->
            <div class="row mt-4">
                <div class="col-5 text-justify">
                    <h6><strong>LAMPIRAN 4 - DAFTAR PARTNER & PERIODE KERJA SAMA</strong></h6>
                    @php
                        $partnerList = isset($agreementLetter->custom_fields['custom_partner_list']) 
                            ? json_decode($agreementLetter->custom_fields['custom_partner_list'], true) 
                            : [];
                    @endphp
                    
                    @if(!empty($partnerList))
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th width="8%">No</th>
                                <th width="30%">Nama Partner</th>
                                <th width="25%">Lokasi</th>
                                <th width="25%">Periode</th>
                                <th width="12%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($partnerList as $index => $partner)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $partner['name'] ?? '_____' }}</td>
                                <td>{{ $partner['location'] ?? '_____' }}</td>
                                <td>{{ $partner['period'] ?? '_____' }}</td>
                                <td>{{ $partner['status'] ?? 'Aktif' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th width="8%">No</th>
                                <th width="30%">Nama Partner</th>
                                <th width="25%">Lokasi</th>
                                <th width="25%">Periode</th>
                                <th width="12%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">1</td>
                                <td>_____</td>
                                <td>_____</td>
                                <td>_____</td>
                                <td>Aktif</td>
                            </tr>
                        </tbody>
                    </table>
                    @endif
                </div>
                <div class="offset-2 col-5 text-justify">
                    <h6><strong>APPENDIX 4 - LIST OF PARTNERS & COOPERATION PERIODS</strong></h6>
                    @php
                        $partnerList = isset($agreementLetter->custom_fields['custom_partner_list']) 
                            ? json_decode($agreementLetter->custom_fields['custom_partner_list'], true) 
                            : [];
                    @endphp
                    
                    @if(!empty($partnerList))
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th width="8%">No</th>
                                <th width="30%">Partner Name</th>
                                <th width="25%">Location</th>
                                <th width="25%">Period</th>
                                <th width="12%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($partnerList as $index => $partner)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $partner['name'] ?? '______' }}</td>
                                <td>{{ $partner['location'] ?? '______' }}</td>
                                <td>{{ $partner['period'] ?? '______' }}</td>
                                <td>{{ $partner['status'] == 'Aktif' ? 'Active' : ($partner['status'] == 'Tidak Aktif' ? 'Inactive' : 'Completed') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th width="8%">No</th>
                                <th width="30%">Partner Name</th>
                                <th width="25%">Location</th>
                                <th width="25%">Period</th>
                                <th width="12%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">1</td>
                                <td>______</td>
                                <td>______</td>
                                <td>______</td>
                                <td>Active</td>
                            </tr>
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 text-center mt-3">
        <a href="{{ route('agreement-letter.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali</a>
        <a href="{{ route('agreement-letter.edit', $agreementLetter) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Edit</a>
        <button type="button" id="downloadAgreement" class="btn btn-success"><i class="fa fa-file-pdf"></i> Download</button>
    </div>
</div>
@stop

@section('css')
<style>
    h6
    {
        text-align: center;
    }
    @media print {
        #printItem {
            margin-left: 50px;
            margin-right: 50px;
        }
        .page-break-after {
            page-break-after: always;
        }
         .table-sm 
         {
            font-size: 0.8rem;
        }
        .table-sm th, .table-sm td {
            padding: 0.3rem;
        }
    }
    body {
        font-family: Arial, sans-serif;
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
    .text-justify {
        text-align: justify;
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
        let name = "{{ $agreementLetter->number_result ?? 'perjanjian' }}_n8n_partnership";
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