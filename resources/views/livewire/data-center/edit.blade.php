@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Data Center</h3>
        </div>
        <form wire:submit.prevent="update">
            <div class="card-body">
                <!-- Same form structure as create.blade.php -->
                <!-- Copy form from create view and change wire:model to match edit component -->
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('data-centers.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
@endpush