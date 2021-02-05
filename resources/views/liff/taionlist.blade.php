<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>体温一覧</title>
</head>
<body>
    <table>
    @foreach($list as $row)
        <tr>
            <td>{{ $row->line_name }}</td>
            <td>{{ $row->name_sei }}</td>
            <td>{{ $row->name_mei }}</td>
            <td>{{ $row->line_id }}</td>
            <td>{{ $row->url }}</td>
            <td><img src="{{ $row->url }}" width="100px"/></td>
        </tr>
    @endforeach
    </table>
    
    {{ $list->links() }}
</body>
</html>