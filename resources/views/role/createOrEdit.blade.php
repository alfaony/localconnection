@extends('adminlte::page')

@section('content_header')
    <h1>Role Management - {{ @$role ? 'Edit: ' . $role->name : 'Create New Role' }}</h1>
@stop

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            {{-- Role Name Card --}}
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Role Information</h3>
                </div>
                <div class="card-body">
                    @if(@$role)
                        {{-- EDIT MODE: Update name via AJAX --}}
                        <form id="roleNameForm">
                            @csrf
                            <div class="form-group">
                                <label>Role Name</label>
                                @if(@$is_editable)
                                <div class="input-group">
                                    <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-success" id="saveNameBtn">
                                            <i class="fa fa-save"></i> Update Name
                                        </button>
                                    </div>
                                </div>
                                @else
                                <input type="text" class="form-control" value="{{ $role->name }}" disabled>
                                @endif
                            </div>
                        </form>
                    @else
                        {{-- CREATE MODE: Standard form submit --}}
                        <form action="{{ route('role.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Role Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Enter role name" required>
                                <small class="form-text text-muted">
                                    After creating the role, you can assign permissions in the next step.
                                </small>
                            </div>
                            <div class="text-right">
                                <a href="{{ route('role.index') }}" class="btn btn-secondary">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Create Role
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Permissions Card - HANYA TAMPIL JIKA ROLE SUDAH ADA --}}
            @if(@$role)
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">Permissions by Menu</h3>
                    <div class="card-tools">
                        <span class="badge badge-light" id="totalCounter">
                            <i class="fa fa-check-square"></i> Loading...
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Global Actions --}}
                    @if(@$is_editable)
                    <div class="mb-3">
                        <button type="button" class="btn btn-success btn-sm" id="selectAllGlobal">
                            <i class="fa fa-check-square"></i> Select All Permissions
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" id="deselectAllGlobal">
                            <i class="fa fa-square"></i> Deselect All Permissions
                        </button>
                        <small class="text-muted ml-2">
                            <i class="fa fa-info-circle"></i> Or use Save button per menu section below
                        </small>
                    </div>
                    @endif

                    {{-- Accordion per Menu --}}
                    <div id="accordion">
                        @foreach($mainMenus as $menu)
                        <div class="card mb-2">
                            <div class="card-header p-2" id="heading-{{ $menu }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-link text-left flex-grow-1" data-toggle="collapse" data-target="#collapse-{{ $menu }}">
                                        <i class="fa fa-folder"></i> 
                                        <strong>{{ ucwords(str_replace('_', ' ', $menu)) }}</strong>
                                        <span class="badge badge-info ml-2 menu-counter" data-menu="{{ $menu }}">0/0</span>
                                    </button>
                                    
                                    @if(@$is_editable && isset($dataPermission[$menu]))
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-success select-menu-all" data-menu="{{ $menu }}">
                                            <i class="fa fa-check"></i> All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning deselect-menu-all" data-menu="{{ $menu }}">
                                            <i class="fa fa-times"></i> None
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary save-menu-btn" data-menu="{{ $menu }}">
                                            <i class="fa fa-save"></i> Save
                                        </button>
                                    </div>
                                    <span class="badge badge-success ml-2 menu-status" data-menu="{{ $menu }}" style="display:none;">
                                        <i class="fa fa-check"></i> Saved
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div id="collapse-{{ $menu }}" class="collapse" data-parent="#accordion">
                                <div class="card-body">
                                    @if(isset($dataPermission[$menu]))
                                    <div class="row">
                                        @foreach($dataPermission[$menu] as $permission)
                                        <div class="col-md-6">
                                            <div class="custom-control custom-checkbox">
                                                @if(@$is_editable)
                                                <input type="checkbox" 
                                                       class="custom-control-input permission-checkbox" 
                                                       id="permission-{{ $permission['id'] }}"
                                                       data-menu="{{ $menu }}"
                                                       value="{{ $permission['id'] }}"
                                                       {{ isset($rolePermissions[$permission['id']]) ? 'checked' : '' }}>
                                                @else
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="permission-{{ $permission['id'] }}"
                                                       disabled
                                                       {{ isset($rolePermissions[$permission['id']]) ? 'checked' : '' }}>
                                                @endif
                                                <label class="custom-control-label" for="permission-{{ $permission['id'] }}">
                                                    {{ $permission['name'] }}
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @else
                                    <p class="text-muted">No permissions available for this menu.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
                {{-- Info box untuk create mode --}}
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    <strong>Note:</strong> After creating the role, you will be redirected to assign permissions.
                </div>
            @endif

            {{-- Back Button --}}
            <div class="text-right mt-3">
                <a href="{{ route('role.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
