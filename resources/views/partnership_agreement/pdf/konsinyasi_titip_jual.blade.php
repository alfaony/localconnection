
<div class="card-body" id="printItem">
    <div class="row mb-4">
        <div class="col-6 pe-3 text-center">
            <h6><strong>PERJANJIAN KONSINYASI</strong> <strong>ANTARA {{ $agreement->getFields(" nama_perusahaan_pertama") }} DENGAN {{ $agreement->getFields(" nama_perusahaan_pihak_kedua") }}</h6> 
            <h6>No: {{ $agreement->number_result }}</strong></h6>
        </div>
        <div class="col-6 ps-3 text-center">
            <h6><strong>CONSIGNMENT AGREEMENT</strong> <strong>BETWEEN {{ $agreement->getFields(" nama_perusahaan_pertama") }} WITH {{ $agreement->getFields(" nama_perusahaan_pihak_kedua") }}</h6>
            <h6>No: {{ $agreement->number_result }}</strong></h6>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <p>
                Perjanjian Konsinyasi (selanjutnya disebut <strong>“Perjanjian”</strong>) ini
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
                    disebut sebagai <strong>“Pihak Pertama”</strong>);
                </li>
                <li style="margin-bottom: 15px;">
                    <p>
                        {{ $agreement->getFields(" nama_perusahaan_pihak_kedua") }}, suatu perseroan terbatas yang didirikan dan berdiri secara sah berdasarkan hukum
                        Indonesia yang beralamat di {{ $agreement->getFields(" alamat_perusahaan_pihak_kedua") }}, dalam hal ini diwakili oleh {{ $agreement->getFields(" nama_perwakilan_pihak_kedua") }} sebagai {{ $agreement->getFields(" jabatan_perwakilan_pihak_kedua") }}, secara
                        sah bertindak untuk dan atas nama {{ $agreement->getFields(" entitas_di_wakili") }}, (untuk selanjutnya disebut sebagai “Pihak
                        kedua”).
                    </p>
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
                    <p>
                        {{ $agreement->getFields(" nama_perusahaan_pihak_kedua") }}, a limited liability company legally established and standing under Indonesian law
                        domiciled in {{ $agreement->getFields(" alamat_perusahaan_pihak_kedua") }}, in this case represented by {{ $agreement->getFields(" nama_perwakilan_pihak_kedua") }} its capacity as {{ $agreement->getFields(" jabatan_perwakilan_pihak_kedua") }}, therefore
                        acting for and on behalf of {{ $agreement->getFields(" entitas_di_wakili") }}, (hereinafter referred to as the <strong>“Second
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
                Bahwa Pihak Pertama adalah suatu perseroan terbatas yang bergerak sebagai pedagang eceran.
                </li>
                <li style="margin-bottom: 15px;">
                Bahwa Pihak Kedua adalah pihak yang memiliki gerai penjualan.
                </li>
                <li style="margin-bottom: 0x;">
                Bahwa Para Pihak sepakat untuk barang-barang milik Pihak Pertama disimpan di toko Pihak Kedua <strong>(“Proyek”)</strong>.                </li>

            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                Whereas the First Party is a limited liability company engaged as retail.

                </li>
                <li style="margin-bottom: 15px;">
                That the Second Party is the party that has a sales outlet.
                </li>
                <li style="margin-bottom: 0x;">
                That the Parties agree that the goods belonging to the First Party are stored in the Second Party's store <strong>(“Project”)</strong>.

                </li>
            </ol>
        </div>
        <div class="col-6 text-justify mt-0">
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
                Pihak Pertama bermasuk menjual secara konsinyasi barang-barang milik Pihak Pertama sebagaimana dirinci dalam Lampiran yang merupakan bagian yang tidak terpisahkan dari Perjanjian ini (selanjutnya disebut <strong>“Barang Konsinyasi”</strong>) di gerai milik Pihak Kedua dan Pihak Kedua bersedia memajang dan menjual Barang Konsinyasi tersebut di gerainya.
                </li>
                <li style="margin-bottom: 15px;">
                Pihak Kedua wajib mengikuti ketentuan yang ada dalam Perjanjian ini termasuk dan tidak terbatas pada syarat dan ketentuan yang telah disepakati sebelumnya oleh Para Pihak.
                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                    The First Party wishes to sell by consignment basis at the Second Party’s outlet, the jewelries owned by the First Party as described in the Appendix which attached hereto and constitutes as an inseparable part of this Agreement (hereinafter referred to as the <strong>“Consigned Property”</strong>) and the Second Party agrees to display and to sell the Consigned Property at its outlet.
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
                KETENTUAN HARGA DAN PEMBAYARAN

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
                PRICE PROVISIONS AND PAYMENT

                </strong>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                Harga dari setiap Barang Konsinyasi akan ditentukan oleh Pihak Pertama dan Pihak Kedua berhak atas {{ $agreement->getFields("presentase_konsinyasi") }}% dari setiap penjualan Barang Konsinyasi.
                </li>
                <li style="margin-bottom: 15px;">
                Pihak Kedua setuju untuk menerima harga-harga yang ditentukan oleh Pihak Pertama atas setiap Barang Konsinyasi dan akan menjual sesuai harga tersebut tanpa pengurangan, kecuali ditentukan lain secara tertulis oleh Para Pihak.
                </li>
                <li style="margin-bottom: 15px;">
                Pihak Kedua akan menyerahkan kepada Pihak Pertama seluruh hasil penjualan setelah dikurangi {{ $agreement->getFields("presentase_konsinyasi") }}% sebagaimana tersebut dalam Pasal 2.1, pada saat perwakilan Pihak Pertama mendatangi gerai untuk melakukan perhitungan Barang Konsinyasi.

                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
            <li style="margin-bottom: 15px;">
                The price for each item of the Consigned Property will be determined by the First Party and the Second Party shall entitle to collect {{ $agreement->getFields("presentase_konsinyasi") }}% from each sale of the Consigned Property.

                </li>
                <li style="margin-bottom: 15px;">
                The Second Party agrees to receive the prices that are determined by the First Party for each item of the Consigned Property and will sell no less than the set-price, unless otherwise agreed upon in writing by the Parties.

                </li>
                <li style="margin-bottom: 15px;">
                The Second Party shall submit to the First Party the full amount of the sold items, after deduction of {{ $agreement->getFields("presentase_konsinyasi") }}% outlined in Article 2.1, when the representative of the First Party visits the outlet to conduct the calculation of the Consigned Property.

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
                PERNYATAAN DAN JAMINAN
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
                REPRESENTATIONS AND WARRANTIES
                </strong>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                Pihak Kedua menjamin Pihak Pertama bahwa Pihak Kedua akan senantiasa menutup asuransi atas pencurian atau kerugian yang mungkin terjadi di gerainya dan setuju bahwa selama Barang Konsinyasi berada dalam gerai Pihak Kedua, barang tersebut akan terjamin dalam asuransi tersebut.
                </li>
                <li style="margin-bottom: 15px;">
                Pihak Kedua wajib memberikan ganti rugi senilai Barang Konsinyasi yang rusak apabila terbukti Barang Konsinyasi yang disimpan di gerai Pihak Kedua mengalami kerusakan akibat kesalahan dan/atau kelalaian Pihak Kedua.

                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                The Second Party guarantees the First Party that it will always maintain insurance for any theft or damage that may occur over its outlets, and agrees when the Consigned Property is in the Second Party’s outlet, it will be covered by the said insurance.

                </li>
                <li style="margin-bottom: 15px;">
                The Second Party should provide compensation with the value of Consigned Property if it is proven that the Consigned Property stored at the Second Party's outlet has been damaged due to the Second Party's fault and/or negligence.
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
                PENGAKHIRAN PERJANJIAN
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
                TERMINATION OF AGREEMENT
                </strong>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-6 pe-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                Pihak Pertama atas alasan apapun dapat menghentikan Perjanjian setiap dengan melakukan pemberitahuan sebelumnya ke Pihak Kedua.
                </li>
                <li style="margin-bottom: 15px;">
                Pada akhir Perjanjian, Para Pihak akan melakukan evaluasi atas seluruh Barang Konsinyasi yang tidak terjual dan Pihak Kedua wajib mengembalikan seluruh Barang Konsinyasi.

                </li>
                <li style="margin-bottom: 15px;">
                Para Pihak setuju dan sepakat untuk mengesampingkan ketentuan Pasal 1266 KUHPerdata, sepanjang mengenai pengakhiran Perjanjian. 

                </li>
            </ol>
        </div>
        <div class="col-6 ps-3 text-justify">
            <ol>
                <li style="margin-bottom: 15px;">
                The First Party may terminate the Agreement at any time and for any reason with prior notice of termination shall not be required to the Second Party.

                </li>
                <li style="margin-bottom: 15px;">
                At the end of this Agreement, all remaining unsold Consigned Property shall be evaluated by the Parties and the Second Party should be returned the all Consigned Property.
                </li>
                <li style="margin-bottom: 15px;">
                The Parties agree and agree to set aside the provisions of Article 1266 of the Civil Code, as long as it concerns the termination of the Agreement.
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
                PEMBERITAHUAN

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
                NOTIFICATION
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
            <ol>
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
        <div class="col-6 pe-3 text-center">
            <p class="mb-0">
                <strong>
                    PASAL 7
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
                    ARTICLE 7
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
                    PASAL 8
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
                    ARTICLE 8
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
                    PASAL 9
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
                    ARTICLE 9
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