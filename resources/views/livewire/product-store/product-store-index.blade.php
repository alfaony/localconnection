<div>
    <div class="row">
        <div class="col-md-12 mt-3">
            <div class="card">
                <div class="card-header bg-gradient-primary">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3 class="card-title text-white">
                                <i class="fas fa-boxes mr-2"></i> Product Store
                            </h3>
                        </div>
                        <div class="col-md-6 text-right">
                            <button wire:click="createProduct" class="btn btn-light btn-sm">
                                <i class="fas fa-plus-circle mr-1"></i> Add New Product
                            </button>
                        </div>
                    </div>
                </div>
        
                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <i class="icon fas fa-check mr-2"></i> {{ session('message') }}
                        </div>
                    @endif
        
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" wire:model.debounce.300ms="search" placeholder="Search products..." 
                                       class="form-control" placeholder="Search...">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
        
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th wire:click="sortBy('name')" style="cursor: pointer;" class="align-middle">
                                        Name
                                        @if($sortField === 'name')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} float-right"></i>
                                        @else
                                            <i class="fas fa-sort text-muted float-right"></i>
                                        @endif
                                    </th>
                                    <th class="align-middle">Category</th>
                                    <th class="align-middle">Brand</th>
                                    <th class="align-middle">Variant</th>
                                    <th wire:click="sortBy('selling_price')" style="cursor: pointer;" class="align-middle">
                                        Price
                                        @if($sortField === 'selling_price')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} float-right"></i>
                                        @else
                                            <i class="fas fa-sort text-muted float-right"></i>
                                        @endif
                                    </th>
                                    <th class="text-center align-middle" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                <tr>
                                    <td class="align-middle">{{ $product->name }}</td>
                                    <td class="align-middle">
                                        <span class="badge badge-info">{{ $product->category->name ?? '-' }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-secondary">{{ $product->brand->name ?? '-' }}</span>
                                    </td>
                                    <td class="align-middle">{{ $product->variant ?? '-' }}</td>
                                    <td class="align-middle font-weight-bold text-success">
                                        Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('product-store.show', $product->id) }}" class="btn btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button wire:click="editProduct('{{ $product->id }}')" 
                                                    class="btn btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button wire:click="confirmDelete('{{ $product->id }}')" 
                                                    class="btn btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                                        <p class="text-muted">No products found. Add your first product!</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
        
                    <div class="mt-3">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        
            @if ($showFormModal)
                <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-gradient-primary">
                                <h5 class="modal-title text-white">
                                    <i class="fas {{ $selectedProductId ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                                    {{ $selectedProductId ? 'Edit' : 'Create' }} Product
                                </h5>
                                <button type="button" class="close text-white" wire:click="closeForm" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="modal-body p-0">
                                @livewire('product-store.product-store-form')
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>