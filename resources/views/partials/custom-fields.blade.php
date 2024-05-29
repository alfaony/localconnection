@foreach($customFields as $field)
    <div class="form-group">
        <label>{{ $field->name }}</label>
        @if($field->type == 'single_select')
            <select name="custom_field_values[{{ $field->id }}]" class="form-control select2-single select2-single-{{ $index }}">
                <option disabled selected>Pilih {{ $field->name }}</option>
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