<div>
    <div class="d-flex justify-content-between mb-3">
        <input type="text" wire:model="search" class="form-control w-50" placeholder="Cari promo...">
        <a href="{{ route('promo.create') }}" class="btn btn-primary">Tambah Promo</a>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Jenis</th>
                <th>Nilai</th>
                <th>Masa Berlaku</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($promos as $promo)
                <tr>
                    <td>{{ $promo->name }}</td>
                    <td>{{ $promo->type }}</td>
                    <td>{{ $promo->value }}</td>
                    <td>
                        {{ $promo->start_date }} s/d {{ $promo->end_date }}
                    </td>
                    <td>
                        <a href="{{ route('promo.edit', ['id' => $promo->id]) }}" class="btn btn-sm btn-warning">Edit</a>
                        <button wire:click="delete('{{ $promo->id }}')" class="btn btn-sm btn-danger">Hapus</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $promos->links() }}
</div>