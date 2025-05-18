<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ClassController extends Controller
{
    /**
     * Display a listing of the classes.
     */
    public function index(): View
    {
        $classes = ClassModel::all();
        return view('classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new class.
     */
    public function create(): View
    {
        $teachers = Teacher::all();
        return view('classes.create', compact('teachers'));
    }

    /**
     * Store a newly created class in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'name' => 'required|string|max:255',//, 'unique:classes,name'], // Added unique rule, commented out
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $teacherId = $request->input('teacher_id');
        $dayOfWeek = $request->input('day_of_week');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        // Check for conflicts
        $conflictingClass = ClassModel::where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })
            ->first();

        if ($conflictingClass) {
            return back()->withErrors(['conflict' => "This class overlaps with '{$conflictingClass->name}' scheduled on {$conflictingClass->day_of_week} from {$conflictingClass->start_time} to {$conflictingClass->end_time}."]);
        }

        ClassModel::create($request->all());

        return redirect()->route('classes.index')->with('success', 'Class created successfully.');
    }

    /**
     * Display the specified class.
     */
    public function show(ClassModel $class): View
    {
        return view('classes.show', compact('class'));
    }

    /**
     * Show the form for editing the specified class.
     */
    public function edit(ClassModel $class): View
    {
        $teachers = Teacher::all();
        return view('classes.edit', compact('class', 'teachers'));
    }

    /**
     * Update the specified class in storage.
     */
    public function update(Request $request, ClassModel $classToUpdate): RedirectResponse
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'name' => 'required|string|max:255',//, 'unique:classes,name,'.$classToUpdate->id], // Added unique, exclude current, commented out
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $teacherId = $request->input('teacher_id');
        $dayOfWeek = $request->input('day_of_week');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        // Check for conflicts, excluding the current class being updated
        $conflictingClass = ClassModel::where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->where('id', '!=', $classToUpdate->id) // Exclude the current class
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })
            ->first();

        if ($conflictingClass) {
            return back()->withErrors(['conflict' => "This class overlaps with '{$conflictingClass->name}' scheduled on {$conflictingClass->day_of_week} from {$conflictingClass->start_time} to {$conflictingClass->end_time}."]);
        }

        $classToUpdate->update($request->all());

        return redirect()->route('classes.index')->with('success', 'Class updated successfully.');
    }

    /**
     * Remove the specified class from storage.
     */
    public function destroy(ClassModel $class): RedirectResponse
    {
        $class->delete();
        return redirect()->route('classes.index')->with('success', 'Class deleted successfully.');
    }
}