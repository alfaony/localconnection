@extends('adminlte::page')

@section('title', isset($chart) ? 'Edit Alur Kerja' : 'Create Alur Kerja')

@section('content')
    <form method="POST" action="{{ isset($chart) ? route('flowchart.update', $chart->id) : route('flowchart.store') }}" id="flowchartForm">
        @csrf
        @if(isset($chart))
            @method('PUT')
        @endif

        <div class="card card-primary mt-5">
            <div class="card-header">
                <h5>{{ isset($chart) ? 'Edit' : 'Create' }} Alur Kerja</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name">Alur Kerja Name</label>
                        <input type="text" name="name" class="form-control"  value="{{ old('name', $chart->name ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="description">Description</label>
                        <input class="thriveEditor form-control" id="description_description" data-ids="description" name="description" placeholder="yang akan dicetak di perjanjian" value="{{ old('description', $chart->description ?? '') }}" />
                    </div>
                </div>

                <input type="hidden" name="model" id="modelInput">
        
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="mb-2">
                            <button type="button" class="btn btn-success btn-sm" onclick="addNode('start')">Add Start</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="addNode('step')">Add Step</button>
                            <button type="button" class="btn btn-warning btn-sm" onclick="addConditionalBranch()">Add Conditional</button>
                            <button type="button" class="btn btn-dark btn-sm" onclick="addNode('command')">Add Comment</button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="addNode('end')">Add End</button>
                        </div>
                        <div id="drawflow"></div>
                    </div>
                </div>
        
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ isset($chart) ? 'Update' : 'Create' }} Alur Kerja
                    </button>
                    <a href="{{ route('flowchart.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </div>
        
    </form>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/drawflow"></script>
    <script>
        let editor;

        document.addEventListener("DOMContentLoaded", function () {
            editor = new Drawflow(document.getElementById("drawflow"));
            editor.reroute = true;
            editor.start();

            @if(isset($chart) && $chart->json_model)
                editor.import({!! $chart->json_model !!});
            @endif

            document.getElementById("flowchartForm").addEventListener("submit", function (e) {
                // e.preventDefault();

                const exported = editor.export();
                const data = exported?.drawflow?.Home?.data ?? {};

                for (const id in data) {
                    const node = document.getElementById("node-" + id);
                    const title = node?.querySelector(".title");

                    if (title) {
                        const userText = title.innerText.trim();

                        data[id].data.label = userText;
                        data[id].html = `<div class="drawflow_content_node"><div class="title" contenteditable="true">${userText}</div></div>`;
                    }
                }

                // Simpan ke hidden input
                document.getElementById("modelInput").value = JSON.stringify(exported);
                // console.log("✅ Exported model ready:", exported);
            });
        });

        function addNode(type) 
        {
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
            // editor.addNode(type, 1, 1, pos.x, pos.y, type, {}, html);
            // editor.addNode(type, inputs, outputs, pos.x, pos.y, type, {}, html);
            editor.addNode(type, inputs, outputs, pos.x, pos.y, type, { label }, html);

        }
        function addConditionalBranch() 
        {
            const pos = getViewportPosition();
            console.log("Viewport pos", pos);

            const htmlCond = `<div class="title" contenteditable="true">CONDITIONAL</div>`;
            const htmlYes = `<div class="title" contenteditable="true">YES</div>`;
            const htmlNo = `<div class="title" contenteditable="true">NO</div>`;

            const idCond = editor.addNode('condition', 1, 1, pos.x, pos.y, 'condition', {}, htmlCond);
            const idYes  = editor.addNode('step', 1, 1, pos.x + 200, pos.y - 60, 'step', {}, htmlYes);
            const idNo   = editor.addNode('step', 1, 1, pos.x + 200, pos.y + 60, 'step', {}, htmlNo);

            editor.addConnection(idCond, idYes, 'output_1', 'input_1');
            editor.addConnection(idCond, idNo, 'output_1', 'input_1');

            // Optional: auto-scroll ke tengah setelah tambah
            const container = document.getElementById("drawflow");
            container.scrollTo({
                left: pos.x - 300,
                top: pos.y - 200,
                behavior: 'smooth'
            });
        }

        function getViewportPosition() 
        {
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
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
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
.ql-container 
{
    min-height: 150px;
    height: auto;
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
</style>
@endsection