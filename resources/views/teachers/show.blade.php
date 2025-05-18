<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <title>Teacher Details</title>
</head>
<body>
    <h1>Teacher Details</h1>

    <p>Name: {{ $teacher->name }}</p>

    <a href="{{ route('teachers.index') }}">Back to Teachers</a>
</body>
</html>