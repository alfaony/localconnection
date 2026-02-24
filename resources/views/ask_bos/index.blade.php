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
        
                        <ul class="list-group">
                            <li class="list-group-item"><b>Analisis:</b> <span id="analysisResult">-</span></li>
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
                    <div class="mb-3">
                        <label for="responsible" class="form-label">Responsible</label>
                        <select class="form-select selectModal2" id="responsible" name="responsible" required>
                            <option value="">Choose Responsible User</option>
                            <!-- Populate with users from backend -->
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ !$user->back_ground_verified ? 'disabled' : '' }}>{{ $user->name }} </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="accountable" class="form-label">Accountable</label>
                        <select class="form-select selectModal2" id="accountable" name="accountable" required>
                            <option value="">Choose Accountable User</option>
                            <!-- Populate with users from backend -->
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ !$user->back_ground_verified ? 'disabled' : '' }}>{{ $user->name }} </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="consult" class="form-label">Consult</label>
                        <select class="form-select selectModal2" id="consult" name="consult" >
                            <option value="">Choose Consult User</option>
                            <!-- Populate with users from backend -->
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ !$user->back_ground_verified ? 'disabled' : '' }}>{{ $user->name }} </option>
                            @endforeach
                        </select>
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

    // ── Listen hasil AI dari broadcast ──────────────────────────────────────
    window.Echo.private(`ask-bos.${userId}`)
        .listen('AskBosResponseReady', (e) => {
            setLoading(false);

            document.getElementById('analysisResult').innerText      = e.analysis;
            document.getElementById('trustScoreResult').innerText    = e.trust_score;
            document.getElementById('executionScoreResult').innerText = e.execution_score;

            document.getElementById('analysisResultSave').value      = e.analysis;
            document.getElementById('trustScoreResultSave').value    = e.trust_score;
            document.getElementById('executionScoreResultSave').value = e.execution_score;

            if (e.trust_score !== 0 || e.execution_score !== 0) {
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
                document.getElementById('analysisResult').innerText       = data.analysis;
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

    document.addEventListener('DOMContentLoaded', function () {
        const askButton            = document.getElementById('askButton');
        const decisionButton       = document.getElementById('decisionButton');
        const submitDecisionButton = document.getElementById('submitDecisionButton');

        document.getElementById('submitDecision').style.display = 'none';

        // ── Ask Questions ──────────────────────────────────────────────────
        askButton.addEventListener('click', function () {
            const question       = document.getElementById('questionInput').value;
            const selectedFilters = Array.from(document.querySelectorAll('input[type="checkbox"]:checked')).map(el => el.value);

            if (!question.trim()) { alert('Silakan masukkan pertanyaan sebelum mengirim.'); return; }

            // Reset form
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
                document.getElementById('analysisResult').innerText = 'Terjadi kesalahan saat memproses.';
                setLoading(false);
            });
        });

        // ── Make Decision modal submit ─────────────────────────────────────
        decisionButton.addEventListener('click', function () {
            const modal = new bootstrap.Modal(document.getElementById('makeDecisionModal'));
            modal.show();
        });

        submitDecisionButton.addEventListener('click', function () {
            const responsible = document.getElementById('responsible').value;
            const accountable = document.getElementById('accountable').value;
            const consult     = document.getElementById('consult').value;
            const question    = document.getElementById('questionInput').value;

            if (!question.trim())              { alert('Silakan masukkan pertanyaan sebelum mengirim.'); return; }
            if (!responsible || !accountable)  { alert('Please select Responsible dan Accountable.'); return; }

            document.getElementById('questionResult').value    = question;
            document.getElementById('responsibleResult').value = responsible;
            document.getElementById('accountableResult').value = accountable;
            document.getElementById('consultResult').value     = consult;

            setLoading(true);
            document.getElementById('closeModal').click();

            fetch("{{ route('ask.makeDesition') }}", {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body   : JSON.stringify({ question, responsible, accountable, consult })
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