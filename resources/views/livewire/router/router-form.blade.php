<div>
    @include('components.alert')
    @canAccess('store','routers')
    @canAccess('update','routers')
    <div class="card card-primary card-outline mt-5">
        <div class="card-header">
            <h3 class="card-title">{{ $mikrotikId ? 'Edit' : 'Create' }}  Router - Mikrotik</h3>
        </div>
        <form wire:submit.prevent="save">
            <div class="card-body">
                <div class="form-group">
                    <label for="pop_id">POP</label>
                    <select class="form-control @error('pop_id') is-invalid @enderror" id="pop_id" wire:model="pop_id">
                        <option value="">-- Pilih --</option>
                        @foreach($pops as $pop)
                            <option value="{{ $pop->id }}">{{ $pop->name }}</option>
                        @endforeach
                    </select>
                    @error('pop_id') <span class="error text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Enter name" wire:model="name">
                    @error('name') <span class="error text-danger">{{ $message }}</span> @enderror
                </div>
                
                <div class="form-group">
                    <label for="mikrotik_host">Host</label>
                    <input type="text" class="form-control @error('mikrotik_host') is-invalid @enderror" id="mikrotik_host" placeholder="Enter host" wire:model="mikrotik_host">
                    @error('mikrotik_host') <span class="error text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="mikrotik_port">Port</label>
                    <input type="text" class="form-control @error('mikrotik_port') is-invalid @enderror" id="mikrotik_port" placeholder="Enter port" wire:model="mikrotik_port">
                    @error('mikrotik_port') <span class="error text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="mikrotik_username">Username</label>
                    <input type="text" class="form-control @error('mikrotik_username') is-invalid @enderror" id="mikrotik_username" placeholder="Enter username" wire:model="mikrotik_username">
                    @error('mikrotik_username') <span class="error text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="mikrotik_password">Password</label>
                    <input type="text" class="form-control @error('mikrotik_password') is-invalid @enderror" id="mikrotik_password" placeholder="Password" wire:model="mikrotik_password">
                    @error('mikrotik_password') <span class="error text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="mikrotik_ssl" wire:model="mikrotik_ssl" value="1">
                        <label for="mikrotik_ssl" class="custom-control-label">Use SSL</label>
                    </div>
                    @error('mikrotik_ssl') <span class="error text-danger">{{ $message }}</span> @enderror
                </div>
                {{-- 
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="mikrotik_active" wire:model="mikrotik_active" value="1">
                        <label for="mikrotik_active" class="custom-control-label">Active</label>
                    </div>
                    @error('mikrotik_active') <span class="error text-danger">{{ $message }}</span> @enderror
                </div>
                ---}}
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ route('router.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>
    @endcanAccess
    @endcanAccess
</div>