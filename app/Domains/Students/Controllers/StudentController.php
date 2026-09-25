<?php

namespace App\Domains\Students\Controllers;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Students\Models\EmergencyContact;
use App\Domains\Students\Models\Student;
use App\Domains\Students\Models\StudentTransfer;
use App\Domains\Students\Models\StudentDocument;
use App\Domains\Students\Models\StudentEnrollment;
use App\Domains\Students\Requests\PromoteStudentsRequest;
use App\Domains\Students\Requests\SaveEmergencyContactRequest;
use App\Domains\Students\Requests\StoreStudentDocumentRequest;
use App\Domains\Students\Requests\StoreStudentRequest;
use App\Domains\Students\Requests\TransferStudentRequest;
use App\Domains\Students\Requests\UpdateStudentMedicalRequest;
use App\Domains\Students\Requests\UpdateStudentRequest;
use App\Domains\Students\Services\StudentService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Enums\StudentDocumentCategory;
use App\Support\Enums\StudentStatus;
use App\Support\Enums\StudentTimelineType;
use App\Support\Enums\StudentTransferType;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(private readonly StudentService $studentService) {}

    public function dashboard(): View
    {
        $this->authorize('viewAny', Student::class);

        $year = $this->studentService->currentYear();
        $currentStatuses = [StudentStatus::Active->value, StudentStatus::New->value];
        $totalStudents = Student::where('academic_year_id', $year->getKey())->whereIn('status', $currentStatuses)->count();
        $newAdmissions = Student::where('academic_year_id', $year->getKey())
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
        $transfers = StudentTransfer::whereHas('student', fn ($q) => $q->where('academic_year_id', $year->getKey()))
            ->where('transfer_date', '>=', $year->start_date)
            ->count();
        $grade8Candidates = Student::where('academic_year_id', $year->getKey())
            ->whereIn('status', $currentStatuses)
            ->whereHas('gradeLevel', fn ($q) => $q->where('code', '8')->orWhere('name', 'Grade 8'))
            ->count();
        $byGrade = GradeLevel::ordered()->withCount(['students as current_students_count' => function ($query) use ($year, $currentStatuses) {
            $query->where('academic_year_id', $year->getKey())->whereIn('status', $currentStatuses);
        }])->get();

        return view('students.dashboard', compact('year', 'totalStudents', 'newAdmissions', 'transfers', 'grade8Candidates', 'byGrade'));
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Student::class);

        $students = $this->studentService->paginate($request->only([
            'q', 'status', 'gender', 'grade_level_id', 'class_room_id', 'academic_year_id', 'transfer',
        ]));
        $gradeLevels = GradeLevel::ordered()->get();
        $classRooms = ClassRoom::where('academic_year_id', $this->studentService->currentYear()->getKey())->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $statusOptions = StudentStatus::cases();

        return view('students.index', compact('students', 'gradeLevels', 'classRooms', 'academicYears', 'statusOptions'));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', Student::class);

        $filters = $request->only(['q', 'status', 'gender', 'grade_level_id', 'class_room_id', 'academic_year_id', 'transfer']);
        $query = Student::withTrashed()->with(['gradeLevel', 'classRoom', 'academicYear', 'primaryGuardian'])
            ->search($filters['q'] ?? null)
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['gender'] ?? null, fn ($q, $v) => $q->where('gender', $v))
            ->when($filters['grade_level_id'] ?? null, fn ($q, $v) => $q->where('grade_level_id', $v))
            ->when($filters['class_room_id'] ?? null, fn ($q, $v) => $q->where('class_room_id', $v))
            ->when($filters['academic_year_id'] ?? null, fn ($q, $v) => $q->where('academic_year_id', $v))
            ->when(($filters['transfer'] ?? null) === '1', fn ($q) => $q->whereIn('status', [StudentStatus::Transferred->value, StudentStatus::Withdrawn->value]))
            ->orderBy('last_name')->orderBy('first_name');

        $filename = 'edusphere-students-'.now()->format('Y-m-d_His').'.csv';
        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Student Number', 'First Name', 'Other Names', 'Last Name', 'Gender', 'Date of Birth', 'Grade', 'Class', 'Parent', 'Parent Phone', 'Status', 'Academic Year']);
            $query->chunk(500, function ($students) use ($out) {
                foreach ($students as $student) {
                    fputcsv($out, [
                        $student->student_number, $student->first_name, $student->other_names, $student->last_name,
                        $student->gender, optional($student->date_of_birth)->format('Y-m-d'), $student->gradeLevel?->name,
                        $student->classRoom?->name, $student->primaryGuardian?->full_name, $student->primaryGuardian?->phone,
                        $student->statusLabel(), $student->academicYear?->name,
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
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

        $student->load([
            'gradeLevel', 'classRoom', 'academicYear', 'primaryParent',
            'parents', 'enrollments.academicYear', 'enrollments.gradeLevel', 'enrollments.classRoom',
            'medicalRecord', 'emergencyContacts', 'documents.uploadedBy', 'documents.verifiedBy',
            'transfers.fromClassRoom', 'transfers.toClassRoom', 'transfers.approvedBy',
            'statusHistories', 'timeline',
        ]);

        return view('students.show', [
            'student' => $student,
            'attendancePercentage' => $student->attendancePercentage(),
            'outstandingBalance' => null,
            'documentCategories' => StudentDocumentCategory::cases(),
        ]);
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

    /* ------------------------------- Roster ------------------------------- */

    public function roster(Request $request): View
    {
        $this->authorize('viewAny', Student::class);

        $year = $this->studentService->currentYear();
        $classRooms = ClassRoom::where('academic_year_id', $year->getKey())->with('gradeLevel')->orderBy('name')->get();
        $selected = $request->filled('class_room_id')
            ? ClassRoom::with('gradeLevel')
                ->where('academic_year_id', $year->getKey())
                ->findOrFail($request->input('class_room_id'))
            : null;

        $students = collect();

        if ($selected) {
            $students = StudentEnrollment::with(['student.gradeLevel', 'student.classRoom'])
                ->where('class_room_id', $selected->getKey())
                ->where('academic_year_id', $year->getKey())
                ->orderBy('roll_number')
                ->get();
        }

        return view('students.roster', compact('classRooms', 'selected', 'students', 'year'));
    }

    /* ----------------------------- Transfers ------------------------------ */

    public function transfer(Student $student): View
    {
        $this->authorize('transfer', $student);

        $year = $this->studentService->currentYear();
        $classRooms = ClassRoom::where('academic_year_id', $year->getKey())->with('gradeLevel')->orderBy('name')->get();
        $student->load(['classRoom', 'gradeLevel']);

        return view('students.transfer', compact('student', 'classRooms'));
    }

    public function transferStore(TransferStudentRequest $request, Student $student): RedirectResponse
    {
        $this->authorize('transfer', $student);

        $data = $request->validated();

        if (($data['type'] ?? null) === StudentTransferType::Internal->value) {
            $this->studentService->transferInternal(
                $student,
                ClassRoom::findOrFail($data['to_class_room_id']),
                $data['transfer_date'] ?? null,
                $data['reason'] ?? null,
                auth()->id(),
            );
            ActivityLogger::log('internal transfer for '.$student->full_name, 'students', $student->id);
            $message = 'Student "'.$student->full_name.'" moved sections in '.$student->gradeLevel?->name.'.';
        } else {
            $this->studentService->transferExternal($student, $data, auth()->id());
            ActivityLogger::log('external transfer for '.$student->full_name, 'students', $student->id);
            $message = 'Student "'.$student->full_name.'" transferred out to '.($data['destination_school'] ?? 'another school').'.';
        }

        return redirect()->route('students.show', $student)->with('status', $message);
    }

    /* ----------------------------- Promotion ------------------------------ */

    public function promote(Request $request): View
    {
        $this->authorize('promote', Student::class);

        $year = $this->studentService->currentYear();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $classRooms = ClassRoom::where('academic_year_id', $year->getKey())->with('gradeLevel')->orderBy('name')->get();

        $selectedClass = null;
        $candidates = collect();
        $nextYear = AcademicYear::where('start_date', '>', $year->start_date)->orderBy('start_date')->first();

        if ($request->filled('class_room_id')) {
            $selectedClass = ClassRoom::with('gradeLevel')
                ->where('academic_year_id', $year->getKey())
                ->findOrFail($request->input('class_room_id'));
            $candidates = $this->studentService->promotionCandidates($selectedClass);

            $nextYear = AcademicYear::find($request->input('target_academic_year_id')) ?? $nextYear;
        }

        return view('students.promote', compact('classRooms', 'selectedClass', 'candidates', 'academicYears', 'nextYear', 'year'));
    }

    public function promoteStore(PromoteStudentsRequest $request): RedirectResponse
    {
        $this->authorize('promote', Student::class);

        $class = ClassRoom::with('gradeLevel')->findOrFail($request->input('class_room_id'));
        $year = AcademicYear::findOrFail($request->input('target_academic_year_id'));

        abort_unless((int) $class->academic_year_id === (int) $this->studentService->currentYear()->getKey(), 422, 'The source class must belong to the active academic year.');

        $result = $this->studentService->promote($class, $year, $request->input('note'), auth()->id());
        ActivityLogger::log('promoted '.(int) $result['promoted'].' students from '.$class->name, 'students');

        return redirect()->route('students.promote')
            ->with('status', 'Promotion complete: '.$result['promoted'].' promoted, '.$result['graduated'].' graduated.');
    }

    /* ------------------------- Emergency contacts ------------------------- */

    public function emergencyContactStore(SaveEmergencyContactRequest $request, Student $student): RedirectResponse
    {
        $this->authorize('update', $student);

        $this->studentService->addEmergencyContact($student, $request->validated(), auth()->id());
        ActivityLogger::log('added emergency contact for '.$student->full_name, 'students', $student->id);

        return redirect()->to(route('students.show', $student).'#emergency')
            ->with('status', 'Emergency contact added.');
    }

    public function emergencyContactUpdate(SaveEmergencyContactRequest $request, Student $student, EmergencyContact $contact): RedirectResponse
    {
        $this->authorize('update', $student);
        abort_unless((int) $contact->student_id === (int) $student->getKey(), 404);

        $contact->update($request->validated());

        return redirect()->to(route('students.show', $student).'#emergency')
            ->with('status', 'Emergency contact updated.');
    }

    public function emergencyContactDestroy(Student $student, EmergencyContact $contact): RedirectResponse
    {
        $this->authorize('update', $student);
        abort_unless((int) $contact->student_id === (int) $student->getKey(), 404);

        $contact->delete();

        return redirect()->to(route('students.show', $student).'#emergency')
            ->with('status', 'Emergency contact removed.');
    }

    /* ------------------------------- Medical ------------------------------ */

    public function medicalStore(UpdateStudentMedicalRequest $request, Student $student): RedirectResponse
    {
        $this->authorize('viewMedical', $student);

        $this->studentService->saveMedicalRecord($student, $request->validated(), auth()->id());
        ActivityLogger::log('updated medical record for '.$student->full_name, 'students', $student->id);

        return redirect()->to(route('students.show', $student).'#medical')
            ->with('status', 'Medical record saved.');
    }

    /* ------------------------------ Documents ----------------------------- */

    public function documentStore(StoreStudentDocumentRequest $request, Student $student): RedirectResponse
    {
        $this->authorize('viewDocuments', $student);

        $category = $request->input('category');
        $name = $request->input('name') ?: StudentDocumentCategory::tryFrom($category)?->label().' ('.now()->format('M j, Y').')';
        $path = $request->file('file')->store('student-documents/'.$student->getKey(), 'public');

        $document = StudentDocument::create([
            'student_id' => $student->getKey(),
            'category' => $category,
            'name' => $name,
            'path' => $path,
            'mime' => $request->file('file')->getMimeType(),
            'size' => $request->file('file')->getSize(),
            'notes' => $request->input('notes'),
            'uploaded_by_id' => auth()->id(),
        ]);

        $this->studentService->addDocument($student, $document, auth()->id());

        return redirect()->to(route('students.show', $student).'#documents')
            ->with('status', 'Document "'.$document->name.'" uploaded.');
    }

    public function documentVerify(Student $student, StudentDocument $document): RedirectResponse
    {
        $this->authorize('viewDocuments', $student);
        abort_unless((int) $document->student_id === (int) $student->getKey(), 404);

        $document->update([
            'verified' => ! $document->verified,
            'verified_by_id' => $document->verified ? null : auth()->id(),
            'verified_at' => $document->verified ? null : now(),
        ]);

        $this->studentService->logTimeline(
            $student,
            $document->verified ? StudentTimelineType::DocumentVerified : StudentTimelineType::Note,
            $document->verified ? 'Document verified: '.$document->name.'.' : 'Document verification revoked: '.$document->name.'.',
            ['document_id' => $document->getKey()]
        );

        return redirect()->to(route('students.show', $student).'#documents')
            ->with('status', $document->verified ? 'Document verified.' : 'Verification revoked.');
    }

    public function documentDestroy(Student $student, StudentDocument $document): RedirectResponse
    {
        $this->authorize('viewDocuments', $student);
        abort_unless((int) $document->student_id === (int) $student->getKey(), 404);

        Storage::disk('public')->delete($document->path);
        $document->delete();

        return redirect()->to(route('students.show', $student).'#documents')
            ->with('status', 'Document removed.');
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
