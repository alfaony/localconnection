<div>
    @include('components.alert')
    @canAccess('store','routers')
    @canAccess('update','routers')
    <div class="card card-primary card-outline mt-5">
        <div class="card-header">
            <h3 class="card-title">{{ $mikrotikId ? 'Edit' : 'Create' }} Router - Mikrotik</h3>
        </div>
        <form wire:submit.prevent="save">
            <div class="card-body">
                <div class="form-group">
                    <label for="pop_id">POP <span class="text-danger">*</span></label>
                    <select class="form-control @error('pop_id') is-invalid @enderror" 
                            id="pop_id" 
                            wire:model="pop_id">
                        <option value="">-- Pilih POP --</option>
                        @foreach($pops as $pop)
                            <option value="{{ $pop->id }}">{{ $pop->name }}</option>
                        @endforeach
                    </select>
                    @error('pop_id') <span class="error text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="name">Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           placeholder="Enter router name" 
                           wire:model="name">
                    @error('name') <span class="error text-danger">{{ $message }}</span> @enderror
                </div>

                {{-- ✅ Connection Settings Section --}}
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Connection Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="mikrotik_host">
                                Host (IP Address) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control @error('mikrotik_host') is-invalid @enderror" 
                                       id="mikrotik_host" 
                                       placeholder="192.168.1.1" 
                                       wire:model.lazy="mikrotik_host">
                                <div class="input-group-append">
                                    @if($hostChecked)
                                        @if($hostAvailable)
                                            <span class="input-group-text bg-success">
                                                <i class="fas fa-check"></i>
                                            </span>
                                        @else
                                            <span class="input-group-text bg-danger">
                                                <i class="fas fa-times"></i>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @error('mikrotik_host') 
                                <span class="error text-danger">{{ $message }}</span> 
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="mikrotik_port">Port <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('mikrotik_port') is-invalid @enderror" 
                                   id="mikrotik_port" 
                                   placeholder="8728" 
                                   wire:model.lazy="mikrotik_port">
                            @error('mikrotik_port') 
                                <span class="error text-danger">{{ $message }}</span> 
                            @enderror
                            <small class="form-text text-muted">
                                Default: 8728 (non-SSL), 8729 (SSL)
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="mikrotik_username">Username <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('mikrotik_username') is-invalid @enderror" 
                                   id="mikrotik_username" 
                                   placeholder="admin" 
                                   wire:model.lazy="mikrotik_username">
                            @error('mikrotik_username') 
                                <span class="error text-danger">{{ $message }}</span> 
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="mikrotik_password">Password <span class="text-danger">*</span></label>
                            <input type="password" 
                                   class="form-control @error('mikrotik_password') is-invalid @enderror" 
                                   id="mikrotik_password" 
                                   placeholder="Enter password" 
                                   wire:model.lazy="mikrotik_password">
                            @error('mikrotik_password') 
                                <span class="error text-danger">{{ $message }}</span> 
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" 
                                       type="checkbox" 
                                       id="mikrotik_ssl" 
                                       wire:model="mikrotik_ssl" 
                                       value="1">
                                <label for="mikrotik_ssl" class="custom-control-label">
                                    Use SSL Connection
                                </label>
                            </div>
                            @error('mikrotik_ssl') 
                                <span class="error text-danger">{{ $message }}</span> 
                            @enderror
                            <small class="form-text text-muted">
                                Enable if using SSL/TLS (port 8729)
                            </small>
                        </div>

                        {{-- ✅ Test Connection Button --}}
                        <div class="form-group">
                            <button type="button" 
                                    class="btn btn-info btn-block" 
                                    wire:click="testConnection"
                                    wire:loading.attr="disabled"
                                    wire:target="testConnection">
                                <span wire:loading.remove wire:target="testConnection">
                                    <i class="fas fa-network-wired"></i> Test Connection
                                </span>
                                <span wire:loading wire:target="testConnection">
                                    <i class="fas fa-spinner fa-spin"></i> Testing...
                                </span>
                            </button>
                        </div>

                        {{-- ✅ Connection Test Result --}}
                        @if($connectionTestResult)
                            <div class="alert alert-{{ $connectionTestResult['success'] ? 'success' : 'danger' }} alert-dismissible">
                                <button type="button" class="close" wire:click="$set('connectionTestResult', null)">
                                    <span>&times;</span>
                                </button>
                                <h5>
                                    <i class="icon fas fa-{{ $connectionTestResult['success'] ? 'check' : 'ban' }}"></i>
                                    {{ $connectionTestResult['success'] ? 'Success!' : 'Failed!' }}
                                </h5>
                                {{ $connectionTestResult['message'] }}
                                
                                @if($connectionTestResult['success'] && isset($connectionTestResult['identity']))
                                    <br>
                                    <small><strong>Router Identity:</strong> {{ $connectionTestResult['identity'] }}</small>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" 
                        class="btn btn-primary"
                        @if(!$hostChecked || !$hostAvailable) disabled @endif>
                    <i class="fas fa-save"></i> Submit
                </button>
                <a href="{{ route('router.index') }}" class="btn btn-default">
                    <i class="fas fa-times"></i> Cancel
                </a>
                
                @if(!$hostChecked || !$hostAvailable)
                    <div class="text-warning mt-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> Please test connection first before saving!
                    </div>
                @endif
            </div>
        </form>
    </div>
    @endcanAccess
    @endcanAccess
</div>

@push('js')
<script>
    // Notification handler
    window.addEventListener('show-notification', function(event) {
        const type = event.detail.type || 'info';
        const message = event.detail.message || '';
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Success' : type === 'error' ? 'Error' : 'Warning',
                text: message,
                showConfirmButton: true,
            });
        } else {
            alert(message);
        }
    });
</script>
@endpush