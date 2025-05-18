<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .schedule-table th, .schedule-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            vertical-align: top;
            height: 40px;
        }
        .schedule-table th {
            background-color: #f0f0f0;
        }
        .class-block {
            color: white;
            padding: 2px;
            margin: 1px;
            border-radius: 3px;
            font-size: 0.8em;
            overflow: hidden;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .class-block a {
            color: rgb(27, 27, 27);
            text-decoration: none;
            display: block;
            width: 100%;
            height: 100%;
        }

        /* More distinct colors for different teachers */
        .teacher-1 { background-color: #2563eb; }  /* Blue 600 */
        .teacher-2 { background-color: #dc2626; }  /* Red 600 */
        .teacher-3 { background-color: #facc15; }  /* Yellow 400 */
        .teacher-4 { background-color: #16a34a; }  /* Green 600 */
        .teacher-5 { background-color: #8b5cf6; }  /* Violet 500 */
        .teacher-6 { background-color: #ec4899; }  /* Pink 500 */
        .teacher-7 { background-color: #f97316; }  /* Orange 600 */
        .teacher-8 { background-color: #14b8a6; }  /* Teal 500 */
        .teacher-9 { background-color: #4ade80; } /* Lime 500 */
        .teacher-10 { background-color: #6b7280; } /* Gray 500 */

    </style>
</head>
<body>
    <h1>Classes</h1>
    <a href="{{ route('classes.create') }}">Add New Class</a>
    <a href="{{ route('teachers.index') }}">Back to Teachers</a>

    <table class="schedule-table">
        <thead>
            <tr>
                <th>Time</th>
                <th>Monday</th>
                <th>Tuesday</th>
                <th>Wednesday</th>
                <th>Thursday</th>
                <th>Friday</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Define the time slots
                $start_time = 8;
                $end_time = 18;
                $slot_duration = 1;
                $num_slots = ($end_time - $start_time) / $slot_duration;
            @endphp

            @for ($i = 0; $i < $num_slots; $i++)
                @php
                    $time = date('H:i', strtotime("{$start_time}:00 + {$i} hours"));
                    $displayTime = date('h:ia', strtotime($time));
                @endphp
                <tr>
                    <td>{{ $displayTime }}</td>
                    @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day)
                        <td>
                            @foreach ($classes as $class)
                                @if ($class->day_of_week == $day)
                                    @php
                                        $class_start_time = date('H', strtotime($class->start_time));
                                        $class_end_time = date('H', strtotime($class->end_time));
                                        $start_slot = ($class_start_time - $start_time) / $slot_duration;
                                        $end_slot = ($class_end_time - $start_time) / $slot_duration;

                                        if ($i >= $start_slot && $i < $end_slot) {
                                            $rowspan = $end_slot - $start_slot;
                                            $teacher_number = $class->teacher_id % 10;  /* Increased modulo to use more colors */
                                            $teacher_class = 'teacher-' . ($teacher_number + 1);
                                    @endphp
                                            <div class="class-block {{ $teacher_class }}" style="height: {{ $rowspan * 40 - 2 }}px;">
                                                <a href="{{ route('classes.show', $class->id) }}">
                                                    {{ $class->name }}<br>
                                                    ({{ $class->teacher->name }})
                                                </a>
                                            </div>
                                        @php
                                            break;
                                        }
                                    @endphp
                                @endif
                            @endforeach
                        </td>
                    @endforeach
                </tr>
            @endfor
        </tbody>
    </table>
</body>
</html>
