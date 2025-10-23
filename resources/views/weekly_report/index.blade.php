@extends('adminlte::page')

@section('title', 'Laporan Mingguan')

@section('content_header')
    <h1 class="m-0 text-dark">📆 Laporan Mingguan</h1>
@stop

@section('content')
@include('components.alert')
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div>
            <small class="text-muted">Daftar laporan mingguan Anda</small>
        </div>
        @canAccess('store','weekly_reports')
        <a href="{{ route('weekly-report.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus-circle mr-1"></i> Buat Laporan Baru
        </a>
        @endcanAccess
    </div>

    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="pl-4">Divisi</th>
                            <th>Tanggal</th>
                            <th>Minggu ke</th>
                            <th>Tahun</th>
                            <th>File</th>
                            <th class="text-right pr-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td class="pl-4">{{ $report->division->name ?? '-' }}</td>
                                <td>{{ $report->date->format('d M Y') }}</td>
                                <td>Minggu ke-{{ $report->week }}</td>
                                <td>{{ $report->year }}</td>
                                <td>
                                    @if($report->file)
                                        <a href="{{ s3_asset(true,10, $report->file) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-right pr-4">
                                    @if($report->isEditable)
                                    @canAccess('store','weekly_reports')
                                    <a href="{{ route('weekly-report.edit', $report->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcanAccess
                                    {{-- Optional Delete --}}
                                    @canAccess('destroy','weekly_reports')
                                    <form action="{{ route('weekly-report.destroy', $report->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus laporan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                                    </form> 
                                    @endcanAccess
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    Belum ada laporan mingguan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($reports->hasPages())
                <div class="card-footer bg-light py-2">
                    <div class="d-flex justify-content-center">
                        {{ $reports->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop