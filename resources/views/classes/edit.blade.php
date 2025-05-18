<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Class</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <h1>Edit Class</h1>

    <form action="{{ route('classes.update', $class->id) }}" method="POST" id="edit-class-form">
        @csrf
        @method('PUT')
        <div>
            <label for="teacher_id">Teacher:</label>
            <select name="teacher_id" id="teacher_id" required>
                <option value="">Select Teacher</option>
                @foreach ($teachers as $teacher)
                    <option value="{{ $teacher->id }}" {{ $class->teacher_id == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                @endforeach
            </select>
            <div id="teacher_id-error" class="error"></div>
        </div>
        <div>
            <label for="name">Class Name:</label>
            <input type="text" id="name" name="name" value="{{ $class->name }}" required>
            <div id="name-error" class="error"></div>
        </div>
        <div>
            <label for="day_of_week">Day of Week:</label>
            <select name="day_of_week" id="day_of_week" required>
                <option value="Monday" {{ $class->day_of_week == 'Monday' ? 'selected' : '' }}>Monday</option>
                <option value="Tuesday" {{ $class->day_of_week == 'Tuesday' ? 'selected' : '' }}>Tuesday</option>
                <option value="Wednesday" {{ $class->day_of_week == 'Wednesday' ? 'selected' : '' }}>Wednesday</option>
                <option value="Thursday" {{ $class->day_of_week == 'Thursday' ? 'selected' : '' }}>Thursday</option>
                <option value="Friday" {{ $class->day_of_week == 'Friday' ? 'selected' : '' }}>Friday</option>
            </select>
            <div id="day_of_week-error" class="error"></div>
        </div>
        <div>
            <label for="start_time">Start Time:</label>
            <input type="time" id="start_time" name="start_time" value="{{ $class->start_time }}" required>
            <div id="start_time-error" class="error"></div>
        </div>
        <div>
            <label for="end_time">End Time:</label>
            <input type="time" id="end_time" name="end_time" value="{{ $class->end_time }}" required>
            <div id="end_time-error" class="error"></div>
        </div>
         @error('conflict')  
            <div class="alert alert-danger">{{ $message }}</div>  
        @enderror
        <button type="submit">Update Class</button>
        <a href="{{ route('classes.index') }}">Back to Classes</a>
    </form>

 
                <script src="{{ asset('js/edit-class-validation.js') }}"></script>
</body>
</html>