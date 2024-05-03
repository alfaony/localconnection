<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membuat Tiket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        body { padding-top: 20px; }
        .container { max-width: 600px; }
    </style>
</head>
<body>
    <div class="container">
    <div class="col-md-12">
        @if(Session::get('store'))
        <div class="alert alert-success mt-3">Tiket Berhasil Dibuat</div>
        @endif
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    </div>
        <div class="card">
            <div class="card-header">Membuat Tiket</div>
            <div class="card-body">
                <form action="{{ route('bos-ticket.store') }}" method="POST">
                    @csrf
                    {{-- 
                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact Email</label>
                        <input type="email" class="form-control" id="contact" name="contact" required>
                    </div>
                    --}}
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subjek</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Konten</label>
                        <input type="text" class="thriveEditor form-control" id="description_content" data-ids="content"  name="content">
                    </div>
                    <div class="form-group mb-3">
                        {!! NoCaptcha::renderJs() !!}
                        {!! NoCaptcha::display() !!}
                    </div>
                    <button type="submit" class="btn btn-primary">Buat Tiket</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
    <script src="{{ asset('js/thriveEditor.js') }}"></script>
</body>
</html>
