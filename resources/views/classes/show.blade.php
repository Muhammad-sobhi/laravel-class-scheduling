<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <title>Class Details</title>
</head>
<body>
    <h1>Class Details</h1>

    <p>Name: {{ $class->name }}</p>
    <p>Teacher: {{ $class->teacher->name }}</p>
    <p>Day: {{ $class->day_of_week }}</p>
    <p>Time: {{ $class->start_time }} - {{ $class->end_time }}</p>

    <a href="{{ route('classes.index') }}">Back to Classes</a>
</body>
</html>