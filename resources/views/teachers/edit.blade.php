<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <title>Edit Teacher</title>
</head>
<body>
    <h1>Edit Teacher</h1>

    <form action="{{ route('teachers.update', $teacher->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div>
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="{{ $teacher->name }}" required>
            @error('name')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit">Update Teacher</button>
        <a href="{{ route('teachers.index') }}">Back to Teachers</a>
    </form>
</body>
</html>