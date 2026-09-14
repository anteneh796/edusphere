<?php

namespace App\Domains\Students\Controllers;

use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Students\Models\Student;
use App\Domains\Students\Requests\StoreStudentRequest;
use App\Domains\Students\Requests\UpdateStudentRequest;
use App\Domains\Students\Services\StudentService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Enums\StudentStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(private readonly StudentService $studentService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Student::class);

        $students = $this->studentService->paginate($request->only(['q', 'status', 'grade_level_id', 'class_room_id']));
        $gradeLevels = GradeLevel::ordered()->get();
        $classRooms = ClassRoom::where('academic_year_id', $this->studentService->currentYear()->getKey())->orderBy('name')->get();
        $statusOptions = StudentStatus::cases();

        return view('students.index', compact('students', 'gradeLevels', 'classRooms', 'statusOptions'));
    }

    public function create(): View
    {
        $this->authorize('create', Student::class);

        $gradeLevels = GradeLevel::ordered()->get();
        $classRooms = $this->classRoomOptions();

        return view('students.create', compact('gradeLevels', 'classRooms'));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $this->authorize('create', Student::class);

        $student = $this->studentService->create($request->validated());
        ActivityLogger::log('registered student '.$student->full_name.' ('.$student->student_number.')', 'students', $student->id);

        return redirect()->route('students.show', $student)
            ->with('status', 'Student "'.$student->full_name.'" registered as '.$student->student_number.'.');
    }

    public function show(Student $student): View
    {
        $this->authorize('view', $student);

        $student->load(['gradeLevel', 'classRoom', 'academicYear', 'primaryGuardian', 'guardians', 'enrollments.academicYear', 'enrollments.gradeLevel', 'enrollments.classRoom']);

        return view('students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $this->authorize('update', $student);

        $gradeLevels = GradeLevel::ordered()->get();
        $classRooms = $this->classRoomOptions();
        $student->load(['primaryGuardian', 'academicYear']);

        return view('students.edit', compact('student', 'gradeLevels', 'classRooms'));
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $this->authorize('update', $student);

        $this->studentService->update($student, $request->validated());
        ActivityLogger::log('updated student '.$student->full_name, 'students', $student->id);

        return redirect()->route('students.show', $student)
            ->with('status', 'Student "'.$student->full_name.'" updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorize('delete', $student);

        $this->studentService->delete($student);
        ActivityLogger::log('archived student '.$student->full_name, 'students', $student->id);

        return redirect()->route('students.index')
            ->with('status', 'Student "'.$student->full_name.'" archived.');
    }

    public function idCard(Student $student): View
    {
        $this->authorize('view', $student);

        $student->load(['gradeLevel', 'classRoom', 'academicYear', 'primaryGuardian', 'enrollments.academicYear', 'enrollments.gradeLevel', 'enrollments.classRoom']);

        return view('students.id-card', compact('student'));
    }

    private function classRoomOptions(): Collection
    {
        return ClassRoom::where('academic_year_id', $this->studentService->currentYear()->getKey())
            ->get(['id', 'name', 'grade_level_id'])
            ->map(fn ($class) => [
                'id' => $class->getKey(),
                'name' => $class->name,
                'grade_level_id' => $class->grade_level_id,
            ])
            ->values();
    }
}
