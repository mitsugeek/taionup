<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>userlist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
</head>
<body>
   
    <form method="post" action="{{route("userUpdate")}}">
        @csrf
        <table class="table">
            @foreach($list as $row)
            <input type="hidden" name="rows[{{$loop->index}}][line_id]" value="{{$row->line_id}}" />
            <tr>
                <td>{{$row->line_name}}</td>
                <td><input type="text" name="rows[{{$loop->index}}][name_sei]" value="{{$row->name_sei}}" /></td>
                <td><input type="text" name="rows[{{$loop->index}}][name_mei]" value="{{$row->name_mei}}" /></td>
            </tr>
            @endforeach
        </table>
        <input type="submit" value="更新" />
    </form>
    
</body>
</html>