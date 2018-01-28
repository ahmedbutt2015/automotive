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

<div class="container">
    <div class="row well col-lg-6 col-lg-push-3">
        <h2>Complete this form</h2>
        <form action="/save/user/info" method="post">
            <div class="form-group">
                <label for="">Latitude:</label>
                <input type="number" step="0" class="form-control" placeholder="Enter latitude" required name="latitude">
            </div>
            <div class="form-group">
                <label for="">Longitude:</label>
                <input type="number" step="0" class="form-control" placeholder="Enter longitude" required name="longitude">
            </div>
            <div class="form-group">
                <label for="">Heading:</label>
                <input type="text" class="form-control" placeholder="Enter Heading" name="heading" required>
            </div>
            <div class="form-group">
                <label for="">Timestamp:</label>
                <input type="datetime-local" class="form-control" placeholder="Enter Timestamp" name="timestamp" required>
            </div>
            <div class="form-group">
                <label for="">Mo Id:</label>
                <input type="text" class="form-control" placeholder="Enter Mo Id" name="mo_id" required>
            </div>
            <div class="form-group">
                <label for="">Speed:</label>
                <input type="number" step="0" class="form-control" placeholder="Enter speed" required name="speed">
            </div>

            <div class="form-group">
                <label for="">Driver Id:</label>
                <input type="text" class="form-control" placeholder="Enter Driver Id" name="driver_id" required>
            </div>

            <div class="form-group">
                <label for="">Trip Id:</label>
                <input type="text" class="form-control" placeholder="Enter Trip Id" name="trip_id" required>
            </div>
            {{csrf_field()}}
            <button type="submit" class="btn btn-default">Submit</button>
            @if(isset($error))
                <div class="row">
                    <div class="alert alert-warning">
                        <strong>Error ! </strong> {{$error}}
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>

</body>
</html>
