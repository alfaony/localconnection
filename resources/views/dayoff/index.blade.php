@extends('adminlte::page')

@section('title', 'Daftar Cuti Saya')

@section('content_header')
    <h1>Daftar Pengajuan Cuti</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('dayoff.create') }}" class="btn btn-primary mb-3">+ Ajukan Cuti</a>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="bg-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Durasi</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th>Bukti</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cutis as $cuti)
                    <tr>
                        <td>{{ $cuti->date_start->format('d M Y') }} - {{ $cuti->date_end->format('d M Y') }}</td>
                        <td>{{ $cuti->type->name ?? '-' }}</td>
                        <td>{{ $cuti->date_start->diffInDays($cuti->date_end) + 1 }} hari</td>
                        <td>{{ $cuti->reason }}</td>
                        <td>
                            @if($cuti->rejected_at)
                                <span class="badge bg-danger">Ditolak</span>
                            @elseif($cuti->approved_hr_at && $cuti->approved_finance_at)
                                <span class="badge bg-success">Disetujui</span>
                            @else
                                <span class="badge bg-warning text-dark">Menunggu</span>
                            @endif
                        </td>
                        <td>
                            @if($cuti->file)
                                <a href="{{ Storage::url($cuti->file) }}" target="_blank" class="btn btn-sm btn-info">Lihat</a>
                            @endif
                        </td>
                        <td>
                            @if(!$cuti->approved_hr_at && !$cuti->rejected_at)
                                <a href="{{ route('dayoff.edit', $cuti->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('dayoff.destroy', $cuti->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus cuti ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            @else
                                <small><em>Terkunci</em></small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Belum ada pengajuan cuti.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@stop