@extends('adminlte::page')

@section('title', 'TANYA B.O.S')

@section('content_header')
    <h1 class="text-center">TANYA <b>B.O.S</b></h1>
@stop

@section('content')
@include('components.alert')
<div class="row">
    <div class="col-md-12">
        <!-- Textarea -->
        <div class="mb-4">
            <textarea id="questionInput" class="form-control" rows="4" placeholder="Tulis pertanyaan Anda di sini"></textarea>
        </div>

        <!-- Buttons -->
        <div class="text-center mb-4">
            @canAccess('ask','ask_bos')
            <button id="askButton" class="btn btn-outline-primary me-3">
                <i class="fas fa-question-circle"></i> Ask Questions
            </button>
            @endcanAccess

            @canAccess('makeDesition','ask_bos')
            <button id="decisionButton" class="btn btn-outline-secondary">
                <i class="fas fa-balance-scale"></i> Make Decision
            </button>
            @endcanAccess
        </div>

        <!-- Checkbox Filters -->
        <div class="mb-4 d-flex justify-content-center flex-wrap">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="webSearch" value="Web Search" disabled>
                <label class="form-check-label" for="webSearch">Web Search</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="logistics" value="Logistik" disabled>
                <label class="form-check-label" for="logistics">Logistik</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="finance" value="Finance" disabled>
                <label class="form-check-label" for="finance">Finance</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="stock" value="Stok Barang" disabled>
                <label class="form-check-label" for="stock">Stok Barang</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="location" value="Lokasi" disabled>
                <label class="form-check-label" for="location">Lokasi</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="project" value="Project" disabled>
                <label class="form-check-label" for="project">Project</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="marketing" value="Marketing" disabled>
                <label class="form-check-label" for="marketing">Marketing</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="salesQuote" value="Sales Quote" disabled>
                <label class="form-check-label" for="salesQuote">Sales Quote</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="bigData" value="Big Data" disabled>
                <label class="form-check-label" for="bigData">Big Data</label>
            </div>
        </div>

        <!-- Results -->
         @canAccess('checkResponse','ask_bos')
         <form action="{{ route('decision.store') }}" method="post">
            @csrf
            <div class="row">
                <div class="col-md-12">
                    <div id="resultSection" class="mt-5">
                        <h3 class="text-primary mb-3">Hasil Analisa</h3>
        
                        <input type="hidden" name="responsible" id="responsibleResult" />
                        <input type="hidden" name="accountable" id="accountableResult" />
                        <input type="hidden" name="consult" id="consultResult" />
                        <input type="hidden" name="question" id="questionResult"/>
                        <input type="hidden" name="analysisResult" id="analysisResultSave"/>
                        <input type="hidden" name="trustScoreResult" id="trustScoreResultSave"/>
                        <input type="hidden" name="executionScoreResult" id="executionScoreResultSave"/>
                        <input type="hidden" name="nominal" id="nominalSave"/>
                        <input type="hidden" name="consultVendor" id="consultVendorSave"/>
        
                        <ul class="list-group">
                            <li class="list-group-item">
                                <b>Analisis:</b>
                                <div id="analysisResult" style="white-space: pre-line; margin-top: 4px;">-</div>
                            </li>
                            <li class="list-group-item"><b>Trust Score:</b> <span id="trustScoreResult">-</span></li>
                            <li class="list-group-item"><b>Execution Score:</b> <span id="executionScoreResult">-</span></li>
                        </ul>

                        {{-- Tombol Reload: fallback manual jika broadcast tidak diterima --}}
                        <div class="mt-3 text-right" id="reloadWrapper" style="display:none;">
                            <button type="button" id="reloadBtn" class="btn btn-sm btn-outline-warning"
                                    onclick="fetchFromCache()" title="Gunakan jika hasil tidak muncul otomatis">
                                <i class="fas fa-sync-alt mr-1"></i> Reload Hasil
                            </button>
                            <small class="text-muted ml-2">Gunakan jika hasil tidak muncul otomatis</small>
                        </div>

                     @canAccess('store','decisions')
                    <div class="d-flex justify-content-end mt-2">
                        <button type="submit" id="submitDecision" class="btn btn-primary" style="display:none;" onclick="return confirm('Yakin ingin menyimpan keputusan?')">Simpan</button>
                    </div>
                    @endcanAccess
                </div>
        </form>
        @endcanAccess
    </div>
</div>
<!-- Modal for Make Decision -->
<div class="modal fade" id="makeDecisionModal" tabindex="-1" aria-labelledby="makeDecisionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="makeDecisionModalLabel">Make Decision</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @canAccess('store','decisions')
                <form id="decisionForm">
                    {{-- Nilai / Nominal --}}
                    <div class="mb-3">
                        <label for="nominalInput" class="form-label">
                            Nilai Keputusan
                            <small class="text-muted">(opsional — nominal transaksi/anggaran)</small>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="number" class="form-control" id="nominalInput"
                                   placeholder="0" min="0" step="1000">
                        </div>
                        <small id="nominalHint" class="text-muted" style="display:none;">
                            <i class="fas fa-info-circle text-warning"></i>
                            Nilai ini melebihi threshold — Consult diisi dengan nama vendor/pihak luar.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="responsible" class="form-label">Responsible</label>
                        <select class="form-select selectModal2" id="responsible" name="responsible" required>
                            <option value="">Choose Responsible User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ !$user->back_ground_verified ? 'disabled' : '' }}>{{ $user->name }} </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="accountable" class="form-label">Accountable</label>
                        <select class="form-select selectModal2" id="accountable" name="accountable" required>
                            <option value="">Choose Accountable User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ !$user->back_ground_verified ? 'disabled' : '' }}>{{ $user->name }} </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="consultWrapper">
                        <label for="consult" class="form-label">Consult</label>

                        {{-- Default: dropdown user internal --}}
                        <select class="form-select selectModal2" id="consult" name="consult">
                            <option value="">Choose Consult User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ !$user->back_ground_verified ? 'disabled' : '' }}>{{ $user->name }} </option>
                            @endforeach
                        </select>

                        {{-- Vendor luar — muncul jika nominal > threshold --}}
                        <input type="text" class="form-control" id="consultVendor" name="consult_vendor"
                               placeholder="Nama vendor / pihak luar (misal: PT Maju Jaya)"
                               style="display:none;">
                    </div>
                </form>
                @endcanAccess
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeModal">Close</button>
                <button type="button" class="btn btn-primary" id="submitDecisionButton">Submit Decision</button>
            </div>
        </div>
    </div>

