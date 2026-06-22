<table>
    <thead>
        <tr><th colspan="6">{{ $event->title }} Attendance</th></tr>
        <tr><th>Name</th><th>Email</th><th>Status</th><th>Checked In</th><th>Checked Out</th><th>Notes</th></tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                @foreach($row as $value)
                    <td>{{ $value }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
