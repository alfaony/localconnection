@canAccess('index','meetings')
<div class="row">
    <div class="col-md-12">
        <div class="card mt-">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                <h4 class="mb-0">Rapat</h4>
        
                <a href="{{ route('meeting.create') }}" class="btn btn-primary ml-auto">
                    <i class="fas fa-plus"></i> Buat Rapat
                </a>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    @if($googleReadyChecked)
                    <div >
                        @if ($googleConnected)
                            <span class="badge bg-success align-self-center">
                                ✅ Terhubung ke Google Calendar
                            </span>
                        @else
                            <a href="{{ route('google.auth') }}" class="btn btn-warning">
                                🔗 Hubungkan Google Calendar
                            </a>
                        @endif
                    </div>
                    @endif
                    <input type="text" class="form-control" wire:model.live="search" placeholder="Cari Rapat"
                        style="width: auto;">
                </div>
        
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-nowrap" width="5%">No</th>
                                <th class="text-nowrap" width="15%">Nama Rapat</th>
                                <th class="text-nowrap" width="25%">Agenda Rapat</th>
                                {{--<th class="text-nowrap" width="10%">Status</th>--}}
                                <th class="text-nowrap" width="10%">Tanggal</th>
                                <th class="text-nowrap" width="15%">Waktu</th>
                                <th class="text-nowrap" width="10%">Jenis Rapat</th>
                                <th class="text-nowrap" width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($meetings as $index => $meeting)
                                <tr>
                                    <td>{{ $meetings->firstItem() + $index }}</td>
                                    <td>{{ Str::limit($meeting->meeting_name, 30) }}</td>
                                    <td>{{ Str::limit(strip_tags($meeting->meeting_agenda), 100) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($meeting->start_date)->format('Y-m-d') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($meeting->end_time)->format('H:i') }}</td>
                                    <td>
                                        {!! $meeting->meeting_type_badge !!}
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @canAccess('show','meetings')
                                            <a href="{{ route('meeting.show', $meeting->slug) }}" class="btn btn-info btn-sm mb-1 mr-1">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcanAccess
                                            @canAccess('edit','meetings')
                                            <a href="{{ route('meeting.edit', $meeting->slug) }}"
                                                class="btn btn-primary btn-sm mb-1 mr-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcanAccess
                                            @canAccess('destroy','meetings')
                                            <form action="{{ route('meeting.destroy', $meeting->slug) }}" method="POST"
                                                style="display:inline;" class="delete-form mb-1 mr-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endcanAccess
                                            {{-- 
                                            <a href="{{ route('ratings.create', $meeting->slug) }}"
                                                class="btn btn-success btn-sm"> <i class="far fa-fw fa-star"></i> </a>
                                            --}}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    {{ $meetings->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endcanAccess