</div>
@stop
@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>
<audio id="notification-sound" src="/audio/notification-message-entry.mp3" preload="auto"></audio>
<script>
    // ── Setup Laravel Echo (Reverb) ──────────────────────────────────────────
    const userId = @json(auth()->id());
    const host   = '{{ config('services.connection_reverb.host') }}';
    const key    = '{{ config('services.connection_reverb.key') }}';
    const port   = '{{ config('services.connection_reverb.port') }}';

    window.Pusher = Pusher;
    window.Echo = new Echo.default({
        broadcaster       : 'reverb',
        key               : key,
        wsHost            : host,
        wsPort            : 8080,
        wssPort           : port,
        forceTLS          : true,
        enabledTransports : ['ws', 'wss'],
        authEndpoint      : '/broadcasting/authorize',
        disableStats      : true,
    });

    // ── Helper: tampilkan analisa dengan newline rapi ──────────────────────
    function setAnalysis(text) {
        const el = document.getElementById('analysisResult');
        // Ubah literal \n (dari JSON string) menjadi newline sebenarnya
        el.innerText = (text || '-').replace(/\\n/g, '\n');
    }

    // ── Listen hasil AI dari broadcast ──────────────────────────────────────
    // Channel: bos.user.{userId} (public Channel — bukan PrivateChannel)
    // Event  : .bos.response.ready (dot prefix = custom broadcastAs name)
    window.Echo.channel(`bos.user.${userId}`)
        .listen('.bos.response.ready', (e) => {
            setLoading(false);

            // Handle error flag dari backend (job gagal permanen)
            if (e.is_error) {
                setAnalysis(e.analysis || 'Terjadi kesalahan saat memproses.');
                showToast('❌ Gagal mendapatkan hasil analisa.');
                return;
            }

            setAnalysis(e.analysis);
            document.getElementById('trustScoreResult').innerText    = e.trust_score ?? '-';
            document.getElementById('executionScoreResult').innerText = e.execution_score ?? '-';

            document.getElementById('analysisResultSave').value      = e.analysis;
            document.getElementById('trustScoreResultSave').value    = e.trust_score ?? 0;
            document.getElementById('executionScoreResultSave').value = e.execution_score ?? 0;

            if (e.trust_score || e.execution_score) {
                document.getElementById('submitDecision').style.display = 'block';
            }

            // Notifikasi suara
            document.getElementById('notification-sound')?.play();

            // Toast singkat
            showToast('✅ Hasil analisa B.O.S sudah siap!');
        });

    // ── Helper: loading state ────────────────────────────────────────────────
    function setLoading(isLoading) {
        const analysis     = document.getElementById('analysisResult');
        const reloadWrapper = document.getElementById('reloadWrapper');
        if (isLoading) {
            analysis.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sedang memproses, harap tunggu...';
            document.getElementById('trustScoreResult').innerText     = '';
            document.getElementById('executionScoreResult').innerText = '';
            document.getElementById('submitDecision').style.display   = 'none';
            // Tampilkan tombol Reload setelah 15 detik jika broadcast belum datang
            setTimeout(() => { reloadWrapper.style.display = 'block'; }, 15000);
        } else {
            // Hasil sudah diterima — sembunyikan tombol Reload
            reloadWrapper.style.display = 'none';
        }
    }

    // ── Helper: toast notifikasi ─────────────────────────────────────────────
    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'askbos-toast';
        toast.innerText = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 400); }, 3500);
    }

    // ── Fallback: ambil hasil dari cache secara manual ───────────────────────
    function fetchFromCache() {
        const btn = document.getElementById('reloadBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...';

        fetch("{{ route('check.response') }}")
            .then(r => r.json())
            .then(data => {
                if (data.status === 'waiting') {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Reload Hasil';
                    showToast('⏳ Hasil belum tersedia, coba lagi sebentar.');
                    return;
                }

                // Hasil ditemukan di cache — tampilkan
                setLoading(false);
                setAnalysis(data.analysis);
                document.getElementById('trustScoreResult').innerText     = data.trust_score;
                document.getElementById('executionScoreResult').innerText = data.execution_score;
                document.getElementById('analysisResultSave').value       = data.analysis;
                document.getElementById('trustScoreResultSave').value     = data.trust_score;
                document.getElementById('executionScoreResultSave').value = data.execution_score;

                if (data.trust_score !== 0 || data.execution_score !== 0) {
                    document.getElementById('submitDecision').style.display = 'block';
                }
                document.getElementById('notification-sound')?.play();
                showToast('✅ Hasil analisa B.O.S sudah siap!');
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Reload Hasil';
                showToast('❌ Gagal memuat. Coba lagi.');
            });
    }

    // ── Select2 untuk modal ──────────────────────────────────────────────────
    $(document).ready(function () {
        $('.selectModal2').select2({
            dropdownParent: '#makeDecisionModal',
            width         : '100%',
            placeholder   : 'Pilih',
            allowClear    : true
        });
    });


    // ── Nominal threshold dari env ───────────────────────────────────────────
    const NOMINAL_THRESHOLD = {{ config('services.openai.nominal_threshold', 100000000) }};

    // Switch Consult: dropdown → free text vendor jika nominal >= threshold
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('nominalInput')?.addEventListener('input', function () {
            const nominal       = parseFloat(this.value) || 0;
            const isHighValue   = nominal >= NOMINAL_THRESHOLD;
            document.getElementById('nominalHint').style.display        = isHighValue ? 'block' : 'none';
            document.getElementById('consult').style.display            = isHighValue ? 'none'  : 'block';
            document.getElementById('consultVendor').style.display      = isHighValue ? 'block' : 'none';
            if (isHighValue) document.getElementById('consult').value       = '';
            else             document.getElementById('consultVendor').value = '';
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const askButton            = document.getElementById('askButton');
        const decisionButton       = document.getElementById('decisionButton');
        const submitDecisionButton = document.getElementById('submitDecisionButton');

        document.getElementById('submitDecision').style.display = 'none';

        // ── Ask Questions ──────────────────────────────────────────────────
        askButton.addEventListener('click', function () {
            const question        = document.getElementById('questionInput').value;
            const selectedFilters = Array.from(document.querySelectorAll('input[type="checkbox"]:checked')).map(el => el.value);

            if (!question.trim()) { alert('Silakan masukkan pertanyaan sebelum mengirim.'); return; }

            document.getElementById('questionResult').value = question;
            ['responsibleResult','accountableResult','consultResult'].forEach(id => document.getElementById(id).value = '');
            setLoading(true);

            fetch("{{ route('ask.bos') }}", {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body   : JSON.stringify({ question, filters: selectedFilters })
            })
            .then(r => r.json())
            .catch(() => {
                setAnalysis('Terjadi kesalahan saat memproses.');
                setLoading(false);
            });
        });

        // ── Make Decision — buka modal ─────────────────────────────────────
        decisionButton.addEventListener('click', function () {
            const modal = new bootstrap.Modal(document.getElementById('makeDecisionModal'));
            modal.show();
        });

        // ── Make Decision — submit ─────────────────────────────────────────
        submitDecisionButton.addEventListener('click', function () {
            const responsible   = document.getElementById('responsible').value;
            const accountable   = document.getElementById('accountable').value;
            const question      = document.getElementById('questionInput').value;
            const nominal       = parseFloat(document.getElementById('nominalInput').value) || 0;
            const isHighValue   = nominal >= NOMINAL_THRESHOLD;

            // Consult: dropdown user internal ATAU free text vendor luar
            const consult       = isHighValue ? '' : document.getElementById('consult').value;
            const consultVendor = isHighValue ? document.getElementById('consultVendor').value.trim() : '';

            if (!question.trim())              { alert('Silakan masukkan pertanyaan sebelum mengirim.'); return; }
            if (!responsible || !accountable)  { alert('Please select Responsible dan Accountable.'); return; }
            if (isHighValue && !consultVendor) { alert('Nilai melebihi threshold — nama vendor wajib diisi.'); return; }

            document.getElementById('questionResult').value    = question;
            document.getElementById('responsibleResult').value = responsible;
            document.getElementById('accountableResult').value = accountable;
            document.getElementById('consultResult').value     = consult;
            // Simpan ke hidden field form → akan ikut tersubmit ke decision.store
            document.getElementById('nominalSave').value       = nominal || '';
            document.getElementById('consultVendorSave').value = consultVendor || '';

            setLoading(true);
            document.getElementById('closeModal').click();

            fetch("{{ route('ask.makeDesition') }}", {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body   : JSON.stringify({ question, responsible, accountable, consult, consult_vendor: consultVendor, nominal })
            })
            .then(r => r.json())
            .catch(() => {
                document.getElementById('analysisResult').innerText = 'Terjadi kesalahan saat memproses.';
                setLoading(false);
            });
        });
    });

</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>
    .form-check-inline { margin-right: 15px; }

    .select2-selection__rendered { line-height: 31px !important; }
    .select2-container .select2-selection--single { height: 35px !important; }
    .select2-selection__arrow { height: 34px !important; }
    hr { border: 1px solid black; border-radius: 5px; }

    /* ── Toast AskBos ─────────────────────────────────── */
    .askbos-toast {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #28a745;
        color: #fff;
        padding: 14px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 4px 16px rgba(0,0,0,.25);
        opacity: 0;
        transform: translateY(20px);
        transition: opacity .3s, transform .3s;
        z-index: 9999;
    }
    .askbos-toast.show {
        opacity: 1;
        transform: translateY(0);
    }
</style>
@stop