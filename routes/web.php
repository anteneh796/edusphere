<?php

use App\Domains\Academics\Controllers\AcademicsController;
use App\Domains\Academics\Controllers\AcademicTermController;
use App\Domains\Academics\Controllers\AcademicYearController;
use App\Domains\Academics\Controllers\ClassRoomController;
use App\Domains\Academics\Controllers\GradeLevelController;
use App\Domains\Academics\Controllers\SectionController;
use App\Domains\Academics\Controllers\SubjectController;
use App\Domains\Accounts\Controllers\AuditLogController;
use App\Domains\Accounts\Controllers\Auth\ForgotPasswordController;
use App\Domains\Accounts\Controllers\Auth\LoginController;
use App\Domains\Accounts\Controllers\Auth\ResetPasswordController;
use App\Domains\Accounts\Controllers\DashboardController;
use App\Domains\Accounts\Controllers\ProfileController;
use App\Domains\Accounts\Controllers\RolesController;
use App\Domains\Accounts\Controllers\SessionSecurityController;
use App\Domains\Accounts\Controllers\StaffController;
use App\Domains\Accounts\Controllers\UserController;
use App\Domains\Admissions\Controllers\AdmissionApplicationController;
use App\Domains\Admissions\Controllers\AdmissionAssessmentController;
use App\Domains\Admissions\Controllers\AdmissionCommunicationController;
use App\Domains\Admissions\Controllers\AdmissionReportsController;
use App\Domains\Admissions\Controllers\AdmissionsDashboardController;
use App\Domains\Admissions\Controllers\ApplicantDocumentController;
use App\Domains\Admissions\Controllers\ApplicationGuardianController;
use App\Domains\Admissions\Controllers\GradeCapacityController;
use App\Domains\Approvals\Controllers\ApprovalController;
use App\Domains\Attendance\Controllers\AttendanceController;
use App\Domains\Cms\Controllers\CmsController;
use App\Domains\Cms\Controllers\ContentBlockController;
use App\Domains\Cms\Controllers\EventController;
use App\Domains\Cms\Controllers\GalleryController;
use App\Domains\Cms\Controllers\NewsController;
use App\Domains\Cms\Controllers\PageController;
use App\Domains\Cms\Controllers\PublicWebsiteController;
use App\Domains\Cms\Controllers\TestimonialController;
use App\Domains\Exams\Controllers\ExamPapersController;
use App\Domains\Exams\Controllers\ExamResultsController;
use App\Domains\Exams\Controllers\ExamsController;
use App\Domains\Exams\Controllers\ReportCardsController;
use App\Domains\HumanResources\Controllers\ContractController;
use App\Domains\HumanResources\Controllers\DepartmentController;
use App\Domains\HumanResources\Controllers\DocumentController;
use App\Domains\HumanResources\Controllers\EmployeeController;
use App\Domains\HumanResources\Controllers\HrController;
use App\Domains\HumanResources\Controllers\LeaveRequestController;
use App\Domains\HumanResources\Controllers\LeaveTypeController;
use App\Domains\HumanResources\Controllers\OfficialLetterController;
use App\Domains\HumanResources\Controllers\PayrollController;
use App\Domains\HumanResources\Controllers\PerformanceReviewController;
use App\Domains\HumanResources\Controllers\PositionController;
use App\Domains\HumanResources\Controllers\RecruitmentCandidateController;
use App\Domains\HumanResources\Controllers\ReportsController as HrReportsController;
use App\Domains\HumanResources\Controllers\StaffAttendanceController;
use App\Domains\HumanResources\Controllers\TrainingRecordController;
use App\Domains\Notifications\Controllers\NotificationController;
use App\Domains\Portals\Controllers\GuardianServicesController;
use App\Domains\Portals\Controllers\ParentPortalController;
use App\Domains\Portals\Controllers\StudentPortalController;
use App\Domains\Portals\Controllers\TeacherPortalController;
use App\Domains\Reports\Controllers\ReportsController;
use App\Domains\Settings\Controllers\SettingsController;
use App\Domains\Students\Controllers\GuardianController;
use App\Domains\Students\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicWebsiteController::class, 'home'])->name('public.home');

/* ------------------------- Authentication ------------------------- */

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('auth.login');
    Route::post('login', [LoginController::class, 'authenticate'])->name('auth.authenticate');

    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('auth.forgot');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('auth.forgot.send');

    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('auth.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'update'])->name('auth.reset.update');
});

/* --------------------- Authenticated area ------------------------- */

Route::middleware(['auth', 'active'])->group(function () {
    // Logout must remain available even when a user is forced to change password.
    Route::post('logout', [LoginController::class, 'logout'])->name('auth.logout');
});

