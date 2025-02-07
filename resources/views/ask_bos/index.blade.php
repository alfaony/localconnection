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
            <button id="askButton" class="btn btn-outline-primary me-3">
                <i class="fas fa-question-circle"></i> Ask Questions
            </button>
            <button id="decisionButton" class="btn btn-outline-secondary">
                <i class="fas fa-balance-scale"></i> Make Decision
            </button>
        </div>

        <!-- Checkbox Filters -->
        <div class="mb-4 d-flex justify-content-center flex-wrap">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="webSearch" value="Web Search">
                <label class="form-check-label" for="webSearch">Web Search</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="finance" value="Finance">
                <label class="form-check-label" for="finance">Finance</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="stock" value="Stok Barang" >
                <label class="form-check-label" for="stock">Stok Barang</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="location" value="Lokasi">
                <label class="form-check-label" for="location">Lokasi</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="logistics" value="Logistik">
                <label class="form-check-label" for="logistics">Logistik</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="project" value="Project">
                <label class="form-check-label" for="project">Project</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="marketing" value="Marketing">
                <label class="form-check-label" for="marketing">Marketing</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="salesQuote" value="Sales Quote">
                <label class="form-check-label" for="salesQuote">Sales Quote</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="bigData" value="Big Data">
                <label class="form-check-label" for="bigData">Big Data</label>
            </div>
        </div>

        <!-- Results -->
        <div id="resultSection" class="mt-5">
            <h3 class="text-primary mb-3">Hasil Analisis</h3>
            <ul class="list-group">
                <li class="list-group-item"><b>Analisis:</b> <span id="analysisResult">-</span></li>
                <li class="list-group-item"><b>Trust Score:</b> <span id="trustScoreResult">-</span></li>
                <li class="list-group-item"><b>Execution Score:</b> <span id="executionScoreResult">-</span></li>
            </ul>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .form-check-inline {
        margin-right: 15px;
    }
</style>
@stop

@section('js')
<script>
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

        decisionButton.addEventListener('click', function () {
            alert('Make Decision Clicked! (Implement logic here)');
        });
    });
    document.addEventListener('DOMContentLoaded', function () {
        const askButton = document.getElementById('askButton');
        
        askButton.addEventListener('click', function () {
            const question = document.getElementById('questionInput').value;
            const selectedFilters = Array.from(document.querySelectorAll('input[type="checkbox"]:checked')).map(el => el.value);

            if (!question.trim()) {
                alert('Silakan masukkan pertanyaan sebelum mengirim.');
                return;
            }

            document.getElementById('analysisResult').innerText = "Sedang memproses...";
            document.getElementById('trustScoreResult').innerText = "-";
            document.getElementById('executionScoreResult').innerText = "-";

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

        function checkResponse() {
            setTimeout(() => {
                fetch("{{ route('check.response') }}")
                .then(response => response.json())
                .then(data => {
                    if (data.status !== 'waiting') {
                        document.getElementById('analysisResult').innerText = data.analysis;
                        document.getElementById('trustScoreResult').innerText = `${data.trust_score} / 100`;
                        document.getElementById('executionScoreResult').innerText = `${data.execution_score} / 100`;
                    } else {
                        checkResponse(); // Cek kembali jika belum selesai
                    }
                })
                .catch(error => console.error("Error fetching response:", error));
            }, 3000); // Polling setiap 3 detik
        }
    });
</script>
@stop