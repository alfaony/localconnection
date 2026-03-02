<div class="form-group">
    <label>Key Result</label>
    @if($hasHead)
        <ul>
            @foreach($keyResult as $value)
                @if(in_array($value->id, $selectedKeyResults))
                    <li>{{ $value->result }}</li>
                @endif
            @endforeach
        </ul>
    @else
        <select id="keyresult-select-{{ $index }}" name="key_result_{{ $index }}[]" class="keyresult-select form-control select2-multiple select2-multiple-{{ $index }}" multiple required>
            @foreach($keyResult as $value)
                <option value="{{ $value->id }}"
                    {{ in_array($value->id, $selectedKeyResults) ? 'selected' : '' }}>
                    {{ $value->result }}
                </option>
            @endforeach
        </select>
    @endif
</div>
