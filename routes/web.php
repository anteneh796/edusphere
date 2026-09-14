<?php

use App\Domains\Academics\Controllers\AcademicsController;
use App\Domains\Academics\Controllers\ClassRoomController;
use App\Domains\Cms\Controllers\CmsController;
use App\Domains\Cms\Controllers\GalleryController;
use App\Domains\Cms\Controllers\NewsController;
use App\Domains\Cms\Controllers\PageController;
use App\Domains\Cms\Controllers\PublicWebsiteController;
use App\Domains\Academics\Controllers\GradeLevelController;
use App\Domains\Academics\Controllers\SubjectController;
use App\Domains\Accounts\Controllers\AuditLogController;
use App\Domains\Accounts\Controllers\Auth\ForgotPasswordController;
use App\Domains\Accounts\Controllers\Auth\LoginController;
use App\Domains\Accounts\Controllers\Auth\ResetPasswordController;
use App\Domains\Accounts\Controllers\DashboardController;
use App\Domains\Accounts\Controllers\ProfileController;
use App\Domains\Accounts\Controllers\UserController;
use App\Domains\Attendance\Controllers\AttendanceController;
use App\Domains\Exams\Controllers\ExamPapersController;
use App\Domains\Exams\Controllers\ExamResultsController;
use App\Domains\Exams\Controllers\ExamsController;
use App\Domains\Portals\Controllers\ParentPortalController;
use App\Domains\Portals\Controllers\StudentPortalController;
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
    Route::post('logout', [LoginController::class, 'logout'])->name('auth.logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('security', [ProfileController::class, 'security'])->name('security');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('password', [ProfileController::class, 'password'])->name('password');
    });

    /* -------- Staff-only modules (role middleware applied per module) -------- */

    Route::middleware(['role:super_admin,principal'])->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    Route::middleware(['role:super_admin,principal,registrar'])->name('academics.')->group(function () {
        Route::get('academics', [AcademicsController::class, 'index'])->name('index');

        Route::get('academics/grades', [GradeLevelController::class, 'index'])->name('grades.index');

        Route::get('academics/classes', [ClassRoomController::class, 'index'])->name('classes.index');
        Route::get('academics/classes/create', [ClassRoomController::class, 'create'])->name('classes.create');
        Route::post('academics/classes', [ClassRoomController::class, 'store'])->name('classes.store');
        Route::get('academics/classes/{classRoom}/edit', [ClassRoomController::class, 'edit'])->name('classes.edit');
        Route::put('academics/classes/{classRoom}', [ClassRoomController::class, 'update'])->name('classes.update');
        Route::delete('academics/classes/{classRoom}', [ClassRoomController::class, 'destroy'])->name('classes.destroy');
        Route::get('academics/classes/{classRoom}/subjects', [ClassRoomController::class, 'subjects'])->name('classes.subjects');
        Route::put('academics/classes/{classRoom}/subjects', [ClassRoomController::class, 'saveSubjects'])->name('classes.subjects.update');

        Route::get('academics/subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('academics/subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
        Route::post('academics/subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::get('academics/subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
        Route::put('academics/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('academics/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
    });

    Route::middleware(['role:super_admin,principal,registrar,teacher'])->name('attendance.')->group(function () {
        Route::get('attendance', [AttendanceController::class, 'index'])->name('index');
        Route::get('attendance/take', [AttendanceController::class, 'create'])->name('create');
        Route::post('attendance/sessions', [AttendanceController::class, 'store'])->name('store');
        Route::get('attendance/sessions/{session}', [AttendanceController::class, 'show'])->name('show');
        Route::put('attendance/sessions/{session}', [AttendanceController::class, 'update'])->name('update');
        Route::post('attendance/sessions/{session}/close', [AttendanceController::class, 'close'])->name('close');
    });

    Route::post('api/v1/attendance/sync', [AttendanceController::class, 'sync'])
        ->middleware('role:super_admin,principal,registrar,teacher');

    Route::middleware(['role:super_admin,principal,registrar,teacher'])->name('exams.')->group(function () {
        Route::get('exams', [ExamsController::class, 'index'])->name('index');
        Route::get('exams/create', [ExamsController::class, 'create'])->name('create');
        Route::post('exams', [ExamsController::class, 'store'])->name('store');
        Route::get('exams/{exam}', [ExamsController::class, 'show'])->name('show');
        Route::get('exams/{exam}/edit', [ExamsController::class, 'edit'])->name('edit');
        Route::put('exams/{exam}', [ExamsController::class, 'update'])->name('update');
        Route::delete('exams/{exam}', [ExamsController::class, 'destroy'])->name('destroy');

        Route::post('exams/{exam}/publish', [ExamsController::class, 'publish'])->name('publish');
        Route::post('exams/{exam}/complete', [ExamsController::class, 'complete'])->name('complete');

        Route::get('exams/{exam}/papers', [ExamPapersController::class, 'edit'])->name('papers.edit');
        Route::put('exams/{exam}/papers', [ExamPapersController::class, 'update'])->name('papers.update');

        Route::get('exams/papers/{paper}/results', [ExamResultsController::class, 'board'])->name('results');
        Route::put('exams/papers/{paper}/results', [ExamResultsController::class, 'save'])->name('results.save');
    });

    Route::middleware(['role:super_admin,principal,registrar,teacher,accountant'])->name('students.')->group(function () {
        Route::get('students', [StudentController::class, 'index'])->name('index');

        Route::middleware(['role:super_admin,principal,registrar'])->group(function () {
            Route::get('students/create', [StudentController::class, 'create'])->name('create');
            Route::post('students', [StudentController::class, 'store'])->name('store');
        });

        Route::get('students/{student}', [StudentController::class, 'show'])->name('show');
        Route::get('students/{student}/id-card', [StudentController::class, 'idCard'])->name('id-card');

        Route::middleware(['role:super_admin,principal,registrar'])->group(function () {
            Route::get('students/{student}/edit', [StudentController::class, 'edit'])->name('edit');
            Route::put('students/{student}', [StudentController::class, 'update'])->name('update');
            Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('destroy');
        });
    });

    Route::middleware(['role:super_admin,principal,registrar,teacher'])->name('guardians.')->group(function () {
        Route::get('guardians', [GuardianController::class, 'index'])->name('index');

        Route::middleware(['role:super_admin,principal,registrar'])->group(function () {
            Route::get('guardians/create', [GuardianController::class, 'create'])->name('create');
            Route::post('guardians', [GuardianController::class, 'store'])->name('store');
        });

        Route::get('guardians/{guardian}', [GuardianController::class, 'show'])->name('show');

        Route::middleware(['role:super_admin,principal,registrar'])->group(function () {
            Route::get('guardians/{guardian}/edit', [GuardianController::class, 'edit'])->name('edit');
            Route::put('guardians/{guardian}', [GuardianController::class, 'update'])->name('update');
            Route::delete('guardians/{guardian}', [GuardianController::class, 'destroy'])->name('destroy');
        });
    });

    Route::middleware(['role:super_admin,principal'])->name('cms.')->group(function () {
        Route::get('website', [CmsController::class, 'index'])->name('index');

        Route::name('pages.')->group(function () {
            Route::get('website/pages', [PageController::class, 'index'])->name('index');
            Route::get('website/pages/{page}/edit', [PageController::class, 'edit'])->name('edit');
            Route::put('website/pages/{page}', [PageController::class, 'update'])->name('update');
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

Route::middleware(['role:super_admin,principal'])->name('audit.')->group(function () {
    Route::get('audit', [AuditLogController::class, 'index'])->name('index');
    Route::get('audit/{auditLog}', [AuditLogController::class, 'show'])->name('show');
});

/* --------------------- Public website (guests) --------------------- */
// NOTE: the `{page}` catch-all is registered LAST so that `/login`, `/dashboard`
// and the staff route tree are always matched first.

Route::name('public.')->group(function () {
    Route::get('news', [PublicWebsiteController::class, 'news'])->name('news');
    Route::get('news/{news}', [PublicWebsiteController::class, 'newsShow'])->name('news-show');
    Route::get('gallery', [PublicWebsiteController::class, 'gallery'])->name('gallery');
    Route::get('{page}', [PublicWebsiteController::class, 'show'])->name('page');
});

/* --------------------- Student portal ------------------------------ */

Route::middleware(['auth', 'active', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('dashboard', [StudentPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('attendance', [StudentPortalController::class, 'attendance'])->name('attendance');
    Route::get('results', [StudentPortalController::class, 'results'])->name('results');
    Route::get('profile', [StudentPortalController::class, 'profile'])->name('profile');
});

/* --------------------- Parent portal ------------------------------- */

Route::middleware(['auth', 'active', 'role:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('dashboard', [ParentPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('wards', [ParentPortalController::class, 'wards'])->name('wards');
    Route::get('wards/{student}', [ParentPortalController::class, 'wardShow'])->name('wards.show');
    Route::get('billing', [ParentPortalController::class, 'billing'])->name('billing');
});
    });

    Route::middleware(['role:super_admin,principal'])->name('audit.')->group(function () {
        Route::get('audit', [AuditLogController::class, 'index'])->name('index');
        Route::get('audit/{auditLog}', [AuditLogController::class, 'show'])->name('show');
    });
});
