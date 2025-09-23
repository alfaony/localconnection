<div>
    @section('title', 'Punishment Management')

    @section('content_header')
        <h1>Punishment Management</h1>
    @stop

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="userSelect" class="small">Select User</label>
                        <select id="userSelect" class="form-control form-control-sm" wire:model="selectedUser">
                            <option value="">All Users</option>
                            @foreach($companyUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="startDate" class="small">Start Date</label>
                        <x-adminlte-input-date name="startDate" wire:model.lazy="startDate" igroup-size="sm" 
                            placeholder="Start Date" wire:ignore/>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="endDate" class="small">End Date</label>
                        <x-adminlte-input-date name="endDate" wire:model.lazy="endDate" igroup-size="sm" 
                            placeholder="End Date" wire:ignore/>
                    </div>
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
                            @if($punishment->dailytask)
                                <a href="{{ route('dailytask.show', $punishment->dailytask->slug)}}">
                                    {{ $punishment->dailytask->name }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td><span class="badge bg-danger">{{ $punishment->point }}</span></td>
                        <td>{{ $punishment->created_at->format('d M Y, H:i') }}</td>
                        <td>
                            @canAccess('destroy','punishment_users')
                            <button class="btn btn-xs btn-default text-danger mx-1 shadow" 
                                onclick="confirmDelete({{ $punishment->id }})"
                                title="Delete">
                                <i class="fa fa-lg fa-fw fa-trash"></i>
                            </button>
                            @endcanAccess
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
        <!-- Tempus Dominus CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css" integrity="sha512-3JRrEUwaCkFUBLK1N8HehwQgu8e23jTH4np5NHOmQOobuC4ROQxFwFgBLTnhcnQRMs84muMh0PnnwXlPq5MGjg==" crossorigin="anonymous" />
        <!-- Select2 CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
        <style>
            .badge {
                font-size: 0.9em;
                padding: 0.35em 0.65em;
            }
            .select2-container .select2-selection--single {
                height: calc(1.8125rem + 2px) !important;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: calc(1.8125rem + 2px) !important;
            }
            .select2-container .select2-selection--single .select2-selection__rendered {
                padding-left: 0.75rem !important;
            }
        </style>
    @stop

    @section('js')
        <!-- Moment.js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous"></script>
        <!-- Tempus Dominus JavaScript -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js" integrity="sha512-k6/Bkb8Fxf/c1Tkyl39yJwcOZ1P4cRrJu77p83zJjN2Z55prbFHxPs9vN7q3l3+tSMGPDdoH51AEU8Vgo1cgAA==" crossorigin="anonymous"></script>
        <!-- Select2 JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
        
        <script>
            document.addEventListener('livewire:load', function () {
                initializeSelect2();
                initializeDatePickers();
            });

            function initializeSelect2() {
                $('#userSelect').select2({
                    placeholder: 'Select a user',
                    allowClear: true,
                    width: '100%'
                });

                // Handle select2 change event
                $('#userSelect').on('change', function (e) {
                    @this.set('selectedUser', $(this).val());
                });
            }

            function initializeDatePickers() {
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

                // Set initial dates
                $('#startDate').datetimepicker('date', '{{ $this->startDate }}');
                $('#endDate').datetimepicker('date', '{{ $this->endDate }}');

                // Event when date changes
                $('#startDate').on("change.datetimepicker", function (e) {
                    if (e.date) {
                        @this.set('startDate', e.date.format('YYYY-MM-DD'));
                    }
                });
                
                $('#endDate').on("change.datetimepicker", function (e) {
                    if (e.date) {
                        @this.set('endDate', e.date.format('YYYY-MM-DD'));
                    }
                });
            }

            function resetFilters() {
                // Reset via Livewire
                Livewire.emit('resetFilters');
            }

            function confirmDelete(id) {
                if (confirm('Are you sure you want to delete this punishment record?')) {
                    Livewire.emit('delete', id);
                }
            }

            // Listen for browser events from Livewire
            window.addEventListener('resetSelect2', event => {
                $('#userSelect').val('').trigger('change');
            });

            window.addEventListener('swal:modal', event => {
                Swal.fire({
                    title: event.detail.title,
                    text: event.detail.text,
                    icon: event.detail.type,
                    confirmButtonText: 'OK'
                });
            });

            // Reinitialize when Livewire updates
            document.addEventListener('livewire:update', function () {
                // Reinitialize select2 if it's destroyed during update
                if (!$('#userSelect').hasClass('select2-hidden-accessible')) {
                    initializeSelect2();
                }
                
                // Reinitialize datepickers if needed
                $('[wire\\:ignore]').each(function() {
                    if ($(this).find('.datetimepicker-input').length) {
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
                        $(this).datetimepicker(config);
                    }
                });
            });
        </script>
    @stop
</div>