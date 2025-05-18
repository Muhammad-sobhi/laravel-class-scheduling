<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <title>Teachers</title>
</head>
<body>
    <h1>Teachers</h1>

    @if (session('success'))
        <p class="success">{{ session('success') }}</p>
    @endif

    <a href="{{ route('teachers.create') }}">Add New Teacher</a>
    <a href="{{ route('classes.index') }}">View Classes</a>

    @if ($teachers->count() > 0)
        <ul>
            @foreach ($teachers as $teacher)
                <li>
                    <span>{{ $teacher->name }}</span>
                    <a href="{{ route('teachers.show', $teacher->id) }}">View</a>
                    <a href="{{ route('teachers.edit', $teacher->id) }}">Edit</a>
                    <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </li>
            @endforeach
        </ul>
    @else
        <p>No teachers found.</p>
    @endif
</body>
</html>