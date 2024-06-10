<div class="form-group">
    <label>Key Result</label>
    <select name="key_result_{{ $index }}[]" class="form-control select2-multiple select2-multiple-{{ $index }}" multiple required>
        @foreach($keyResult as $value)
            <option value="{{ $value->id }}" {{ in_array($value->id, $selectedKeyResults) ? 'selected' : '' }}>
                {{ $value->result }}
            </option>
        @endforeach
    </select>
</div>
