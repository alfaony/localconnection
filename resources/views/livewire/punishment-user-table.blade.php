<div>

    @section('title', 'Punishment Management')

    @section('content_header')
        <h1>Punishment Management</h1>
        <div class="row mt-2">
            <div class="col-md-12">
                <x-adminlte-button label="Reset Filters" theme="outline-danger" icon="fas fa-sync" onclick="resetFilters()" class="btn-sm"/>
            </div>
        </div>
    @stop

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="search" placeholder="Search user..." wire:model.lazy="search" igroup-size="sm">
                        <x-slot name="appendSlot">
                            <x-adminlte-button theme="outline-primary" icon="fas fa-search" wire:click="applyFilters"/>
                        </x-slot>
                    </x-adminlte-input>
                </div>
                <div class="col-md-3">
                    <x-adminlte-input-date name="startDate" wire:model.lazy="startDate" igroup-size="sm" 
                        placeholder="Start Date" />
                </div>
                <div class="col-md-3">
                    <x-adminlte-input-date name="endDate" wire:model.lazy="endDate" igroup-size="sm" 
                        placeholder="End Date" />
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Daily Task</th>
                        <th>Points</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($punishments as $punishment)
                    <tr>
                        <td>{{ $punishment->id }}</td>
                        <td>{{ $punishment->user->name ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('dailytask.show', $punishment->dailytask->slug)}}">{{ $punishment->dailytask->name ?? 'N/A' }}</a>
                        </td>
                        <td><span class="badge bg-danger">{{ $punishment->point }}</span></td>
                        <td>{{ $punishment->created_at->format('d M Y, H:i') }}</td>
                        <td>
                            <button class="btn btn-xs btn-default text-danger mx-1 shadow" 
                                onclick="if (confirm('Apakah kamu yakin ingin menghapus data ini?')) { 
                                    @this.call('delete', {{ $punishment->id }}) 
                                }"
                                title="Delete">
                                <i class="fa fa-lg fa-fw fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No punishment records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer clearfix">
            <div class="float-left">
                <select class="form-control form-control-sm" wire:model="perPage" style="width: 80px">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="float-right">
                {{ $punishments->links() }}
            </div>
        </div>
    </div>

    @section('css')
        <link rel="stylesheet" href="/css/admin_custom.css">
        <style>
            .badge {
                font-size: 0.9em;
                padding: 0.35em 0.65em;
            }
        </style>
    @stop

    @section('js')
        <script>
            document.addEventListener('livewire:load', function () {
                // Config for datepicker
                const config = {
                    format: 'YYYY-MM-DD',
                    showClose: true,
                    showClear: true,
                    icons: {
                        time: 'fa fa-clock',
                        date: 'fa fa-calendar',
                        up: 'fa fa-arrow-up',
                        down: 'fa fa-arrow-down',
                        previous: 'fa fa-chevron-left',
                        next: 'fa fa-chevron-right',
                        today: 'fa fa-calendar-check',
                        clear: 'fa fa-trash',
                        close: 'fa fa-times'
                    }
                };

                // Initialize datepickers
                $('#startDate').datetimepicker(config);
                $('#endDate').datetimepicker(config);

                // Event when date changes
                $('#startDate').on("change.datetimepicker", function (e) {
                    @this.set('startDate', e.date.format('YYYY-MM-DD'));
                });
                $('#endDate').on("change.datetimepicker", function (e) {
                    @this.set('endDate', e.date.format('YYYY-MM-DD'));
                });
            });

            function resetFilters() {
                Livewire.emit('resetFilters');
            }

            // Confirm before delete
            window.addEventListener('swal:modal', event => {
                Swal.fire({
                    title: event.detail.title,
                    text: event.detail.text,
                    icon: event.detail.type,
                    confirmButtonText: 'OK'
                });
            });

            // Livewire hook to reinitialize datepickers after Livewire update
            document.addEventListener('livewire:update', function () {
                $('[wire\:ignore]').each(function() {
                    $(this).find('.datepicker').datetimepicker('destroy');
                    $(this).find('.datepicker').datetimepicker(config);
                });
            });
        </script>
    @stop
</div>