@if(@$role)
{{-- JavaScript HANYA diload saat EDIT mode --}}
<script>
$(document).ready(function() {
    
    // Update role name
    $('#roleNameForm').submit(function(e) {
        e.preventDefault();
        
        var roleName = $('input[name="name"]').val();
        $('#saveNameBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: '{{ route("role.update-name", $role) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                name: roleName
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                    $('#saveNameBtn').html('<i class="fa fa-check"></i> Saved');
                    setTimeout(function() {
                        $('#saveNameBtn').html('<i class="fa fa-save"></i> Update Name').prop('disabled', false);
                    }, 2000);
                }
            },
            error: function(xhr) {
                showNotification('danger', 'Error: ' + (xhr.responseJSON?.message || 'Unknown error'));
                $('#saveNameBtn').html('<i class="fa fa-save"></i> Update Name').prop('disabled', false);
            }
        });
    });
    
    // Select all in a menu
    $('.select-menu-all').click(function() {
        var menu = $(this).data('menu');
        $('.permission-checkbox[data-menu="' + menu + '"]').prop('checked', true);
        updateMenuCounter(menu);
    });
    
    // Deselect all in a menu
    $('.deselect-menu-all').click(function() {
        var menu = $(this).data('menu');
        $('.permission-checkbox[data-menu="' + menu + '"]').prop('checked', false);
        updateMenuCounter(menu);
    });
    
    // Save menu permissions
    $('.save-menu-btn').click(function() {
        var menu = $(this).data('menu');
        var $btn = $(this);
        var $status = $('.menu-status[data-menu="' + menu + '"]');
        
        // Get selected permissions for this menu
        var permissions = [];
        $('.permission-checkbox[data-menu="' + menu + '"]:checked').each(function() {
            permissions.push($(this).val());
        });
        
        console.log('Saving menu:', menu, 'Permissions:', permissions.length);
        
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        $status.hide();
        
        $.ajax({
            url: '{{ route("role.update-menu-permissions", $role) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                menu: menu,
                permissions: permissions
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                    $btn.html('<i class="fa fa-check"></i> Saved');
                    $status.show().fadeOut(3000);
                    
                    setTimeout(function() {
                        $btn.html('<i class="fa fa-save"></i> Save').prop('disabled', false);
                    }, 2000);
                    
                    updateTotalCounter();
                }
            },
            error: function(xhr) {
                showNotification('danger', 'Error saving ' + menu + ': ' + (xhr.responseJSON?.message || 'Unknown error'));
                $btn.html('<i class="fa fa-save"></i> Save').prop('disabled', false);
            }
        });
    });
    
    // Global select all
    $('#selectAllGlobal').click(function() {
        if (!confirm('This will assign ALL permissions to this role. Continue?')) {
            return;
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: '{{ route("role.select-all", $role) }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    $('.permission-checkbox').prop('checked', true);
                    updateAllCounters();
                    showNotification('success', response.message);
                }
                $btn.html('<i class="fa fa-check-square"></i> Select All Permissions').prop('disabled', false);
            },
            error: function(xhr) {
                showNotification('danger', 'Error: ' + (xhr.responseJSON?.message || 'Unknown error'));
                $btn.html('<i class="fa fa-check-square"></i> Select All Permissions').prop('disabled', false);
            }
        });
    });
    
    // Global deselect all
    $('#deselectAllGlobal').click(function() {
        if (!confirm('This will remove ALL permissions from this role. Continue?')) {
            return;
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: '{{ route("role.deselect-all", $role) }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    $('.permission-checkbox').prop('checked', false);
                    updateAllCounters();
                    showNotification('success', response.message);
                }
                $btn.html('<i class="fa fa-square"></i> Deselect All Permissions').prop('disabled', false);
            },
            error: function(xhr) {
                showNotification('danger', 'Error: ' + (xhr.responseJSON?.message || 'Unknown error'));
                $btn.html('<i class="fa fa-square"></i> Deselect All Permissions').prop('disabled', false);
            }
        });
    });
    
    // Update counter when checkbox changes
    $('.permission-checkbox').change(function() {
        var menu = $(this).data('menu');
        updateMenuCounter(menu);
        updateTotalCounter();
    });
    
    // Update menu counter
    function updateMenuCounter(menu) {
        var total = $('.permission-checkbox[data-menu="' + menu + '"]').length;
        var checked = $('.permission-checkbox[data-menu="' + menu + '"]:checked').length;
        $('.menu-counter[data-menu="' + menu + '"]').text(checked + '/' + total);
    }
    
    // Update total counter
    function updateTotalCounter() {
        var total = $('.permission-checkbox').length;
        var checked = $('.permission-checkbox:checked').length;
        $('#totalCounter').html('<i class="fa fa-check-square"></i> ' + checked + ' / ' + total + ' permissions selected');
    }
    
    // Update all counters
    function updateAllCounters() {
        @foreach($mainMenus as $menu)
        updateMenuCounter('{{ $menu }}');
        @endforeach
        updateTotalCounter();
    }
    
    // Show notification
    function showNotification(type, message) {
        var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert" style="position:fixed;top:70px;right:20px;z-index:9999;min-width:300px;">' +
            message +
            '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
            '</div>';
        $('body').append(alertHtml);
        
        setTimeout(function() {
            $('.alert').fadeOut(function() { $(this).remove(); });
        }, 5000);
    }
    
    // Initialize counters
    updateAllCounters();
});
</script>
@endif
@stop

@section('css')
<style>
    .card-header .btn-link {
        text-decoration: none;
        color: #333;
    }
    .card-header .btn-link:hover {
        color: #007bff;
    }
    .menu-counter {
        font-size: 12px;
    }
    .menu-status {
        animation: fadeIn 0.3s;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .custom-control-label {
        cursor: pointer;
        user-select: none;
    }
</style>
@stop