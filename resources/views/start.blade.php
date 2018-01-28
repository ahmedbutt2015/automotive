<!DOCTYPE html>
<html lang="en">
<head>
    <title>Task</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
<nav class="navbar navbar-toggleable-md navbar-light bg-faded">
    <a class="navbar-brand" href="/">Home</a>
</nav>
<div class="container">
    <div class="row" style="text-align: center;position: absolute; top: 50% ;left: 50%">
        <form action="" method="post">
            {{csrf_field()}}
            <button class="btn btn-lg btn-success">Start !</button>
        </form>
        @if(Session::has('error'))
            <div class="row">
                <div class="alert alert-warning">
                    <strong>Error ! </strong> {{Session::get('error')}}
                </div>
            </div>
        @endif

        @if(Session::has('job'))
            <div class="row">
                <div class="alert alert-success">
                    <strong>{{Session::get('job')}}</strong>
                </div>
            </div>
        @endif

    </div>
</div>

</body>
</html>
