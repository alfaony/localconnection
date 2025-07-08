<div>
    
    <div class="card mt-3">
        <div class="card-header bg-primary">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-white">
                    <i class="fas fa-laptop mr-2"></i> Manajemen Laptop Bekas
                </h3>
                @canAccess('create','used_laptops')
                <a href="{{ route('used-laptop.create') }}" class="btn btn-success">
                    <i class="fas fa-plus mr-1"></i> Tambah Laptop
                </a>
                @endcanAccess
            </div>
            <div class="card-tools mt-2">
                <div class="input-group input-group-sm">
                    <input 
                        type="text" 
                        class="form-control" 
                        placeholder="Cari laptop..."
                        wire:model.debounce.300ms="search"
                    >
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th wire:click="sortBy('name')" style="cursor: pointer;">
                                Nama
                                @if($sortField === 'name')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} float-right mt-1"></i>
                                @else
                                    <i class="fas fa-sort float-right mt-1"></i>
                                @endif
                            </th>
                            <th>Processor</th>
                            <th>RAM</th>
                            <th>SSD</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual Disarankan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laptops as $laptop)
                        <tr>
                            <td>{{ $laptop->name }}</td>
                            <td>{{ $laptop->processor }}</td>
                            <td>{{ $laptop->ram }}</td>
                            <td>{{ $laptop->ssd }}</td>
                            <td>Rp {{ number_format($laptop->purchase_price,0,',','.') }}</td>
                            <td class="font-weight-bold text-success">
                                Rp {{ number_format($laptop->suggested_selling_price,0,',','.') }}
                            </td>
                            <td>
                                @if($laptop->is_sold)
                                    <span class="badge bg-success">
                                        Terjual (Rp {{ number_format($laptop->sold_price) }})
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Belum Terjual</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    @canAccess('show','used_laptops')
                                    <a 
                                        href="{{ route('used-laptop.show', $laptop->slug) }}"
                                        class="btn btn-sm btn-info mr-1 mb-1"
                                        title="Detail"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcanAccess
                                    @if($laptop->isAction())
                                    @canAccess('update','used_laptops')
                                    <a 
                                        href="{{ route('used-laptop.edit', $laptop->slug) }}"
                                        class="btn btn-sm btn-primary mr-1 mb-1"
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcanAccess
                                    @canAccess('destroy','used_laptops')
                                    <form 
                                        method="POST"
                                        action="{{ route('used-laptop.destroy', $laptop->slug) }}"
                                        class="d-inline"
                                        title="Hapus"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger mb-1">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcanAccess
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-laptop fa-2x mb-2 text-muted"></i>
                                <p class="text-muted">Tidak ada data laptop ditemukan</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer clearfix">
            <div class="float-right">
                {{ $laptops->links() }}
            </div>
            <div class="float-left mt-1">
                <select wire:model="perPage" class="form-control form-control-sm">
                    <option value="10">10 per halaman</option>
                    <option value="25">25 per halaman</option>
                    <option value="50">50 per halaman</option>
                    <option value="100">100 per halaman</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Form Modal -->
    <div class="modal fade" id="formModal" tabindex="-1" role="dialog" 
         aria-labelledby="formModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="formModalLabel">
                        {{ $laptopId ? 'Edit Laptop' : 'Tambah Laptop Baru' }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveLaptop">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nama Laptop</label>
                                    <input type="text" class="form-control" id="name" wire:model="name">
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="processor">Processor</label>
                                    <input type="text" class="form-control" id="processor" wire:model="processor">
                                    @error('processor') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="ram">RAM</label>
                                    <input type="text" class="form-control" id="ram" wire:model="ram">
                                    @error('ram') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="ssd">SSD</label>
                                    <input type="text" class="form-control" id="ssd" wire:model="ssd">
                                    @error('ssd') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gpu">GPU</label>
                                    <input type="text" class="form-control" id="gpu" wire:model="gpu">
                                </div>
                                
                                <div class="form-group">
                                    <label for="operating_system">Sistem Operasi</label>
                                    <input type="text" class="form-control" id="operating_system" 
                                           wire:model="operating_system">
                                </div>
                                
                                <div class="form-group">
                                    <label for="purchase_price">Harga Beli (Rp)</label>
                                    <input type="text" class="form-control"  id="amount_show" placeholder="Rp 30.000.000" oninput="formatRupiahFormat(this,'amount')" required/>
                                    <input type="hidden" id="amount" name="amount" name="name"  value="{{ old('amount') ?? @$divisionBudget->amount }}" >
                                    @error('purchase_price') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="notes">Catatan</label>
                                    <input class="thriveEditor form-control" id="description_notes" data-ids="notes" wire:model="notes" placeholder="yang akan dicetak di perjanjian" />
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="is_sold" wire:model="is_sold">
                            <label class="form-check-label" for="is_sold">
                                Laptop sudah terjual
                            </label>
                        </div>
                        
                        @if($is_sold)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sold_price">Harga Jual (Rp)</label>
                                    <input type="number" class="form-control" id="sold_price" 
                                           wire:model="sold_price">
                                    @error('sold_price') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sold_at">Tanggal Terjual</label>
                                    <input type="date" class="form-control" id="sold_at" 
                                           wire:model="sold_at" max="{{ date('Y-m-d') }}">
                                    @error('sold_at') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" wire:click="saveLaptop">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" 
         aria-labelledby="detailModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="detailModalLabel">
                        Detail Laptop
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if($currentLaptop)
                    <div class="row">
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between">
                                <h4>{{ $currentLaptop->name }}</h4>
                                <span class="badge {{ $currentLaptop->is_sold ? 'bg-success' : 'bg-secondary' }} p-2">
                                    {{ $currentLaptop->sale_status }}
                                </span>
                            </div>
                            
                            <div class="mt-3">
                                <h5>Spesifikasi</h5>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="spec-item mb-3">
                                            <i class="fas fa-microchip text-primary mr-2"></i>
                                            <strong>Processor:</strong> {{ $currentLaptop->processor }}
                                        </div>
                                        <div class="spec-item mb-3">
                                            <i class="fas fa-memory text-primary mr-2"></i>
                                            <strong>RAM:</strong> {{ $currentLaptop->ram }}
                                        </div>
                                        <div class="spec-item mb-3">
                                            <i class="fas fa-hdd text-primary mr-2"></i>
                                            <strong>SSD:</strong> {{ $currentLaptop->ssd }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        @if($currentLaptop->gpu)
                                        <div class="spec-item mb-3">
                                            <i class="fas fa-gamepad text-primary mr-2"></i>
                                            <strong>GPU:</strong> {{ $currentLaptop->gpu }}
                                        </div>
                                        @endif
                                        <div class="spec-item mb-3">
                                            <i class="fas fa-window-restore text-primary mr-2"></i>
                                            <strong>OS:</strong> {{ $currentLaptop->operating_system ?? '-' }}
                                        </div>
                                        <div class="spec-item mb-3">
                                            <i class="fas fa-money-bill-wave text-primary mr-2"></i>
                                            <strong>Harga Beli:</strong> Rp {{ number_format($currentLaptop->purchase_price) }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-light p-3 rounded mt-3">
                                    <i class="fas fa-calculator text-success mr-2"></i>
                                    <strong>Harga Jual Disarankan:</strong> 
                                    <span class="text-success font-weight-bold">
                                        Rp {{ number_format($currentLaptop->suggested_selling_price,0,',','.') }}
                                    </span>
                                    <small class="text-muted d-block mt-1">
                                        (Harga beli + perbaikan) + 30%
                                    </small>
                                </div>
                                
                                @if($currentLaptop->notes)
                                <div class="mt-3">
                                    <h5>Catatan</h5>
                                    <p class="text-muted">{{ $currentLaptop->notes }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-4 border-left">
                            <h5>Riwayat</h5>
                            
                            @if($currentLaptop->is_sold)
                            <div class="sale-info mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-check-circle fa-2x text-success mr-2"></i>
                                    <div>
                                        <div class="font-weight-bold">Terjual</div>
                                        <div class="text-muted">
                                            {{ $currentLaptop->sold_at->format('d M Y') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4 pl-3">
                                    <div class="text-success font-weight-bold">
                                        Rp {{ number_format($currentLaptop->sold_price) }}
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            <div class="history-section">
                                <h6>Perbaikan</h6>
                                @if($currentLaptop->repairs->count())
                                    <ul class="list-group">
                                        @foreach($currentLaptop->repairs as $repair)
                                        <li class="list-group-item d-flex justify-content-between">
                                            <div>{{ $repair->description }}</div>
                                            <div class="text-danger">Rp {{ number_format($repair->cost) }}</div>
                                        </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted">Tidak ada perbaikan</p>
                                @endif
                            </div>
                            
                            <div class="history-section mt-3">
                                <h6>Pemeriksaan</h6>
                                @if($currentLaptop->checks->count())
                                    <ul class="list-group">
                                        @foreach($currentLaptop->checks as $check)
                                        <li class="list-group-item">
                                            <div class="d-flex justify-content-between">
                                                <div>{{ $check->checked_at->format('d M Y') }}</div>
                                                <div>
                                                    <span class="badge {{ $check->condition === 'good' ? 'bg-success' : 'bg-warning' }}">
                                                        {{ $check->condition === 'good' ? 'Baik' : 'Perlu Perbaikan' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mt-1 text-muted small">{{ $check->notes }}</div>
                                        </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted">Tidak ada pemeriksaan</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" 
         aria-labelledby="deleteModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="deleteModalLabel">Konfirmasi Penghapusan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus laptop ini?</p>
                    <p class="font-weight-bold">Data yang dihapus tidak dapat dikembalikan!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteLaptop">
                        <i class="fas fa-trash mr-1"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
    

    @push('scripts')
    <script>
        document.addEventListener('livewire:load', function () {
            // Show form modal
            window.livewire.on('showFormModal', () => {
                $('#formModal').modal('show');
            });
            
            // Hide form modal
            window.livewire.on('hideFormModal', () => {
                $('#formModal').modal('hide');
            });
            
            // Show detail modal
            window.livewire.on('showDetailModal', () => {
                $('#detailModal').modal('show');
            });
            
            // Hide detail modal
            window.livewire.on('hideDetailModal', () => {
                $('#detailModal').modal('hide');
            });
            
            // Show delete modal
            window.livewire.on('showDeleteModal', () => {
                $('#deleteModal').modal('show');
            });
            
            // Hide delete modal
            window.livewire.on('hideDeleteModal', () => {
                $('#deleteModal').modal('hide');
            });
        });
    </script>
    @endpush
</div>