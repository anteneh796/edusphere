<?php

namespace App\Providers;

use App\Domains\Academics\Models\AcademicTerm;
use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Academics\Models\Section;
use App\Domains\Academics\Models\Subject;
use App\Domains\Academics\Policies\AcademicTermPolicy;
use App\Domains\Academics\Policies\AcademicYearPolicy;
use App\Domains\Academics\Policies\ClassRoomPolicy;
use App\Domains\Academics\Policies\GradeLevelPolicy;
use App\Domains\Academics\Policies\SectionPolicy;
use App\Domains\Academics\Policies\SubjectPolicy;
use App\Domains\Accounts\Models\Permission;
use App\Domains\Accounts\Models\Role;
use App\Domains\Accounts\Models\User;
use App\Domains\Accounts\Policies\UserPolicy;
use App\Domains\Admissions\Models\AdmissionApplication;
use App\Domains\Admissions\Models\ApplicantDocument;
use App\Domains\Admissions\Models\GradeCapacity;
use App\Domains\Admissions\Policies\AdmissionApplicationPolicy;
use App\Domains\Admissions\Policies\ApplicantDocumentPolicy;
use App\Domains\Admissions\Policies\GradeCapacityPolicy;
use App\Domains\Approvals\Models\ApprovalRequest;
use App\Domains\Approvals\Policies\ApprovalRequestPolicy;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Policies\AttendancePolicy;
use App\Domains\Cms\Models\ContentBlock;
use App\Domains\Cms\Models\Event;
use App\Domains\Cms\Models\GalleryItem;
use App\Domains\Cms\Models\NewsItem;
use App\Domains\Cms\Models\Page;
use App\Domains\Cms\Models\Testimonial;
use App\Domains\Cms\Policies\CmsPolicy;
use App\Domains\Exams\Models\Exam;
use App\Domains\Exams\Policies\ExamPolicy;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Notifications\Policies\NotificationPolicy;
use App\Domains\Settings\Models\Setting;
use App\Domains\Settings\Policies\SettingPolicy;
use App\Domains\Students\Models\Guardian;
use App\Domains\Students\Models\Student;
use App\Domains\Students\Policies\GuardianPolicy;
use App\Domains\Students\Policies\StudentPolicy;
use App\Support\Enums\RoleName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! app()->isProduction());
        Model::preventAccessingMissingAttributes(! app()->isProduction());
        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());

        Paginator::defaultView('vendor.pagination.edusphere');

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Setting::class, SettingPolicy::class);
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Guardian::class, GuardianPolicy::class);
        Gate::policy(Subject::class, SubjectPolicy::class);
        Gate::policy(ClassRoom::class, ClassRoomPolicy::class);
        Gate::policy(GradeLevel::class, GradeLevelPolicy::class);
        Gate::policy(AcademicYear::class, AcademicYearPolicy::class);
        Gate::policy(AcademicTerm::class, AcademicTermPolicy::class);
        Gate::policy(Section::class, SectionPolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);
        Gate::policy(ApprovalRequest::class, ApprovalRequestPolicy::class);
        Gate::policy(AttendanceSession::class, AttendancePolicy::class);
        Gate::policy(AdmissionApplication::class, AdmissionApplicationPolicy::class);
        Gate::policy(ApplicantDocument::class, ApplicantDocumentPolicy::class);
        Gate::policy(GradeCapacity::class, GradeCapacityPolicy::class);
        Gate::policy(Exam::class, ExamPolicy::class);
        Gate::policy(Page::class, CmsPolicy::class);
        Gate::policy(NewsItem::class, CmsPolicy::class);
        Gate::policy(GalleryItem::class, CmsPolicy::class);
        Gate::policy(Event::class, CmsPolicy::class);
        Gate::policy(Testimonial::class, CmsPolicy::class);
        Gate::policy(ContentBlock::class, CmsPolicy::class);

        Role::created(function (Role $role): void {
            $permissions = config("rbac.roles.{$role->name}", []);

            if (empty($permissions)) {
                return;
            }

            $role->label = $role->label ?: config("rbac.role_labels.{$role->name}", $role->label);
            $role->saveQuietly();

            $ids = collect($permissions)->map(fn (string $name): string => (string) Permission::updateOrCreate(['name' => $name], [
                'label' => $name,
                'module' => Str::before($name, '.'),
            ])->id);

            $role->permissions()->syncWithoutDetaching($ids->all());
        });

        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole(RoleName::SuperAdmin->value)) {
                return true;
            }

            return null;
        });

        Gate::after(function (User $user, string $ability, ?bool $result, mixed $arguments) {
            if ($result === false && $user->hasRole(RoleName::SuperAdmin->value)) {
                return true;
            }

            return $result;
        });
    }
}