Route::middleware(['auth', 'active', 'force.password'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('security', [ProfileController::class, 'security'])->name('security');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('password', [ProfileController::class, 'password'])->name('password');
    });

    /* ---------------- Staff modules (permission middleware) ---------------- */

    Route::middleware(['permission:users.view'])->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');

        Route::get('roles', [RolesController::class, 'index'])->name('roles.index');
        Route::get('roles/{role}/edit', [RolesController::class, 'edit'])->name('roles.edit');
    });

    Route::middleware(['permission:staff.view'])->name('staff.')->group(function () {
        Route::get('staff', [StaffController::class, 'index'])->name('index');

        Route::middleware(['permission:staff.create'])->group(function () {
            Route::get('staff/create', [StaffController::class, 'create'])->name('create');
            Route::post('staff', [StaffController::class, 'store'])->name('store');
        });

        Route::get('staff/{user}', [StaffController::class, 'show'])->name('show');

        Route::middleware(['permission:staff.edit'])->group(function () {
            Route::get('staff/{user}/edit', [StaffController::class, 'edit'])->name('edit');
            Route::put('staff/{user}', [StaffController::class, 'update'])->name('update');
            Route::delete('staff/{user}', [StaffController::class, 'destroy'])->name('destroy');
        });
    });

    Route::middleware(['permission:users.create'])->group(function () {
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
    });

    Route::middleware(['permission:users.view'])->group(function () {
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    });

    Route::middleware(['permission:users.edit'])->group(function () {
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::put('roles/{role}', [RolesController::class, 'update'])->name('roles.update');
    });

    Route::middleware(['permission:users.delete'])->group(function () {
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware(['permission:settings.view'])->group(function () {
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    });

    Route::middleware(['permission:settings.edit'])->group(function () {
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    Route::middleware(['permission:academics.view'])->name('academics.')->group(function () {
        Route::get('academics/hub', [AcademicsController::class, 'index'])->name('index');
        Route::get('academics/grades', [GradeLevelController::class, 'index'])->name('grades.index');
        Route::get('academics/classes', [ClassRoomController::class, 'index'])->name('classes.index');
        Route::get('academics/classes/{classRoom}/subjects', [ClassRoomController::class, 'subjects'])->name('classes.subjects');
        Route::get('academics/subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('academics/years', [AcademicYearController::class, 'index'])->name('years.index');
        Route::get('academics/sections', [SectionController::class, 'index'])->name('sections.index');
        Route::get('academics/terms', [AcademicTermController::class, 'index'])->name('terms.index');

        Route::middleware(['permission:academics.create'])->group(function () {
            Route::get('academics/classes/create', [ClassRoomController::class, 'create'])->name('classes.create');
            Route::post('academics/classes', [ClassRoomController::class, 'store'])->name('classes.store');
            Route::get('academics/subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
            Route::post('academics/subjects', [SubjectController::class, 'store'])->name('subjects.store');
            Route::get('academics/grades/create', [GradeLevelController::class, 'create'])->name('grades.create');
            Route::post('academics/grades', [GradeLevelController::class, 'store'])->name('grades.store');
            Route::get('academics/years/create', [AcademicYearController::class, 'create'])->name('years.create');
            Route::post('academics/years', [AcademicYearController::class, 'store'])->name('years.store');
            Route::get('academics/sections/create', [SectionController::class, 'create'])->name('sections.create');
            Route::post('academics/sections', [SectionController::class, 'store'])->name('sections.store');
            Route::get('academics/terms/create', [AcademicTermController::class, 'create'])->name('terms.create');
            Route::post('academics/terms', [AcademicTermController::class, 'store'])->name('terms.store');
        });

        Route::middleware(['permission:academics.edit'])->group(function () {
            Route::get('academics/classes/{classRoom}/edit', [ClassRoomController::class, 'edit'])->name('classes.edit');
            Route::put('academics/classes/{classRoom}', [ClassRoomController::class, 'update'])->name('classes.update');
            Route::put('academics/classes/{classRoom}/subjects', [ClassRoomController::class, 'saveSubjects'])->name('classes.subjects.update');
            Route::get('academics/subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
            Route::put('academics/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
            Route::get('academics/grades/{grade}/edit', [GradeLevelController::class, 'edit'])->name('grades.edit');
            Route::put('academics/grades/{grade}', [GradeLevelController::class, 'update'])->name('grades.update');
            Route::post('academics/years/{year}/activate', [AcademicYearController::class, 'activate'])->name('years.activate');
            Route::get('academics/years/{year}/edit', [AcademicYearController::class, 'edit'])->name('years.edit');
            Route::put('academics/years/{year}', [AcademicYearController::class, 'update'])->name('years.update');
            Route::get('academics/sections/{section}/edit', [SectionController::class, 'edit'])->name('sections.edit');
            Route::put('academics/sections/{section}', [SectionController::class, 'update'])->name('sections.update');
            Route::post('academics/terms/{term}/activate', [AcademicTermController::class, 'activate'])->name('terms.activate');
            Route::get('academics/terms/{term}/edit', [AcademicTermController::class, 'edit'])->name('terms.edit');
            Route::put('academics/terms/{term}', [AcademicTermController::class, 'update'])->name('terms.update');
        });

        Route::middleware(['permission:academics.delete'])->group(function () {
            Route::delete('academics/classes/{classRoom}', [ClassRoomController::class, 'destroy'])->name('classes.destroy');
            Route::delete('academics/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
            Route::delete('academics/grades/{grade}', [GradeLevelController::class, 'destroy'])->name('grades.destroy');
            Route::delete('academics/years/{year}', [AcademicYearController::class, 'destroy'])->name('years.destroy');
            Route::delete('academics/sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');
            Route::delete('academics/terms/{term}', [AcademicTermController::class, 'destroy'])->name('terms.destroy');
        });
    });

    Route::middleware(['permission:attendance.view'])->name('attendance.')->group(function () {
        Route::get('attendance', [AttendanceController::class, 'index'])->name('index');
        Route::get('attendance/dashboard', [AttendanceController::class, 'dashboard'])->name('dashboard');
        Route::get('attendance/alerts', [AttendanceController::class, 'alerts'])->name('alerts');
        Route::get('attendance/sessions/{session}', [AttendanceController::class, 'show'])->name('show');

        Route::middleware(['permission:attendance.approve'])->group(function () {
            Route::get('attendance/corrections', [AttendanceController::class, 'corrections'])->name('corrections');
            Route::post('attendance/corrections/{correction}', [AttendanceController::class, 'correctionsReview'])->name('corrections.review');
        });

        Route::middleware(['permission:attendance.configure'])->group(function () {
            Route::get('attendance/settings', [AttendanceController::class, 'settings'])->name('settings');
            Route::put('attendance/settings', [AttendanceController::class, 'settingsUpdate'])->name('settings.update');
            Route::post('attendance/sessions/{session}/override', [AttendanceController::class, 'override'])->name('override');
        });

        Route::prefix('attendance/reports')->name('reports.')->group(function () {
            Route::get('', [AttendanceController::class, 'reports'])->name('index');
            Route::get('daily', [AttendanceController::class, 'reportsDaily'])->name('daily');
            Route::get('student', [AttendanceController::class, 'reportsStudent'])->name('student');
            Route::get('monthly', [AttendanceController::class, 'reportsMonthly'])->name('monthly');
            Route::get('statuses', [AttendanceController::class, 'reportsStatus'])->name('status');
            Route::get('trend', [AttendanceController::class, 'reportsTrend'])->name('trend');
            Route::get('completion', [AttendanceController::class, 'reportsCompletion'])->name('completion');
            Route::get('class', [AttendanceController::class, 'reportsClass'])->name('class');
            Route::get('grade', [AttendanceController::class, 'reportsGrade'])->name('grade');
            Route::get('late', [AttendanceController::class, 'reportsLate'])->name('late');
            Route::get('term', [AttendanceController::class, 'reportsTerm'])->name('term');
        });

        Route::middleware(['permission:attendance.create'])->group(function () {
            Route::get('attendance/take', [AttendanceController::class, 'create'])->name('create');
            Route::post('attendance/sessions', [AttendanceController::class, 'store'])->name('store');
        });

        Route::middleware(['permission:attendance.edit'])->group(function () {
            Route::put('attendance/sessions/{session}', [AttendanceController::class, 'update'])->name('update');
            Route::post('attendance/sessions/{session}/close', [AttendanceController::class, 'close'])->name('close');
        });
    });

    Route::post('api/v1/attendance/sync', [AttendanceController::class, 'sync'])
        ->middleware('permission:attendance.create');

    Route::middleware(['permission:exams.view'])->name('exams.')->group(function () {
        Route::get('exams', [ExamsController::class, 'index'])->name('index');

        Route::middleware(['permission:exams.create'])->group(function () {
            Route::get('exams/create', [ExamsController::class, 'create'])->name('create');
            Route::post('exams', [ExamsController::class, 'store'])->name('store');
        });

        Route::get('exams/papers/{paper}/results', [ExamResultsController::class, 'board'])->name('results');
        Route::put('exams/papers/{paper}/results', [ExamResultsController::class, 'save'])->name('results.save');

        Route::get('exams/{exam}', [ExamsController::class, 'show'])->name('show');

        Route::middleware(['permission:exams.edit'])->group(function () {
            Route::get('exams/{exam}/edit', [ExamsController::class, 'edit'])->name('edit');
            Route::put('exams/{exam}', [ExamsController::class, 'update'])->name('update');
            Route::get('exams/{exam}/papers', [ExamPapersController::class, 'edit'])->name('papers.edit');
            Route::put('exams/{exam}/papers', [ExamPapersController::class, 'update'])->name('papers.update');
        });

        Route::middleware(['permission:exams.delete'])->group(function () {
            Route::delete('exams/{exam}', [ExamsController::class, 'destroy'])->name('destroy');
        });

        Route::middleware(['permission:exams.publish'])->group(function () {
            Route::post('exams/{exam}/publish', [ExamsController::class, 'publish'])->name('publish');
            Route::post('exams/{exam}/complete', [ExamsController::class, 'complete'])->name('complete');
        });
    });

    Route::middleware(['permission:exams.view'])->name('report-cards.')->group(function () {
        Route::get('report-cards', [ReportCardsController::class, 'index'])->name('index');

        Route::middleware(['permission:exams.edit'])->group(function () {
            Route::get('report-cards/create', [ReportCardsController::class, 'create'])->name('create');
            Route::post('report-cards', [ReportCardsController::class, 'generate'])->name('store');
            Route::post('report-cards/{reportCard}/approve', [ReportCardsController::class, 'approve'])->name('approve');
        });

        Route::get('report-cards/{reportCard}', [ReportCardsController::class, 'show'])->name('show');

        Route::post('report-cards/{reportCard}/publish', [ReportCardsController::class, 'publish'])
            ->middleware('permission:exams.publish')
            ->name('publish');
    });

    Route::middleware(['permission:students.view'])->name('students.')->group(function () {
        Route::get('students', [StudentController::class, 'index'])->name('index');
        Route::get('students/roster', [StudentController::class, 'roster'])->name('roster');

        Route::middleware(['permission:students.promote'])->group(function () {
            Route::get('students/promote', [StudentController::class, 'promote'])->name('promote');
            Route::post('students/promote', [StudentController::class, 'promoteStore'])->name('promote.store');
        });

        Route::middleware(['permission:students.create'])->group(function () {
            Route::get('students/create', [StudentController::class, 'create'])->name('create');
            Route::post('students', [StudentController::class, 'store'])->name('store');
        });

        Route::get('students/{student}', [StudentController::class, 'show'])->name('show');
        Route::get('students/{student}/id-card', [StudentController::class, 'idCard'])->name('id-card');

        Route::middleware(['permission:students.transfer'])->group(function () {
            Route::get('students/{student}/transfer', [StudentController::class, 'transfer'])->name('transfer');
            Route::post('students/{student}/transfer', [StudentController::class, 'transferStore'])->name('transfer.store');
        });

        Route::middleware(['permission:students.edit'])->group(function () {
            Route::get('students/{student}/edit', [StudentController::class, 'edit'])->name('edit');
            Route::put('students/{student}', [StudentController::class, 'update'])->name('update');
            Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('destroy');
            Route::post('students/{student}/emergency-contacts', [StudentController::class, 'emergencyContactStore'])->name('emergency-contacts.store');
            Route::put('students/{student}/emergency-contacts/{contact}', [StudentController::class, 'emergencyContactUpdate'])->name('emergency-contacts.update');
            Route::delete('students/{student}/emergency-contacts/{contact}', [StudentController::class, 'emergencyContactDestroy'])->name('emergency-contacts.destroy');
        });

        Route::middleware(['permission:students.medical'])->group(function () {
            Route::put('students/{student}/medical', [StudentController::class, 'medicalStore'])->name('medical.update');
        });

        Route::middleware(['permission:students.documents'])->group(function () {
            Route::post('students/{student}/documents', [StudentController::class, 'documentStore'])->name('documents.store');
            Route::put('students/{student}/documents/{document}/verify', [StudentController::class, 'documentVerify'])->name('documents.verify');
            Route::delete('students/{student}/documents/{document}', [StudentController::class, 'documentDestroy'])->name('documents.destroy');
        });
    });

    Route::middleware(['permission:guardians.view'])->name('guardians.')->group(function () {
        Route::get('guardians', [GuardianController::class, 'index'])->name('index');

        Route::middleware(['permission:guardians.create'])->group(function () {
            Route::get('guardians/create', [GuardianController::class, 'create'])->name('create');
            Route::post('guardians', [GuardianController::class, 'store'])->name('store');
        });

        Route::get('guardians/{guardian}', [GuardianController::class, 'show'])->name('show');

        Route::middleware(['permission:guardians.edit'])->group(function () {
            Route::get('guardians/{guardian}/edit', [GuardianController::class, 'edit'])->name('edit');
            Route::put('guardians/{guardian}', [GuardianController::class, 'update'])->name('update');
            Route::delete('guardians/{guardian}', [GuardianController::class, 'destroy'])->name('destroy');
        });
    });

    Route::middleware(['permission:cms.view'])->name('cms.')->group(function () {
        Route::get('website', [CmsController::class, 'index'])->name('index');

        Route::name('pages.')->group(function () {
            Route::get('website/pages', [PageController::class, 'index'])->name('index');
            Route::get('website/pages/{page}/edit', [PageController::class, 'edit'])->name('edit');
            Route::put('website/pages/{page}', [PageController::class, 'update'])->name('update');
        });

        Route::name('content-blocks.')->group(function () {
            Route::get('website/content-blocks', [ContentBlockController::class, 'index'])->name('index');
            Route::get('website/content-blocks/create', [ContentBlockController::class, 'create'])->name('create');
            Route::post('website/content-blocks', [ContentBlockController::class, 'store'])->name('store');
            Route::get('website/content-blocks/{contentBlock}/edit', [ContentBlockController::class, 'edit'])->name('edit');
            Route::put('website/content-blocks/{contentBlock}', [ContentBlockController::class, 'update'])->name('update');
            Route::post('website/content-blocks/reorder', [ContentBlockController::class, 'reorder'])->name('reorder');
            Route::delete('website/content-blocks/{contentBlock}', [ContentBlockController::class, 'destroy'])->name('destroy');
        });

        Route::name('news.')->group(function () {
            Route::get('website/news', [NewsController::class, 'index'])->name('index');
            Route::get('website/news/create', [NewsController::class, 'create'])->name('create');
            Route::post('website/news', [NewsController::class, 'store'])->name('store');
            Route::get('website/news/{news}/edit', [NewsController::class, 'edit'])->name('edit');
            Route::put('website/news/{news}', [NewsController::class, 'update'])->name('update');
            Route::delete('website/news/{news}', [NewsController::class, 'destroy'])->name('destroy');
        });

        Route::name('gallery.')->group(function () {
            Route::get('website/gallery', [GalleryController::class, 'index'])->name('index');
            Route::get('website/gallery/create', [GalleryController::class, 'create'])->name('create');
            Route::post('website/gallery', [GalleryController::class, 'store'])->name('store');
            Route::get('website/gallery/{galleryItem}/edit', [GalleryController::class, 'edit'])->name('edit');
            Route::put('website/gallery/{galleryItem}', [GalleryController::class, 'update'])->name('update');
            Route::delete('website/gallery/{galleryItem}', [GalleryController::class, 'destroy'])->name('destroy');
        });

        Route::name('events.')->group(function () {
            Route::get('website/events', [EventController::class, 'index'])->name('index');
            Route::get('website/events/create', [EventController::class, 'create'])->name('create');
            Route::post('website/events', [EventController::class, 'store'])->name('store');
            Route::get('website/events/{event}/edit', [EventController::class, 'edit'])->name('edit');
            Route::put('website/events/{event}', [EventController::class, 'update'])->name('update');
            Route::delete('website/events/{event}', [EventController::class, 'destroy'])->name('destroy');
        });

        Route::name('testimonials.')->group(function () {
            Route::get('website/testimonials', [TestimonialController::class, 'index'])->name('index');
            Route::get('website/testimonials/create', [TestimonialController::class, 'create'])->name('create');
            Route::post('website/testimonials', [TestimonialController::class, 'store'])->name('store');
            Route::get('website/testimonials/{testimonial}/edit', [TestimonialController::class, 'edit'])->name('edit');
            Route::put('website/testimonials/{testimonial}', [TestimonialController::class, 'update'])->name('update');
            Route::delete('website/testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('destroy');
        });
    });

    Route::middleware(['permission:audit.view'])->name('audit.')->group(function () {
        Route::get('audit', [AuditLogController::class, 'index'])->name('index');
        Route::get('audit/{auditLog}', [AuditLogController::class, 'show'])->name('show');
    });

    Route::middleware(['permission:audit.view'])->name('security.')->group(function () {
        Route::get('security/sessions', [SessionSecurityController::class, 'index'])->name('sessions');
        Route::post('security/sessions/{sessionId}/revoke', [SessionSecurityController::class, 'revoke'])->name('sessions.revoke');
        Route::get('security/login-history', [SessionSecurityController::class, 'loginHistory'])->name('login-history');
    });

    Route::middleware(['permission:approvals.view'])->name('approvals.')->group(function () {
        Route::get('approvals', [ApprovalController::class, 'index'])->name('index');

        Route::middleware(['permission:approvals.create'])->group(function () {
            Route::get('approvals/create', [ApprovalController::class, 'create'])->name('create');
            Route::post('approvals', [ApprovalController::class, 'store'])->name('store');
        });

        Route::get('approvals/{approval}', [ApprovalController::class, 'show'])->name('show');

        Route::middleware(['permission:approvals.approve'])->group(function () {
            Route::post('approvals/{approval}/review', [ApprovalController::class, 'review'])->name('review');
        });
    });

    /* ---------------- Admissions & Enrollment portal ---------------- */

    Route::middleware(['permission:admissions.view'])->name('admissions.')->group(function () {
        Route::get('admissions/dashboard', [AdmissionsDashboardController::class, 'index'])->name('dashboard');
        Route::get('admissions/reports', [AdmissionReportsController::class, 'index'])->name('reports.index');
        Route::post('admissions/inquiries/{inquiry}/handle', [AdmissionsDashboardController::class, 'handleInquiry'])->name('inquiries.handle');

        Route::get('admissions/applications', [AdmissionApplicationController::class, 'index'])->name('applications.index');
        Route::get('admissions/applicants', [AdmissionApplicationController::class, 'applicants'])->name('applicants.index');

        Route::middleware(['permission:admissions.create'])->group(function () {
            Route::get('admissions/applications/create', [AdmissionApplicationController::class, 'create'])->name('applications.create');
            Route::post('admissions/applications', [AdmissionApplicationController::class, 'store'])->name('applications.store');
        });

        Route::get('admissions/applications/{application}', [AdmissionApplicationController::class, 'show'])->name('applications.show');

        Route::middleware(['permission:admissions.edit'])->group(function () {
            Route::get('admissions/applications/{application}/edit', [AdmissionApplicationController::class, 'edit'])->name('applications.edit');
            Route::put('admissions/applications/{application}', [AdmissionApplicationController::class, 'update'])->name('applications.update');
            Route::post('admissions/applications/{application}/submit', [AdmissionApplicationController::class, 'submit'])->name('applications.submit');
            Route::post('admissions/applications/{application}/review', [AdmissionApplicationController::class, 'review'])->name('applications.review');
            Route::post('admissions/applications/{application}/approval', [AdmissionApplicationController::class, 'submitForApproval'])->name('applications.approval');
            Route::post('admissions/applications/{application}/withdraw', [AdmissionApplicationController::class, 'withdraw'])->name('applications.withdraw');
            Route::delete('admissions/applications/{application}', [AdmissionApplicationController::class, 'destroy'])->name('applications.destroy');
        });

        Route::middleware(['permission:admissions.approve'])->group(function () {
            Route::post('admissions/applications/{application}/decide', [AdmissionApplicationController::class, 'decide'])->name('applications.decide');
            Route::post('admissions/applications/{application}/waitlist', [AdmissionApplicationController::class, 'waitlist'])->name('applications.waitlist');
        });

        Route::middleware(['permission:admissions.enroll'])->group(function () {
            Route::post('admissions/applications/{application}/promote', [AdmissionApplicationController::class, 'promote'])->name('applications.promote');
            Route::post('admissions/applications/{application}/enroll', [AdmissionApplicationController::class, 'enroll'])->name('applications.enroll');
        });

        Route::middleware(['permission:admissions.create'])->group(function () {
            Route::post('admissions/applications/{application}/guardians', [ApplicationGuardianController::class, 'store'])->name('guardians.store');
            Route::put('admissions/applications/{application}/guardians/{guardian}', [ApplicationGuardianController::class, 'update'])->name('guardians.update');
            Route::delete('admissions/applications/{application}/guardians/{guardian}', [ApplicationGuardianController::class, 'destroy'])->name('guardians.destroy');
            Route::post('admissions/applications/{application}/documents', [ApplicantDocumentController::class, 'store'])->name('documents.store');
            Route::post('admissions/applications/{application}/communications', [AdmissionCommunicationController::class, 'store'])->name('communications.store');
            Route::delete('admissions/applications/{application}/communications/{communication}', [AdmissionCommunicationController::class, 'destroy'])->name('communications.destroy');
        });

        Route::get('admissions/documents', [ApplicantDocumentController::class, 'index'])->name('documents.index');
        Route::get('admissions/assessments', [AdmissionAssessmentController::class, 'index'])->name('assessments.index');
        Route::get('admissions/approvals', [AdmissionApplicationController::class, 'approvalsView'])->name('approvals.index');
        Route::get('admissions/waitlist', [AdmissionApplicationController::class, 'waitlistIndex'])->name('waitlist.index');
        Route::get('admissions/capacity', [GradeCapacityController::class, 'index'])->name('capacity.index');

        Route::middleware(['permission:admissions.create'])->group(function () {
            Route::post('admissions/applications/{application}/assessments', [AdmissionAssessmentController::class, 'store'])->name('assessments.store');
            Route::put('admissions/assessments/{assessment}', [AdmissionAssessmentController::class, 'update'])->name('assessments.update');
            Route::delete('admissions/assessments/{assessment}', [AdmissionAssessmentController::class, 'destroy'])->name('assessments.destroy');
        });

        Route::middleware(['permission:admissions.verify'])->group(function () {
            Route::post('admissions/documents/{document}/verify', [ApplicantDocumentController::class, 'verify'])->name('documents.verify');
        });

        Route::middleware(['permission:admissions.delete'])->group(function () {
            Route::delete('admissions/documents/{document}', [ApplicantDocumentController::class, 'destroy'])->name('documents.destroy');
        });

        Route::middleware(['permission:admissions.capacity'])->group(function () {
            Route::put('admissions/capacity', [GradeCapacityController::class, 'update'])->name('capacity.update');
        });
    });

    Route::middleware(['permission:notifications.view'])->name('notifications.')->group(function () {
        Route::get('notifications', [NotificationController::class, 'index'])->name('index');
        Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('read-all');
        Route::get('notifications/{notification}', [NotificationController::class, 'show'])->name('show');
    });


    Route::middleware(['permission:parent_services.view'])->name('guardian-services.')->group(function () {
        Route::get('parent-services/absences', [GuardianServicesController::class, 'absencesIndex'])->name('absences.index');
        Route::get('parent-services/requests', [GuardianServicesController::class, 'requestsIndex'])->name('requests.index');

        Route::middleware(['permission:parent_services.process'])->group(function () {
            Route::post('parent-services/absences/{absence}/review', [GuardianServicesController::class, 'absencesReview'])->name('absences.review');
            Route::post('parent-services/requests/{request}/process', [GuardianServicesController::class, 'requestsProcess'])->name('requests.process');
        });
    });

    Route::middleware(['permission:reports.view'])->name('reports.')->group(function () {
        Route::get('reports', [ReportsController::class, 'index'])->name('index');
    });

    /* ---------------- Human Resources (HRMS) ---------------- */

    Route::middleware(['permission:hr.view'])->prefix('hr')->name('hr.')->group(function () {
        Route::get('dashboard', [HrController::class, 'dashboard'])->name('dashboard');

        Route::name('employees.')->group(function () {
            Route::get('employees', [EmployeeController::class, 'index'])->name('index');
            Route::get('employees/create', [EmployeeController::class, 'create'])->middleware('permission:hr.create')->name('create');
            Route::post('employees', [EmployeeController::class, 'store'])->middleware('permission:hr.create')->name('store');
            Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('show');
            Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->middleware('permission:hr.edit')->name('edit');
            Route::put('employees/{employee}', [EmployeeController::class, 'update'])->middleware('permission:hr.edit')->name('update');
            Route::put('employees/{employee}/status', [EmployeeController::class, 'status'])->middleware('permission:hr.edit')->name('status');
            Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->middleware('permission:hr.edit')->name('destroy');
        });

        Route::name('departments.')->group(function () {
            Route::get('departments', [DepartmentController::class, 'index'])->name('index');
            Route::get('departments/create', [DepartmentController::class, 'create'])->middleware('permission:hr.create')->name('create');
            Route::post('departments', [DepartmentController::class, 'store'])->middleware('permission:hr.create')->name('store');
            Route::get('departments/{department}/edit', [DepartmentController::class, 'edit'])->middleware('permission:hr.edit')->name('edit');
            Route::put('departments/{department}', [DepartmentController::class, 'update'])->middleware('permission:hr.edit')->name('update');
            Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->middleware('permission:hr.delete')->name('destroy');
        });

        Route::name('positions.')->group(function () {
            Route::get('positions', [PositionController::class, 'index'])->name('index');
            Route::get('positions/create', [PositionController::class, 'create'])->middleware('permission:hr.create')->name('create');
            Route::post('positions', [PositionController::class, 'store'])->middleware('permission:hr.create')->name('store');
            Route::get('positions/{position}/edit', [PositionController::class, 'edit'])->middleware('permission:hr.edit')->name('edit');
            Route::put('positions/{position}', [PositionController::class, 'update'])->middleware('permission:hr.edit')->name('update');
            Route::delete('positions/{position}', [PositionController::class, 'destroy'])->middleware('permission:hr.delete')->name('destroy');
        });

        Route::middleware(['permission:hr.payroll'])->name('contracts.')->group(function () {
            Route::get('contracts', [ContractController::class, 'index'])->name('index');
            Route::get('contracts/create', [ContractController::class, 'create'])->name('create');
            Route::post('contracts', [ContractController::class, 'store'])->name('store');
            Route::get('contracts/{contract}', [ContractController::class, 'show'])->name('show');
            Route::get('contracts/{contract}/edit', [ContractController::class, 'edit'])->name('edit');
            Route::put('contracts/{contract}', [ContractController::class, 'update'])->name('update');
            Route::post('contracts/{contract}/renew', [ContractController::class, 'renew'])->name('renew');
            Route::delete('contracts/{contract}', [ContractController::class, 'destroy'])->middleware('permission:hr.delete')->name('destroy');
        });

        Route::name('leave.')->group(function () {
            Route::get('leave', [LeaveRequestController::class, 'index'])->name('index');
            Route::get('leave/my', [LeaveRequestController::class, 'myLeave'])->name('my');
            Route::get('leave/create', [LeaveRequestController::class, 'create'])->middleware('permission:hr.create')->name('create');
            Route::get('leave/create/{employee}', [LeaveRequestController::class, 'create'])->middleware('permission:hr.create')->name('create.for');
            Route::post('leave', [LeaveRequestController::class, 'store'])->middleware('permission:hr.create')->name('store');
            Route::post('leave/for/{employee}', [LeaveRequestController::class, 'store'])->middleware('permission:hr.create')->name('store.for');
            Route::get('leave/{request}', [LeaveRequestController::class, 'show'])->name('show');
            Route::post('leave/{request}/review', [LeaveRequestController::class, 'review'])->middleware('permission:hr.leave.approve')->name('review');
            Route::post('leave/{request}/cancel', [LeaveRequestController::class, 'cancel'])->middleware('permission:hr.create')->name('cancel');
        });

        Route::name('leave-types.')->group(function () {
            Route::get('leave-types', [LeaveTypeController::class, 'index'])->name('index');
            Route::get('leave-types/create', [LeaveTypeController::class, 'create'])->middleware('permission:hr.create')->name('create');
            Route::post('leave-types', [LeaveTypeController::class, 'store'])->middleware('permission:hr.create')->name('store');
            Route::get('leave-types/{leaveType}/edit', [LeaveTypeController::class, 'edit'])->name('edit');
            Route::put('leave-types/{leaveType}', [LeaveTypeController::class, 'update'])->name('update');
            Route::delete('leave-types/{leaveType}', [LeaveTypeController::class, 'destroy'])->middleware('permission:hr.delete')->name('destroy');
        });

        Route::name('attendance.')->group(function () {
            Route::get('attendance', [StaffAttendanceController::class, 'index'])->name('index');
            Route::get('attendance/take', [StaffAttendanceController::class, 'take'])->middleware('permission:hr.create')->name('take');
            Route::post('attendance', [StaffAttendanceController::class, 'store'])->middleware('permission:hr.create')->name('store');
            Route::put('attendance/{record}', [StaffAttendanceController::class, 'update'])->middleware('permission:hr.create')->name('update');
        });

        Route::name('performance.')->group(function () {
            Route::get('performance', [PerformanceReviewController::class, 'index'])->name('index');
            Route::get('performance/create', [PerformanceReviewController::class, 'create'])->middleware('permission:hr.create')->name('create');
            Route::post('performance', [PerformanceReviewController::class, 'store'])->middleware('permission:hr.create')->name('store');
            Route::get('performance/{review}', [PerformanceReviewController::class, 'show'])->name('show');
            Route::get('performance/{review}/edit', [PerformanceReviewController::class, 'edit'])->middleware('permission:hr.edit')->name('edit');
            Route::put('performance/{review}', [PerformanceReviewController::class, 'update'])->middleware('permission:hr.edit')->name('update');
            Route::post('performance/{review}/status', [PerformanceReviewController::class, 'status'])->middleware('permission:hr.edit')->name('status');
        });

        Route::name('training.')->group(function () {
            Route::get('training', [TrainingRecordController::class, 'index'])->name('index');
            Route::get('training/create', [TrainingRecordController::class, 'create'])->middleware('permission:hr.create')->name('create');
            Route::post('training', [TrainingRecordController::class, 'store'])->middleware('permission:hr.create')->name('store');
            Route::put('training/{record}', [TrainingRecordController::class, 'update'])->middleware('permission:hr.edit')->name('update');
            Route::delete('training/{record}', [TrainingRecordController::class, 'destroy'])->middleware('permission:hr.delete')->name('destroy');
        });

        Route::name('documents.')->group(function () {
            Route::get('documents', [DocumentController::class, 'index'])->name('index');
            Route::post('documents', [DocumentController::class, 'store'])->middleware('permission:hr.create')->name('store');
            Route::post('documents/{document}/verify', [DocumentController::class, 'verify'])->middleware('permission:hr.edit')->name('verify');
            Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('download');
            Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->middleware('permission:hr.delete')->name('destroy');
        });

        Route::middleware(['permission:hr.payroll'])->name('payroll.')->group(function () {
            Route::get('payroll', [PayrollController::class, 'index'])->name('index');
            Route::get('payroll/{employee}', [PayrollController::class, 'edit'])->name('edit');
            Route::post('payroll/{employee}', [PayrollController::class, 'store'])->name('store');
            Route::put('payroll/{employee}', [PayrollController::class, 'update'])->name('update');
        });

        Route::name('letters.')->group(function () {
            Route::get('letters', [OfficialLetterController::class, 'index'])->name('index');
            Route::get('letters/create', [OfficialLetterController::class, 'create'])->middleware('permission:hr.create')->name('create');
            Route::post('letters', [OfficialLetterController::class, 'store'])->middleware('permission:hr.create')->name('store');
            Route::get('letters/{letter}', [OfficialLetterController::class, 'show'])->name('show');
            Route::delete('letters/{letter}', [OfficialLetterController::class, 'destroy'])->middleware('permission:hr.delete')->name('destroy');
        });

        Route::name('candidates.')->group(function () {
            Route::get('candidates', [RecruitmentCandidateController::class, 'index'])->name('index');
            Route::get('candidates/create', [RecruitmentCandidateController::class, 'create'])->middleware('permission:hr.create')->name('create');
            Route::post('candidates', [RecruitmentCandidateController::class, 'store'])->middleware('permission:hr.create')->name('store');
            Route::get('candidates/{candidate}', [RecruitmentCandidateController::class, 'show'])->name('show');
            Route::put('candidates/{candidate}', [RecruitmentCandidateController::class, 'update'])->middleware('permission:hr.edit')->name('update');
            Route::post('candidates/{candidate}/decision', [RecruitmentCandidateController::class, 'decision'])->middleware('permission:hr.edit')->name('decision');
            Route::post('candidates/{candidate}/hire', [RecruitmentCandidateController::class, 'hire'])->middleware('permission:hr.create')->name('hire');
            Route::delete('candidates/{candidate}', [RecruitmentCandidateController::class, 'destroy'])->middleware('permission:hr.delete')->name('destroy');
        });

        Route::middleware(['permission:hr.reports'])->name('reports.')->group(function () {
            Route::get('reports', [HrReportsController::class, 'index'])->name('index');
            Route::get('reports/employees', [HrReportsController::class, 'employees'])->name('employees');
            Route::get('reports/attendance', [HrReportsController::class, 'attendance'])->name('attendance');
            Route::get('reports/leave', [HrReportsController::class, 'leave'])->name('leave');
            Route::get('reports/contracts', [HrReportsController::class, 'contracts'])->name('contracts');
        });
    });
});

/* --------------------- Public website (guests) --------------------- */
// NOTE: the `{page}` catch-all is registered LAST so that `/login`, `/dashboard`
// and the staff route tree are always matched first.

Route::name('public.')->group(function () {
    Route::get('news', [PublicWebsiteController::class, 'news'])->name('news');
    Route::get('news/{news}', [PublicWebsiteController::class, 'newsShow'])->name('news-show');
    Route::get('gallery', [PublicWebsiteController::class, 'gallery'])->name('gallery');
    Route::get('faculty', [PublicWebsiteController::class, 'faculty'])->name('faculty');
    Route::get('events', [PublicWebsiteController::class, 'events'])->name('events');
    Route::get('inquiry', [PublicWebsiteController::class, 'inquiryView'])->name('inquiry');
    Route::post('inquiry', [PublicWebsiteController::class, 'inquiryStore'])->name('inquiry.store');
    Route::post('newsletter', [PublicWebsiteController::class, 'newsletterSubscribe'])->name('newsletter.subscribe');

    Route::get('about', [PublicWebsiteController::class, 'about'])->name('about');
    Route::get('academics', [PublicWebsiteController::class, 'academics'])->name('academics');
    Route::get('admissions', [PublicWebsiteController::class, 'admissions'])->name('admissions');
    Route::get('contact', [PublicWebsiteController::class, 'contact'])->name('contact');
    Route::get('apply', [PublicWebsiteController::class, 'apply'])->name('apply');
    Route::get('login-gateway', [PublicWebsiteController::class, 'loginGateway'])->name('login-gateway');
    Route::get('search', [PublicWebsiteController::class, 'search'])->name('search');
    Route::get('locale/{locale}', [PublicWebsiteController::class, 'locale'])->name('locale');

    Route::get('sitemap.xml', [PublicWebsiteController::class, 'sitemap'])->name('sitemap');
    Route::get('robots.txt', [PublicWebsiteController::class, 'robots'])->name('robots');

    Route::get('{page}', [PublicWebsiteController::class, 'show'])->name('page');
});

/* --------------------- Student portal ------------------------------ */

Route::middleware(['auth', 'active', 'role:student'])->prefix('student')->name('cms.student.')->group(function () {
    Route::get('dashboard', [StudentPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('attendance', [StudentPortalController::class, 'attendance'])->name('attendance');
    Route::get('results', [StudentPortalController::class, 'results'])->name('results');
    Route::get('profile', [StudentPortalController::class, 'profile'])->name('profile');
});

/* --------------------- Parent portal ------------------------------- */

Route::middleware(['auth', 'active', 'role:parent'])->prefix('parent')->name('cms.parent.')->group(function () {
    Route::get('dashboard', [ParentPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('wards', [ParentPortalController::class, 'wardsIndex'])->name('wards');
    Route::post('wards/switch', [ParentPortalController::class, 'switchWard'])->name('wards.switch');
    Route::get('wards/{student}', [ParentPortalController::class, 'wardShow'])->name('wards.show');
    Route::get('attendance', [ParentPortalController::class, 'attendance'])->name('attendance');
    Route::get('absence-requests', [ParentPortalController::class, 'absenceRequests'])->name('absence-requests');
    Route::post('absence-requests', [ParentPortalController::class, 'absenceRequestsStore'])->name('absence-requests.store');
    Route::get('homework', [ParentPortalController::class, 'homework'])->name('homework');
    Route::get('academics', [ParentPortalController::class, 'academics'])->name('academics');
    Route::get('report-card', [ParentPortalController::class, 'reportCard'])->name('report-card');
    Route::get('calendar', [ParentPortalController::class, 'calendar'])->name('calendar');
    Route::get('announcements', [ParentPortalController::class, 'announcements'])->name('announcements');
    Route::get('messages', [ParentPortalController::class, 'messages'])->name('messages');
    Route::post('messages', [ParentPortalController::class, 'messagesStore'])->name('messages.store');
    Route::get('meetings', [ParentPortalController::class, 'meetingRequests'])->name('meetings');
    Route::post('meetings', [ParentPortalController::class, 'meetingRequestsStore'])->name('meetings.store');
    Route::post('meetings/{meetingRequest}/cancel', [ParentPortalController::class, 'meetingRequestCancel'])->name('meetings.cancel');
    Route::get('requests', [ParentPortalController::class, 'requests'])->name('requests');
    Route::post('requests', [ParentPortalController::class, 'requestsStore'])->name('requests.store');
    Route::get('documents', [ParentPortalController::class, 'documents'])->name('documents');
    Route::get('notifications', [ParentPortalController::class, 'notifications'])->name('notifications');
    Route::post('notifications/read-all', [ParentPortalController::class, 'notificationsReadAll'])->name('notifications.read-all');
    Route::get('notifications/{notification}', [ParentPortalController::class, 'notificationShow'])->name('notifications.show');
    Route::get('settings', [ParentPortalController::class, 'settings'])->name('settings');
    Route::put('settings', [ParentPortalController::class, 'settingsUpdate'])->name('settings.update');
});

/* --------------------- Teacher portal ------------------------------- */

Route::middleware(['auth', 'active', 'role:teacher'])->prefix('teacher')->name('cms.teacher.')->group(function () {
    Route::get('dashboard', [TeacherPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('profile', [TeacherPortalController::class, 'profile'])->name('profile');
    Route::get('timetable', [TeacherPortalController::class, 'timetable'])->name('timetable');
    Route::get('classes', [TeacherPortalController::class, 'classes'])->name('classes');
    Route::get('classes/{classSubject}', [TeacherPortalController::class, 'classShow'])->name('classes.show');
    Route::get('attendance', [TeacherPortalController::class, 'attendance'])->name('attendance');
    Route::get('attendance/sessions/{session}', [TeacherPortalController::class, 'attendanceSession'])->name('attendance.session');
    Route::post('attendance/sessions', [TeacherPortalController::class, 'attendanceStore'])->name('attendance.store');
    Route::put('attendance/sessions/{session}', [TeacherPortalController::class, 'attendanceUpdate'])->name('attendance.update');
    Route::post('attendance/sessions/{session}/close', [TeacherPortalController::class, 'attendanceClose'])->name('attendance.close');
    Route::post('attendance/sessions/{session}/correction', [TeacherPortalController::class, 'attendanceCorrectionRequest'])->name('attendance.correction');
    Route::get('lesson-plans', [TeacherPortalController::class, 'lessonPlans'])->name('lesson-plans');
    Route::get('lesson-plans/create', [TeacherPortalController::class, 'lessonPlansCreate'])->name('lesson-plans.create');
    Route::post('lesson-plans', [TeacherPortalController::class, 'lessonPlansStore'])->name('lesson-plans.store');
    Route::get('lesson-plans/{lessonPlan}/edit', [TeacherPortalController::class, 'lessonPlansEdit'])->name('lesson-plans.edit');
    Route::put('lesson-plans/{lessonPlan}', [TeacherPortalController::class, 'lessonPlansUpdate'])->name('lesson-plans.update');
    Route::delete('lesson-plans/{lessonPlan}', [TeacherPortalController::class, 'lessonPlansDestroy'])->name('lesson-plans.destroy');
    Route::get('curriculum', [TeacherPortalController::class, 'curriculum'])->name('curriculum');
    Route::post('curriculum', [TeacherPortalController::class, 'curriculumStore'])->name('curriculum.store');
    Route::put('curriculum/{curriculumUnit}', [TeacherPortalController::class, 'curriculumUpdate'])->name('curriculum.update');
    Route::get('homework', [TeacherPortalController::class, 'homework'])->name('homework');
    Route::get('homework/create', [TeacherPortalController::class, 'homeworkCreate'])->name('homework.create');
    Route::post('homework', [TeacherPortalController::class, 'homeworkStore'])->name('homework.store');
    Route::get('homework/{homeworkAssignment}', [TeacherPortalController::class, 'homeworkShow'])->name('homework.show');
    Route::get('homework/{homeworkAssignment}/edit', [TeacherPortalController::class, 'homeworkEdit'])->name('homework.edit');
    Route::put('homework/{homeworkAssignment}', [TeacherPortalController::class, 'homeworkUpdate'])->name('homework.update');
    Route::delete('homework/{homeworkAssignment}', [TeacherPortalController::class, 'homeworkDestroy'])->name('homework.destroy');
    Route::post('homework/{homeworkAssignment}/publish', [TeacherPortalController::class, 'homeworkPublish'])->name('homework.publish');
    Route::get('assessments', [TeacherPortalController::class, 'assessments'])->name('assessments');
    Route::get('assessments/create', [TeacherPortalController::class, 'assessmentsCreate'])->name('assessments.create');
    Route::post('assessments', [TeacherPortalController::class, 'assessmentsStore'])->name('assessments.store');
    Route::get('assessments/{classroomAssessment}/grades', [TeacherPortalController::class, 'assessmentsGrades'])->name('assessments.grades');
    Route::post('assessments/{classroomAssessment}/grades', [TeacherPortalController::class, 'assessmentsGradesStore'])->name('assessments.grades.store');
    Route::get('behavior', [TeacherPortalController::class, 'behavior'])->name('behavior');
    Route::post('behavior', [TeacherPortalController::class, 'behaviorStore'])->name('behavior.store');
    Route::get('behavior/{behaviorNote}/edit', [TeacherPortalController::class, 'behaviorEdit'])->name('behavior.edit');
    Route::put('behavior/{behaviorNote}', [TeacherPortalController::class, 'behaviorUpdate'])->name('behavior.update');
    Route::delete('behavior/{behaviorNote}', [TeacherPortalController::class, 'behaviorDestroy'])->name('behavior.destroy');
    Route::get('progress', [TeacherPortalController::class, 'progress'])->name('progress');
    Route::get('homeroom', [TeacherPortalController::class, 'homeroom'])->name('homeroom');
    Route::get('messages', [TeacherPortalController::class, 'messages'])->name('messages');
    Route::post('messages', [TeacherPortalController::class, 'messagesStore'])->name('messages.store');
    Route::post('messages/{message}/reply', [TeacherPortalController::class, 'messagesReply'])->name('messages.reply');
    Route::get('meetings', [TeacherPortalController::class, 'meetings'])->name('meetings');
    Route::post('meetings/{meetingRequest}/review', [TeacherPortalController::class, 'meetingsReview'])->name('meetings.review');
    Route::get('resources', [TeacherPortalController::class, 'resources'])->name('resources');
    Route::post('resources', [TeacherPortalController::class, 'resourcesStore'])->name('resources.store');
    Route::get('resources/{resource}/edit', [TeacherPortalController::class, 'resourceEdit'])->name('resources.edit');
    Route::put('resources/{resource}', [TeacherPortalController::class, 'resourceUpdate'])->name('resources.update');
    Route::delete('resources/{resource}', [TeacherPortalController::class, 'resourceDestroy'])->name('resources.destroy');
    Route::get('reports', [TeacherPortalController::class, 'reports'])->name('reports');
});
