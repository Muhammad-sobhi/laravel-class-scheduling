<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Class</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <h1>Add New Class</h1>

    <form action="{{ route('classes.store') }}" method="POST" id="create-class-form">
        @csrf
        <div>
            <label for="teacher_id">Teacher:</label>
            <select name="teacher_id" id="teacher_id" required>
                <option value="">Select Teacher</option>
                @foreach ($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                @endforeach
            </select>
            <div id="teacher_id-error" class="error"></div>
        </div>
        <div>
            <label for="name">Class Name:</label>
            <input type="text" id="name" name="name" required>
            <div id="name-error" class="error"></div>
        </div>
        <div>
            <label for="day_of_week">Day of Week:</label>
            <select name="day_of_week" id="day_of_week" required>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
            </select>
            <div id="day_of_week-error" class="error"></div>
        </div>
        <div>
            <label for="start_time">Start Time:</label>
            <input type="time" id="start_time" name="start_time" required>
            <div id="start_time-error" class="error"></div>
        </div>
        <div>
            <label for="end_time">End Time:</label>
            <input type="time" id="end_time" name="end_time" required>
            <div id="end_time-error" class="error"></div>
        </div>
         @error('conflict')  
            <div class="alert alert-danger">{{ $message }}</div>  
        @enderror
        <button type="submit">Save Class</button>
        <a href="{{ route('classes.index') }}">Back to Classes</a>
    </form>

            <script src="{{ asset('js/create-class-validation.js') }}"></script>
</body>
</html>