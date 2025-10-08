@extends('adminlte::page')


@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('daily_task_project.index') }}">Proyek</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $project->name ?? '' }}</li>
    </ol>
</nav>
<div class="card p-3">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <div class="card-body mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3>{{ $project->name }}</h3>
            @canAccess('customfieldstore','daily_task_projects')
            <button class="btn btn-primary" data-toggle="modal" data-target="#createCustomFieldModal"><i class="fa fa-plus"></i> Custom Field</button>
            @endcanAccess
        </div>
        <div class="card-body">
            <h5>Proyek</h5>
            <ul class="list-group mb-3">
                @foreach($project->projects as $a)
                    <li class="list-group-item">{{ $a->title }}</li>
                @endforeach
            </ul>
        </div>
        <div class="card-body">
            <h5>Custom Fields</h5>
            <ul class="list-group mb-3">
                @foreach($project->customFields as $customField)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <strong>{{ $customField->name }}</strong> ({{ $customField->type == 'single_select' ? 'Single Select' : 'Multi Select' }})
                            <ul class="list-unstyled">
                                @foreach($customField->values->sortBy('ordering') as $value)
                                    <li>{{ $value->value }}</li>
                                @endforeach
                            </ul>
                        </span>
                        <span>
                            @if($isCustomfieldupdate)
                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editCustomFieldModal{{ $customField->id }}"><i class="fa fa-edit"></i></button>
                            @endif
                            @if($isCustomfielddestroy)
                            <form action="{{ route('customfielddestroy', $customField->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this custom field?')"><i class="fa fa-trash"></i></button>
                            </form>
                            @endif
                        </span>
                    </li>

                    <!-- Edit Custom Field Modal -->
                    @if($isCustomfieldupdate)
                    <div class="modal fade" id="editCustomFieldModal{{ $customField->id }}" tabindex="-1" role="dialog" aria-labelledby="editCustomFieldModalLabel{{ $customField->id }}" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <form action="{{ route('customfieldupdate', $customField->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editCustomFieldModalLabel{{ $customField->id }}">Edit Custom Field</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="custom_field_name">Nama Custom Field</label>
                                            <input type="text" class="form-control" name="custom_field_name" value="{{ $customField->name }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="custom_field_type">Tipe</label>
                                            <select class="form-control" name="custom_field_type" disabled required>
                                                @foreach($statusSelect as $key => $value)
                                                <option value="{{ $key }}" {{ $customField->type == $key ? 'selected' : '' }}>{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div id="custom-field-values-container{{ $customField->id }}">
                                            <label for="custom_field_value">Values</label>
                                            @foreach($customField->values->sortBy('ordering') as $value)
                                                <div class="form-group d-flex">
                                                    <input type="text" class="form-control custom-field-value" name="custom_field_value[]" value="{{ $value->value }}" required>
                                                    <button type="button" class="btn btn-danger btn-sm ml-2 remove-custom-field-value"><i class="fa fa-trash"></i></button>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" class="btn btn-secondary add-custom-field-value btn-sm" data-id="{{ $customField->id }}"><i class="fa fa-plus"></i> Tambah Value</button>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
</div>

@if($isCustomfieldstore)
<!-- Create Custom Field Modal -->
<div class="modal fade" id="createCustomFieldModal" tabindex="-1" role="dialog" aria-labelledby="createCustomFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="createCustomFieldForm" action="{{ route('customfieldstore', $project->slug) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCustomFieldModalLabel">Tambah Custom Field</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="custom_field_name">Nama Custom Field</label>
                        <input type="text" class="form-control" id="custom_field_name" name="custom_field_name" required>
                    </div>
                    <div class="form-group">
                        <label for="custom_field_type">Tipe</label>
                        <select class="form-control" id="custom_field_type" name="custom_field_type" required>
                            @foreach($statusSelect as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="custom-field-values-container">
                        <label for="custom_field_value">Values</label>
                        <div class="form-group d-flex">
                            <input type="text" class="form-control custom-field-value" name="custom_field_value[]" required>
                            <button type="button" class="btn btn-danger ml-2 remove-custom-field-value"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary add-custom-field-value"><i class="fa fa-plus"></i> Option</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        function addCustomFieldValue(index) {
            return `
                <div class="form-group d-flex">
                    <input type="text" class="form-control custom-field-value" name="custom_field_value[]" required>
                    <button type="button" class="btn btn-danger ml-2 remove-custom-field-value"><i class="fa fa-trash"></i></button>
                </div>
            `;
        }

        // Add option in create modal
        $(document).on('click', '.add-custom-field-value', function() {
            const containerId = $(this).data('id') ? `#custom-field-values-container${$(this).data('id')}` : '#custom-field-values-container';
            $(containerId).append(addCustomFieldValue());
        });

        // Remove option
        $(document).on('click', '.remove-custom-field-value', function() {
            $(this).closest('.form-group').remove();
        });
    });
</script>
@endsection