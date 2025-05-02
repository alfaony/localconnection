<table>
    <thead>
        <tr>
            <th>User</th>
            <th>Tanggal Dayoff</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dayoffs as $dayoff)
            <tr>
                <td>{{ $dayoff->user->name ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($dayoff->scheduled_time)->format('d-m-Y') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>