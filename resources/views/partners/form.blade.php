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
            <label for="partner_type">Partner Type <span class="text-danger">*</span></label>
            <select class="form-control @error('partner_type') is-invalid @enderror" 
                    id="partner_type" name="partner_type" required>
                <option value="">-- Select Type --</option>
                @foreach($partnerTypes as $key => $value)
                    <option value="{{ $key }}" 
                        {{ old('partner_type', $partner->partner_type ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('partner_type')
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

        // Update custom file input label with filename
        $('#certification_file').on('change', function() {
            console.log("okkkss");
            
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

    .select2-dropdown {
        width: 100% !important;
        max-width: 100% !important;
        overflow: hidden !important;
    }
</style>
@stop
