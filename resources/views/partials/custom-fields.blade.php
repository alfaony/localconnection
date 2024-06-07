<div class="form-group">
    <label> Data Proyek </label>
    @if($project->projects->isEmpty())
        <span class="alert alert-warning d-block">
            <strong>Perhatian!</strong> Tidak memiliki data proyek. Hubungi Manager atau Admin.
        </span>
    @else
    <select name="data_project_id[]" class="form-control select2-single select2-single-{{ $index }}" required>
        <option value="" disabled selected>Pilih -- Data Proyek --</option>
        @foreach($project->projects as $a)
            <option value="{{ $a->id }}" {{ isset($dataProyek) && $dataProyek == $a->id ? 'selected' : '' }}>
                {{ $a->title }}
            </option>
        @endforeach
    </select>
    @endif
</div>
@foreach($customFields as $field)
    <div class="form-group">
        <label>{{ $field->name }}</label>
        @if($field->type == 'single_select')
            <select name="custom_field_values[{{ $field->id }}]" class="form-control select2-single select2-single-{{ $index }}">
                <option disabled selected>Pilih -- {{ $field->name }} --</option>
                @foreach($field->values->sortBy('ordering') as $value)
                    <option value="{{ $value->id }}" {{ isset($selectedValues[$field->id]) && in_array($value->id, $selectedValues[$field->id]) ? 'selected' : '' }}>
                        {{ $value->value }}
                    </option>
                @endforeach
            </select>
        @else
            <select name="custom_field_values[{{ $field->id }}][]" class="form-control select2-multiple select2-multiple-{{ $index }}" multiple>
                @foreach($field->values->sortBy('ordering') as $value)
                    <option value="{{ $value->id }}" {{ isset($selectedValues[$field->id]) && in_array($value->id, $selectedValues[$field->id]) ? 'selected' : '' }}>
                        {{ $value->value }}
                    </option>
                @endforeach
            </select>
        @endif
    </div>
@endforeach