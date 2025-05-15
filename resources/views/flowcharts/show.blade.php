@extends('adminlte::page')

@section('title', 'Alur Kerja')

@section('content')
    <div class="row">
        <div class="col-md-12 mt-2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('flowchart.index') }}">Alur Kerja</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $chart->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-12 mt-1">
            <div class="card">
                <div class="card-header">
                        {{ $chart->name }}
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="description">Description</label>
                            {!!  $chart->description !!}
                        </div>
                    </div>
                
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div id="drawflow"></div>
                        </div>
                    </div>
                
                    <a href="{{ route('flowchart.index') }}" class="btn btn-secondary">Back</a>
                    {{-- <button id="downloadBtn" class="btn btn-success">Download </button>--}}
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/drawflow"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script>
        let editor;

        document.addEventListener("DOMContentLoaded", function () {
            editor = new Drawflow(document.getElementById("drawflow"));
            editor.reroute = false;
            editor.start();
            editor.editor_mode = "view"; // readonly mode

            @if(isset($chart) && $chart->json_model)
                try {
                   let model = @json($chart->json_model);
                        if (typeof model === "string") {
                            model = JSON.parse(model);
                        }

                        if (model?.drawflow?.Home?.data) {
                            editor.import(model);
                            setTimeout(() => focusDiagram(), 100);
                        } else {
                            alert("Flowchart tidak ditemukan.");
                        }
                } catch (e) {
                    console.error("Gagal memuat diagram:", e);
                    alert("Flowchart rusak atau tidak valid.");
                }
            @endif

            // Download as PNG
            document.getElementById("downloadBtn")?.addEventListener("click", function () {
                // Ambil elemen wrapper: precanvas + svg
                const wrapper = document.querySelector("#drawflow .drawflow");

                html2canvas(wrapper, {
                    backgroundColor: null,
                    scale: 2,
                    width: wrapper.scrollWidth,
                    height: wrapper.scrollHeight
                }).then(canvas => {
                    const link = document.createElement("a");
                    link.download = "flowchart.png";
                    link.href = canvas.toDataURL("image/png");
                    link.click();
                });
            });
        });

        function focusDiagram() {
            const nodes = Object.values(editor.drawflow?.Home?.data ?? []);
            if (nodes.length === 0) return;

            const xs = nodes.map(n => n.pos_x);
            const ys = nodes.map(n => n.pos_y);

            const minX = Math.min(...xs);
            const maxX = Math.max(...xs);
            const minY = Math.min(...ys);
            const maxY = Math.max(...ys);

            const padding = 100;
            const canvas = document.getElementById("drawflow");
            const content = canvas.querySelector(".drawflow");

            content.style.minWidth = `${(maxX - minX + padding * 2)}px`;
            content.style.minHeight = `${(maxY - minY + padding * 2)}px`;

            canvas.scrollLeft = minX - padding;
            canvas.scrollTop = minY - padding;
        }

        function addNode(type) {
            const labelMap = {
                start: "START",
                step: "STEP",
                condition: "CONDITION",
                end: "END",
                command: "COMMAND"
            };

            const label = labelMap[type] || "NODE";
            const pos = getViewportPosition();
            const inputs = type === 'command' ? 0 : 1;
            const outputs = type === 'command' ? 0 : 1;
            const html = `<div class='title' contenteditable='true'>${label}</div>`;

            editor.addNode(type, inputs, outputs, pos.x, pos.y, type, {}, html);
        }

        function addConditionalBranch() {
            const pos = getViewportPosition();

            const idCond = editor.addNode('condition', 1, 1, pos.x, pos.y, 'condition', {},
                `<div class="title" contenteditable="true">CONDITIONAL</div>`);

            const idYes = editor.addNode('step', 1, 1, pos.x + 200, pos.y - 60, 'step', {},
                `<div class="title" contenteditable="true">YES</div>`);

            const idNo = editor.addNode('step', 1, 1, pos.x + 200, pos.y + 60, 'step', {},
                `<div class="title" contenteditable="true">NO</div>`);

            editor.addConnection(idCond, idYes, 'output_1', 'input_1');
            editor.addConnection(idCond, idNo, 'output_1', 'input_1');
        }

        function getViewportPosition() {
            const canvas = document.getElementById('drawflow');
            const rect = editor.precanvas.getBoundingClientRect();

            return {
                x: canvas.scrollLeft + (canvas.clientWidth / 2) - rect.left,
                y: canvas.scrollTop + (canvas.clientHeight / 2) - rect.top
            };
        }
    </script>
@endsection
@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/drawflow/dist/drawflow.min.css">
    <style>
        #drawflow {
            width: 100%;
            height: 70vh;
            border: 1px solid #ccc;
            border-radius: 6px;
            background: #f5f5f5;
        }
        .drawflow-node.start { background: #d1fae5; border-left: 6px solid #10b981; }
        .drawflow-node.step { background: #bfdbfe; border-left: 6px solid #3b82f6; }
        .drawflow-node.condition { background: #fef3c7; border-left: 6px solid #facc15; }
        .drawflow-node.end { background: #fecaca; border-left: 6px solid #ef4444; }
        .drawflow-node .title { font-weight: bold; font-size: 14px; }
    </style>
<style>
   
#drawflow 
{
    width: 100%;
    height: 70vh;
    border: 1px solid #ccc;
    background: #f5f5f5;
    border-radius: 6px;
    overflow: auto; /* ✅ aktifkan scroll X dan Y */
    position: relative;
}

.drawflow-node .title {
    font-weight: bold;
    font-size: 14px;
    text-align: center;
}

/* 🟢 Start */
.drawflow-node.start {
    background: #d1fae5;
    border-radius: 999px;
    border-left: 6px solid #10b981;
}

/* 🔵 Step */
.drawflow-node.step {
    background: #bfdbfe;
    border-radius: 6px;
    border-left: 6px solid #3b82f6;
}

/* 🟡 Condition */
.drawflow-node.condition {
    background: #fef3c7;
    border-radius: 6px;
    border-left: 6px solid #f59e0b;
}

/* 🔴 End */
.drawflow-node.end {
    background: #fecaca;
    border-radius: 999px;
    border-left: 6px solid #ef4444;
}

/* 🔷 Node active (default: merah), ubah ke biru terang */
.drawflow-node.selected {
    border: 2px dashed #3b82f6 !important;
    box-shadow: 0 0 8px #3b82f6;
}

.drawflow .drawflow-node.selected 
{
    background: #64e9f8 !important;
}
.drawflow-node.command {
    background: transparent;
    border: 1px dashed #6366f1;
    border-radius: 12px;
    font-style: italic;
    padding: 6px 10px;
    box-shadow: 0 0 4px rgba(99, 102, 241, 0.2);
}
</style>
<style>
    /* Default list styling */
    .ql-editor ol,
    .ql-editor ul {
        padding-left: 1.5em;
    }

    /* Level 1 indentation */
    .ql-editor .ql-indent-1 {
        padding-left: 2em;
    }

    /* Level 2 indentation */
    .ql-editor .ql-indent-2 {
        padding-left: 3em;
    }

    /* Level 3 indentation */
    .ql-editor .ql-indent-3 {
        padding-left: 4em;
    }

    .drawflow {
        position: relative;
    }

    .drawflow svg {
        position: absolute;
        top: 0;
        left: 0;
        z-index: 0;
    }
</style>
@endsection