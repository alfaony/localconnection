<div class="container-fluid mt-5">
    @include('components.alert')
    <!-- @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            {{ session('error') }}
        </div>
    @endif -->

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                {{ $isEditMode ? 'Edit Webhook Setting' : 'Create New Webhook Setting' }}
            </h3>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="save">
                <!-- Application Name -->
                <div class="form-group">
                    <label>Nama Aplikasi</label>
                    <input 
                        type="text" 
                        wire:model="app_name"
                        class="form-control"
                        placeholder="Contoh: Inventory System"
                        required
                    >
                </div>

                <!-- Apps Selection -->
                <div class="form-group">
                    <label>Daftar Apps</label>
                    <div class="row">
                        @foreach($available_apps as $app)
                            <div class="col-sm-4 col-md-3">
                                <div class="custom-control custom-checkbox">
                                    <input 
                                        type="checkbox" 
                                        id="app_{{ $app }}"
                                        value="{{ $app }}"
                                        wire:model="selected_apps"
                                        class="custom-control-input"
                                    >
                                    <label class="custom-control-label" for="app_{{ $app }}">
                                        {{ str_replace('_', ' ', $app) }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- URL -->
                <div class="form-group">
                    <label>Webhook URL</label>
                    <input 
                        type="url" 
                        wire:model="url"
                        class="form-control"
                        placeholder="https://api.example.com/webhook"
                        required
                    >
                </div>

                <!-- Token -->
                <div class="form-group">
                    <label>API Token</label>
                    <input 
                        type="text" 
                        wire:model="token"
                        class="form-control"
                        placeholder="Masukkan token API"
                        required
                    >
                </div>

                <div class="form-group">
                    <button 
                        type="submit" 
                        class="btn btn-primary"
                    >
                        <i class="fas fa-save"></i> {{ $isEditMode ? 'Update Setting' : 'Simpan Setting' }}
                    </button>

                    <button 
                        type="button" 
                        wire:click="testConnection"
                        class="btn btn-success"
                    >
                        <i class="fas fa-plug"></i> Test Connection
                    </button>

                    @if($isEditMode)
                        <button 
                            type="button" 
                            wire:click="resetForm"
                            class="btn btn-default"
                        >
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Settings List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Webhook Settings</h3>
        </div>
        <div class="card-body">
            @if(count($settings) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Nama Aplikasi</th>
                                <th>Apps</th>
                                <th>URL</th>
                                <th>Token</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($settings as $setting)
                                <tr>
                                    <td>{{ $setting['app_name'] }}</td>
                                    <td>
                                        @foreach($setting['selected_apps'] as $app)
                                            <span class="badge bg-primary">
                                                {{ $app }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 200px;">
                                            {{ $setting['url'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 100px;">
                                            {{ substr($setting['token'], 0, 10) }}*****
                                        </span>
                                    </td>
                                    <td>
                                        <button 
                                            wire:click="edit({{ $setting['id'] }})"
                                            class="btn btn-sm btn-warning"
                                            title="Edit"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button 
                                            wire:click="delete({{ $setting['id'] }})"
                                            class="btn btn-sm btn-danger"
                                            title="Delete"
                                            onclick="return confirm('Hapus setting ini?')"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    Belum ada setting webhook yang dibuat
                </div>
            @endif
        </div>
    </div>
</div>

@push('js')
<script>
    // Add any custom JavaScript if needed
    document.addEventListener('livewire:load', function () {
        // Livewire specific JS can go here
    });
</script>
@endpush