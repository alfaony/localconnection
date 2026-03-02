<div>
    <div class="row">
        @include('components.alert')
    </div>
    <div class="card card-primary card-outline mt-5">
        <div class="card-header">
            <h3 class="card-title">Daftar Router - Mikrotik</h3>
            <div class="card-tools">
                <a href="{{ route('router.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i></a>
            </div>
        </div>
        <div class="card-body">
            @if (session()->has('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Host</th>
                        <th>Port</th>
                        <th>Username</th>
                        <th>SSL</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mikrotiks as $mikrotik)
                    <tr>
                        <td>{{ $mikrotik->id }}</td>
                        <td>{{ $mikrotik->host }}</td>
                        <td>{{ $mikrotik->port }}</td>
                        <td>{{ $mikrotik->username }}</td>
                        <td>{!! $mikrotik->ssl ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
                        <td>
                            @switch($mikrotik->active)
                                @case('UP')
                                    <span class="badge bg-success">UP</span>
                                    @break
                                @case('DOWN')
                                    <span class="badge bg-danger">DOWN</span>
                                    @break
                                @default
                                    <span class="badge bg-warning">{{ $mikrotik->active }}</span>
                            @endswitch
                        </td>
                        <td>
                            <a href="{{ route('router.edit', $mikrotik) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                            <button type="button" class="btn btn-danger btn-sm" onclick="confirm('Yakin ingin menghapus router ini?') && @this.delete({{ $mikrotik->id }})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-4">
                {{ $mikrotiks->links() }}
            </div>
        </div>
    </div>
</div>