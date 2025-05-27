@if($cutiToday->isEmpty())
    <div class="text-muted">Tidak ada user yang sedang cuti hari ini.</div>
@else
    <ul class="list-group list-group-flush">
        @foreach($cutiToday as $cuti)
         <li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $cuti->user->name }}</strong><br>
                <small class="text-muted">
                    {{ $cuti->type->name }}: {{ \Carbon\Carbon::parse($cuti->date_start)->format('d M') }}
                    -
                    {{ \Carbon\Carbon::parse($cuti->date_end)->format('d M') }}
                </small>

                {{-- Status Approval --}}
                <div class="mt-1">
                    @if(is_null($cuti->approval_hr_user_id))
                        <span class="badge badge-warning">Menunggu Approval HR</span>
                    @elseif(is_null($cuti->approval_finance_user_id))
                        <span class="badge badge-warning">Menunggu Approval Finance</span>
                    @endif
                </div>
            </div>
            
            <span class="badge badge-info">{{ $cuti->durationInDays() }} hari</span>
        </li>
        @endforeach
    </ul>
@endif