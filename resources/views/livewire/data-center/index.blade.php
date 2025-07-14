<div>
    <div class="row">
        <div class="col-md-12 mt-5">
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header">
                    <h3 class="card-title">Data Centers</h3>
                    <div class="card-tools">
                        <a href="{{ route('data-centers.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Add New
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @include('components.alert')
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Kapasitas (MB)</th>
                                <th>Biaya/Bulan</th>
                                <th>Tanggal Tagihan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dataCenters as $dc)
                            <tr>
                                <td>{{ $dc->name }}</td>
                                <td>{{ number_format($dc->capacity_mb) }}</td>
                                <td>{{ number_format($dc->cost_per_month, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($dc->tanggal_tagihan)->locale('id_ID')->Format('d F Y') }}</td>
                                <td>
                                    <a href="{{ route('data-centers.edit', $dc) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button wire:click="delete({{ $dc->id }})" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>