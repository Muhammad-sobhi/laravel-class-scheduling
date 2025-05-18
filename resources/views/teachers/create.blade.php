<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <title>Add Teacher</title>
</head>
<body>
    <h1>Add New Teacher</h1>

    <form action="{{ route('teachers.store') }}" method="POST">
        @csrf
        <div>
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            @error('name')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit">Save Teacher</button>
        <a href="{{ route('teachers.index') }}">Back to Teachers</a>
    </form>
</body>
</html>