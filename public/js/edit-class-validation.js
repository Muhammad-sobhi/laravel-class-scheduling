
       const form = document.getElementById('edit-class-form');
        const teacherIdInput = document.getElementById('teacher_id');
        const nameInput = document.getElementById('name');
        const dayOfWeekInput = document.getElementById('day_of_week');
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');

        const teacherIdError = document.getElementById('teacher_id-error');
        const nameError = document.getElementById('name-error');
        const dayOfWeekError = document.getElementById('day_of_week-error');
        const startTimeError = document.getElementById('start_time-error');
        const endTimeError = document.getElementById('end_time-error');

        function validateForm(event) {
            let isValid = true;

            teacherIdError.textContent = '';
            nameError.textContent = '';
            dayOfWeekError.textContent = '';
            startTimeError.textContent = '';
            endTimeError.textContent = '';

            if (!teacherIdInput.value) {
                teacherIdError.textContent = 'Please select a teacher.';
                isValid = false;
            }

            if (!nameInput.value) {
                nameError.textContent = 'Please enter a class name.';
                isValid = false;
            }

            if (!dayOfWeekInput.value) {
                dayOfWeekError.textContent = 'Please select a day of the week.';
                isValid = false;
            }

            if (!startTimeInput.value) {
                startTimeError.textContent = 'Please enter a start time.';
                isValid = false;
            }

            if (!endTimeInput.value) {
                endTimeError.textContent = 'Please enter an end time.';
                isValid = false;
            }  else if (startTimeInput.value) {
                // Only check if startTime is also valid
                if (endTimeInput.value <= startTimeInput.value) {
                    endTimeError.textContent = 'End time must be after start time.';
                    isValid = false;
                }
            }

            if (!isValid) {
                event.preventDefault();
            }

            return isValid;
        }

        form.addEventListener('submit', validateForm);
    
