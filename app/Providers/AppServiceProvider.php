<?php

namespace App\Providers;

use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\Subject;
use App\Domains\Academics\Policies\ClassRoomPolicy;
use App\Domains\Academics\Policies\SubjectPolicy;
use App\Domains\Accounts\Models\User;
use App\Domains\Accounts\Policies\UserPolicy;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Policies\AttendancePolicy;
use App\Domains\Cms\Models\GalleryItem;
use App\Domains\Cms\Models\NewsItem;
use App\Domains\Cms\Models\Page;
use App\Domains\Cms\Policies\CmsPolicy;
use App\Domains\Exams\Models\Exam;
use App\Domains\Exams\Policies\ExamPolicy;
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
        Gate::policy(AttendanceSession::class, AttendancePolicy::class);
        Gate::policy(Exam::class, ExamPolicy::class);
        Gate::policy(Page::class, CmsPolicy::class);
        Gate::policy(NewsItem::class, CmsPolicy::class);
        Gate::policy(GalleryItem::class, CmsPolicy::class);

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
