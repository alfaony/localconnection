@section('title', 'Manajemen WFO Rules')

@section('content_header')
    <h1>Manajemen WFO Rules</h1>
@stop


<div>
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('message') }}
        </div>
    @endif

    <!-- Form Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                {{ $editingId ? 'Edit WFO Rules' : 'Tambah WFO Rules' }}
            </h3>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="save">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>User <span class="text-danger">*</span></label>
                            <select wire:model="userId" class="form-control @error('userId') is-invalid @enderror">
                                <option value="">Pilih User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('userId') 
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Jam Masuk Check-in <span class="text-danger">*</span></label>
                            <input type="time" wire:model="entryTimeCheckin" 
                                    class="form-control @error('entryTimeCheckin') is-invalid @enderror">
                            @error('entryTimeCheckin') 
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Jumlah Check-in Per Hari <span class="text-danger">*</span></label>
                            <input type="number" wire:model="timesCheckinInDay" 
                                    class="form-control @error('timesCheckinInDay') is-invalid @enderror"
                                    placeholder="Contoh: 2"
                                    min="1">
                            @error('timesCheckinInDay') 
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Berapa kali check-in yang diperlukan dalam sehari</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Poin Check-in Per Hari <span class="text-danger">*</span></label>
                            <input type="number" wire:model="pointCheckinInDay" 
                                    class="form-control @error('pointCheckinInDay') is-invalid @enderror"
                                    placeholder="Contoh: 10"
                                    max="0">
                            @error('pointCheckinInDay') 
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Poin yang didapat per hari jika memenuhi check-in</small>
                        </div>
                    </div>
                </div>
                @canAccess('store','wfo_rules')
                @canAccess('update','wfo_rules')
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ $editingId ? 'Update' : 'Simpan' }}
                    </button>
                    @if($editingId)
                        <button type="button" wire:click="cancel" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    @endif
                </div>
                @endcanAccess
                @endcanAccess
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar WFO Rules</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Jam Masuk</th>
                        <th>Check-in Per Hari</th>
                        <th>Poin Per Hari</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wfoRules as $wfoRule)
                        <tr>
                            <td>{{ $wfoRule->user->name }}</td>
                            <td>{{ $wfoRule->entry_time_checkin->format('H:i') }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $wfoRule->times_checkin_in_day }}x
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-success">
                                    {{ $wfoRule->point_checkin_in_day }} poin
                                </span>
                            </td>
                            <td>
                                @canAccess('edit','wfo_rules')
                                <button wire:click="edit({{ $wfoRule->id }})"
                                        class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                @endcanAccess
                                @canAccess('destroy','wfo_rules')
                                <button wire:click="delete({{ $wfoRule->id }})"
                                        onclick="return confirm('Yakin ingin menghapus?')"
                                        class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                                @endcanAccess
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Belum ada data WFO Rules
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($wfoRules->hasPages())
            <div class="card-footer clearfix">
                {{ $wfoRules->links() }}
            </div>
        @endif
    </div>
</div>

@section('css')
    <style>
        .alert {
            margin-bottom: 20px;
        }
    </style>
@stop

@section('js')
    <script>
        // Auto hide alert after 3 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 3000);
    </script>
@stop