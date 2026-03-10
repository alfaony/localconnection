<div class="row">
    <!-- PIC User ID -->
    <div class="col-md-12">
        <div class="form-group">
            <label for="pic_user_id">PIC (Person In Charge) <span class="text-danger">*</span></label>
            <select class="form-control select2 @error('pic_user_id') is-invalid @enderror" 
                    id="pic_user_id" name="pic_user_id" required>
                <option value="">-- Select PIC User --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" 
                        {{ old('pic_user_id', $partner->pic_user_id ?? '') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
            @error('pic_user_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Name -->
    <div class="col-md-12">
        <div class="form-group">
            <label for="name">Partner Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                   id="name" name="name" 
                   value="{{ old('name', $partner->name ?? '') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Partner Type -->
    <div class="col-md-6">
        <div class="form-group">
            <label for="partner_type_id">Partner Type <span class="text-danger">*</span></label>
            <select class="form-control @error('partner_type_id') is-invalid @enderror" 
                    id="partner_type_id" name="partner_type_id" required>
                <option value="">-- Select Type --</option>
                @foreach($partnerTypes as $type)
                    <option value="{{ $type->id }}" 
                        {{ old('partner_type_id', $partner->partner_type_id ?? '') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
            @error('partner_type_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Industry -->
    <div class="col-md-6">
        <div class="form-group">
            <label for="industry">Industry</label>
            <input type="text" class="form-control @error('industry') is-invalid @enderror" 
                   id="industry" name="industry" 
                   value="{{ old('industry', $partner->industry ?? '') }}">
            @error('industry')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Website -->
    <div class="col-md-12">
        <div class="form-group">
            <label for="website">Website</label>
            <input type="url" class="form-control @error('website') is-invalid @enderror" 
                   id="website" name="website" 
                   placeholder="https://example.com"
                   value="{{ old('website', $partner->website ?? '') }}">
            @error('website')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Status -->
    <div class="col-md-4">
        <div class="form-group">
            <label for="status">Status <span class="text-danger">*</span></label>
            <select class="form-control @error('status') is-invalid @enderror" 
                    id="status" name="status" required>
                @foreach($statuses as $key => $value)
                    <option value="{{ $key }}" 
                        {{ old('status', $partner->status ?? 'active') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Partnership Started At -->
    <div class="col-md-4">
        <div class="form-group">
            <label for="partnership_started_at">Partnership Started</label>
            <input type="date" class="form-control @error('partnership_started_at') is-invalid @enderror" 
                   id="partnership_started_at" name="partnership_started_at" 
                   value="{{ old('partnership_started_at', $partner ? $partner->partnership_started_at?->format('Y-m-d') : '') }}">
            @error('partnership_started_at')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Is Certified -->
    <div class="col-md-4">
        <div class="form-group">
            <label>Certified</label><br>
            <div class="icheck-primary">
                <input type="checkbox" id="is_certified" name="is_certified" value="1"
                    {{ old('is_certified', $partner->is_certified ?? false) ? 'checked' : '' }}>
                <label for="is_certified">
                    Is Certified Partner
                </label>
            </div>
        </div>
    </div>

    <!-- Certification Level -->
    <div class="col-md-6">
        <div class="form-group">
            <label for="certification_level">Certification Level</label>
            <select class="form-control @error('certification_level') is-invalid @enderror" 
                    id="certification_level" name="certification_level">
                <option value="">-- None --</option>
                @foreach($certificationLevels as $key => $value)
                    <option value="{{ $key }}" 
                        {{ old('certification_level', $partner->certification_level ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('certification_level')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Certified At -->
    <div class="col-md-6">
        <div class="form-group">
            <label for="certified_at">Certified Date</label>
            <input type="date" class="form-control @error('certified_at') is-invalid @enderror" 
                   id="certified_at" name="certified_at" 
                   value="{{ old('certified_at', $partner ? $partner->certified_at?->format('Y-m-d') : '') }}">
            @error('certified_at')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Certification File -->
    <div class="col-md-12">
        <div class="form-group">
            <label for="certification_file">Certification File</label>
            @if($partner && $partner->certification_file)
                <div class="mb-2">
                    <small class="text-muted">Current file: 
                        <a href="{{ Storage::disk('s3')->url($partner->certification_file) }}" target="_blank">
                            <i class="fas fa-file-pdf"></i> View Certificate
                        </a>
                    </small>
                </div>
            @endif
            <div class="custom-file">
                <input type="file" class="custom-file-input @error('certification_file') is-invalid @enderror" 
                       id="certification_file" name="certification_file" accept=".pdf,.jpg,.jpeg,.png">
                <label class="custom-file-label" for="certification_file">Choose file</label>
                @error('certification_file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <small class="form-text text-muted">Accepted formats: PDF, JPG, PNG (Max: 5MB)</small>
        </div>
    </div>
</div>

@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for PIC user dropdown
        $('#pic_user_id').select2({
            placeholder: '-- Select PIC User --',
            allowClear: true,
            width: '100%'
        });

        // Initialize Select2 with tags for Partner Type
        $('#partner_type_id').select2({
            placeholder: '-- Select Type --',
            allowClear: true,
            tags: true,
            width: '100%'
        });

        // Update custom file input label with filename
        $('#certification_file').on('change', function() {
            const fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName || 'Choose file');
        });
    });

    // Toggle certification fields based on is_certified checkbox
    document.getElementById('is_certified').addEventListener('change', function() {
        const certificationLevel = document.getElementById('certification_level');
        const certifiedAt = document.getElementById('certified_at');
        
        if (!this.checked) {
            certificationLevel.value = '';
            certifiedAt.value = '';
        }
    });
</script>
@stop  

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>

    /* ============================================ */
    /* SELECT2 CUSTOM STYLING */
    /* ============================================ */
    
    /* Container */
    .select2-container {
        width: 100% !important;
    }

    /* Single Selection Box */
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        padding: 0.375rem 0.75rem;
    }

    /* Text Displayed */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 24px !important;
        padding-left: 0 !important;
        color: #495057;
    }

    /* Arrow Dropdown */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        top: 1px !important;
        right: 1px !important;
    }

    /* Placeholder */
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6c757d;
    }

    /* Dropdown Results */
    .select2-container--default .select2-results__option {
        padding: 8px 12px;
    }

    /* Hover State */
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #007bff !important;
        color: white;
    }

    /* Selected Option */
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #e9ecef;
    }

    /* Disabled State */
    .select2-container--default.select2-container--disabled .select2-selection--single {
        background-color: #e9ecef !important;
        cursor: not-allowed !important;
        border-color: #ced4da !important;
    }

    /* Focus State */
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #80bdff !important;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
    }

    /* Dropdown */
    .select2-dropdown {
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.175);
    }

    /* Search Box in Dropdown */
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 6px 12px;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        outline: none;
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    /* Clear Button */
    .select2-container--default .select2-selection--single .select2-selection__clear {
        margin-right: 10px;
        font-size: 1.2em;
    }
</style>
@stop
