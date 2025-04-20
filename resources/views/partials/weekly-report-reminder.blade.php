<div class="alert alert-warning shadow-sm">
    <strong><i class="fas fa-exclamation-circle mr-1"></i>Reminder!</strong><br>
    @foreach($notReportedDivisions as $division)
        <div>Divisi <strong>{{ $division->name }}</strong> belum mengisi laporan minggu ini.</div>
    @endforeach

    <a href="{{ route('weekly-report.create') }}" class="btn btn-sm btn-primary mt-2">
        <i class="fas fa-pen mr-1"></i>Isi Laporan
    </a>
</div>