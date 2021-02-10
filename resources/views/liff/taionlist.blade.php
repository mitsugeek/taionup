<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
    <title>体温一覧</title>
<style>
.hidden {display:none;}
</style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col">
            </div>
        </div>
        <div class="row">
            <div class="col">
                <table class="table table-bordered">
                    <tr>
                        <th>名前</th>
                        <th>日時</th>
                        <th>画像</th>
                    </tr>
                    @foreach($list as $row)
                    <tr>
                        @if(empty($row->name_sei))
                            <td>{{ $row->line_name }}</td>
                        @else
                            <td>{{ $row->name_sei." ".$row->name_mei }}</td>
                        @endif
                        <td>{{ $row->created_at }}</td>
                        <td><img src="{{ $row->url }}" width="100px"/></td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col">
                {{ $list->links() }}
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>    
</body>
</html>