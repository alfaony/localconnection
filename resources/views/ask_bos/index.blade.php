@extends('adminlte::page')

@section('title', 'TANYA B.O.S')

@section('content_header')
    <h1 class="text-center">TANYA <b>B.O.S</b></h1>
@stop

@section('content')
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
<script>
    $(document).ready(function () {
        $('.selectModal2').select2({
            dropdownParent: '#makeDecisionModal',
            width: '100%',
            placeholder: 'Pilih',
            allowClear: true
        });
    });
    document.addEventListener('DOMContentLoaded', function () {
        const askButton = document.getElementById('askButton');
        const decisionButton = document.getElementById('decisionButton');

        askButton.addEventListener('click', function () {
            const question = document.getElementById('questionInput').value;
            const selectedFilters = Array.from(document.querySelectorAll('input[type="checkbox"]:checked')).map(el => el.value);

            // Simulasi hasil
            document.getElementById('analysisResult').innerText = `Analisis untuk "${question}" dengan filter ${selectedFilters.join(', ')}`;
            document.getElementById('trustScoreResult').innerText = `${Math.floor(Math.random() * 100)} / 100`;
            document.getElementById('executionScoreResult').innerText = `${Math.floor(Math.random() * 100)} / 100`;
        });
    });

    document.addEventListener('DOMContentLoaded', function () 
    {
        const decisionButton = document.getElementById('decisionButton');
        const submitDecisionButton = document.getElementById('submitDecisionButton');
        const askButton = document.getElementById('askButton');
        
        document.getElementById('submitDecision').style.display = 'none';

        // Show Modal when "Make Decision" is clicked
        decisionButton.addEventListener('click', function () {
            const modal = new bootstrap.Modal(document.getElementById('makeDecisionModal'));
            modal.show();
        });

        // Handle the submission of the decision form
        submitDecisionButton.addEventListener('click', function () 
        {            
            const responsible = document.getElementById('responsible').value;
            const accountable = document.getElementById('accountable').value;
            const consult = document.getElementById('consult').value;
            const question = document.getElementById('questionInput').value;

            document.getElementById('questionResult').value = '';
            document.getElementById('responsibleResult').value = '';
            document.getElementById('accountableResult').value = '';
            document.getElementById('consultResult').value = '';
            document.getElementById('analysisResultSave').value = '';
            document.getElementById('trustScoreResultSave').value = '';
            document.getElementById('executionScoreResultSave').value = '';

            document.getElementById('analysisResult').innerText = "Sedang memproses...";
            document.getElementById('trustScoreResult').innerText = "";
            document.getElementById('executionScoreResult').innerText = "";
            
            retryCount = 0; // Reset retry counter

            if (!question.trim()) {
                alert('Silakan masukkan pertanyaan sebelum mengirim.');
                return;
            }

            // Check if all fields are selected
            if (!responsible || !accountable ) 
            {
                alert('Please select Responsible, Accountable, and Consult.');
                return;
            }
            
            document.getElementById('questionResult').value = question;
            document.getElementById('responsibleResult').value = responsible;
            document.getElementById('accountableResult').value = accountable;
            document.getElementById('consultResult').value = consult;

            // Example: Send the data to the server
            fetch("{{ route('ask.makeDesition') }}", 
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    question:question,
                    responsible: responsible,
                    accountable: accountable,
                    consult: consult
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'processing') {
                    document.getElementById('closeModal').click();         
                    checkResponse(); // Jalankan polling untuk mengambil hasil
                }
            })
            .catch(error => {
                console.error("Error:", error);
                document.getElementById('analysisResult').innerText = "Terjadi kesalahan saat memproses.";
            });
        });

        askButton.addEventListener('click', function () {
            const question = document.getElementById('questionInput').value;
            const selectedFilters = Array.from(document.querySelectorAll('input[type="checkbox"]:checked')).map(el => el.value);

            document.getElementById('questionResult').value = '';
            document.getElementById('responsibleResult').value = '';
            document.getElementById('accountableResult').value = '';
            document.getElementById('consultResult').value = '';

            if (!question.trim()) {
                alert('Silakan masukkan pertanyaan sebelum mengirim.');
                return;
            }

            document.getElementById('analysisResult').innerText = "Sedang memproses...";
            document.getElementById('trustScoreResult').innerText = "";
            document.getElementById('executionScoreResult').innerText = "";
            
            retryCount = 0; // Reset retry counter

            document.getElementById('questionResult').value = question;

            fetch("{{ route('ask.bos') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ question: question, filters: selectedFilters })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'processing') {
                    checkResponse(); // Jalankan polling untuk mengambil hasil
                }
            })
            .catch(error => {
                console.error("Error:", error);
                document.getElementById('analysisResult').innerText = "Terjadi kesalahan saat memproses.";
            });
        });

        let retryCount = 0;
        function checkResponse() {
            setTimeout(() => {
                fetch("{{ route('check.response') }}")
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    
                    if (data.status !== 'waiting') {
                        document.getElementById('analysisResult').innerText = data.analysis;
                        document.getElementById('trustScoreResult').innerText = `${data.trust_score} `;
                        document.getElementById('executionScoreResult').innerText = `${data.execution_score}`;
                        
                        document.getElementById('analysisResultSave').value = data.analysis;
                        document.getElementById('trustScoreResultSave').value = `${data.trust_score} `;
                        document.getElementById('executionScoreResultSave').value = `${data.execution_score}`;

                        if (data.trust_score !== 0 && data.execution_score !== 0) 
                        {
                            const submitDecision = document.getElementById('submitDecision');
                            submitDecision.style.display = 'block';
                        }
                    } else if (retryCount < 16) 
                    {
                        retryCount += 1;
                        checkResponse(); // Cek kembali jika belum selesai
                    } else {
                        document.getElementById('analysisResult').innerText = "Terjadi kesalahan saat memproses. Silakan coba lagi nanti.";
                    }
                })
                .catch(error => console.error("Error fetching response:", error));
            }, 5000); // Polling setiap 3 detik
        }
    });
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>
    .form-check-inline {
        margin-right: 15px;
    }
    body {
        font-family: Arial, sans-serif;
        /* padding: 20px; */
        background-color: #f4f4f4;
    }

    .container {
        background-color: #fff;
        padding: 10px;
        border-radius: 5px;
    }

    .select2-selection__rendered {
        line-height: 31px !important;
    }

    .select2-container .select2-selection--single {
        height: 35px !important;
    }

    .select2-selection__arrow {
        height: 34px !important;
    }

    hr {
        border: 1px solid black;
        border-radius: 5px;
    }

    .select2-selection__rendered {
        line-height: 31px !important;
    }

    .select2-container .select2-selection--single {
        height: 35px !important;
    }

    .select2-selection__arrow {
        height: 34px !important;
    }
</style>
@stop