<table border="1" cellspacing="0" cellpadding="5" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="text-align: center; font-weight: bold;">Nama Staff</th>
            <th style="text-align: center; font-weight: bold;">Tanggal</th>
            <th style="text-align: center; font-weight: bold;">Check</th>
            <th style="text-align: center; font-weight: bold;">Detail Checkin</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
            @php
                $checkinsByDate = collect($user->employeeCheckings)->groupBy(function($checkin) {
                    return \Carbon\Carbon::parse($checkin->scheduled_time)
                        ->locale('id_ID')
                        ->translatedFormat('l, j F Y');
                });
                $totalCheckinCount = $checkinsByDate->flatten()->count();
                $userRowSpanPrinted = false;
            @endphp

            @foreach ($checkinsByDate as $date => $checkins)
                @php
                    $completedCount = $checkins->where('is_completed', true)->count();
                    $dateRowSpan = $checkins->count();
                @endphp

                @foreach ($checkins as $index => $checkin)
                    <tr>
                        {{-- Kolom A: Nama Staff --}}
                        @if (!$userRowSpanPrinted)
                            <td rowspan="{{ $totalCheckinCount }}" style="text-align: center; vertical-align: middle;">
                                {{ $user->name }}
                            </td>
                            @php $userRowSpanPrinted = true; @endphp
                        @endif

                        {{-- Kolom B: Tanggal --}}
                        @if ($index === 0)
                            <td rowspan="{{ $dateRowSpan }}" style="text-align: center; vertical-align: middle;">
                                <strong>{{ $date }}<br>(Total {{ $completedCount }})</strong>
                            </td>
                        @endif

                        {{-- Kolom C: Check ke --}}
                        <td>- Check {{ $index + 1 }}</td>

                        {{-- Kolom D: Detail --}}
                        <td>
                            @if($checkin->is_dayoff)
                                <strong style="color: red;">Cuti</strong>
                            @elseif($checkin->is_permission)
                                <strong style="color: orange;">Sakit</strong>
                            @else
                                Schedule Time: {{ \Carbon\Carbon::parse($checkin->scheduled_time)->format('H:i') ?? '-' }}<br>
                                Checkin Time: {{ $checkin->checkin_start_time ? \Carbon\Carbon::parse($checkin->checkin_start_time)->format('H:i') : '-' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>