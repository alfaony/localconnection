<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Point Percentage</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->point_percentage }}%</td>
            </tr>
        @endforeach
    </tbody>
</table>