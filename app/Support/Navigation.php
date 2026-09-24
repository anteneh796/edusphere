<?php

namespace App\Support;

use App\Support\Enums\RoleName;

class Navigation
{
    /**
     * Application navigation grouped into sections.
     *
     * Each item: ['label', 'route', 'icon', 'permissions' => [...], 'enabled' => bool]
     * The item is shown when the user holds any of its permissions.
     */
    public static function sections(bool $withPlaceholders = false): array
    {
        $sidebar = [
            [
                'title' => __('Overview'),
                'items' => [
                    ['label' => __('Dashboard'), 'route' => 'dashboard', 'icon' => 'dashboard', 'enabled' => true],
                ],
            ],
            [
                'title' => __('Academics'),
                'items' => [
                    ['label' => __('Students'), 'route' => 'students.index', 'icon' => 'graduation', 'enabled' => true, 'permissions' => ['students.view']],                    ['label' => __('Class Roster'), 'route' => 'students.roster', 'icon' => 'list', 'enabled' => true, 'permissions' => ['students.view']],
                    ['label' => __('Promotions'), 'route' => 'students.promote', 'icon' => 'trending-up', 'enabled' => true, 'permissions' => ['students.promote']],
                    ['label' => __('Classes & Subjects'), 'route' => 'academics.index', 'icon' => 'book-open', 'enabled' => true, 'permissions' => ['academics.view']],
                    ['label' => __('Attendance'), 'route' => 'attendance.index', 'icon' => 'clipboard-check', 'enabled' => true, 'permissions' => ['attendance.view']],
                    ['label' => __('Exams & Results'), 'route' => 'exams.index', 'icon' => 'award', 'enabled' => true, 'permissions' => ['exams.view']],
                ],
            ],
            [
                'title' => __('Attendance'),
                'items' => [
                    ['label' => __('Overview'), 'route' => 'attendance.dashboard', 'icon' => 'clipboard-check', 'enabled' => true, 'permissions' => ['attendance.view']],
                    ['label' => __('Sessions'), 'route' => 'attendance.index', 'icon' => 'list', 'enabled' => true, 'permissions' => ['attendance.view']],
                    ['label' => __('Reports'), 'route' => 'attendance.reports.daily', 'icon' => 'bar-chart', 'enabled' => true, 'permissions' => ['attendance.view']],
                    ['label' => __('Corrections'), 'route' => 'attendance.corrections', 'icon' => 'refresh', 'enabled' => true, 'permissions' => ['attendance.approve']],
                    ['label' => __('Alerts'), 'route' => 'attendance.alerts', 'icon' => 'alert-triangle', 'enabled' => true, 'permissions' => ['attendance.view']],
                    ['label' => __('Attendance settings'), 'route' => 'attendance.settings', 'icon' => 'settings', 'enabled' => true, 'permissions' => ['attendance.configure']],
                ],
            ],
            [
                'title' => __('Admissions'),
                'items' => [
                    ['label' => __('Admissions'), 'route' => 'admissions.dashboard', 'icon' => 'clipboard', 'enabled' => true, 'permissions' => ['admissions.view']],
                    ['label' => __('Applications'), 'route' => 'admissions.applications.index', 'icon' => 'file-text', 'enabled' => true, 'permissions' => ['admissions.view']],
                    ['label' => __('Applicants'), 'route' => 'admissions.applicants.index', 'icon' => 'users', 'enabled' => true, 'permissions' => ['admissions.view']],
                    ['label' => __('Documents'), 'route' => 'admissions.documents.index', 'icon' => 'list', 'enabled' => true, 'permissions' => ['admissions.view']],
                    ['label' => __('Assessments'), 'route' => 'admissions.assessments.index', 'icon' => 'clipboard-check', 'enabled' => true, 'permissions' => ['admissions.view']],
                    ['label' => __('Approvals'), 'route' => 'admissions.approvals.index', 'icon' => 'check-square', 'enabled' => true, 'permissions' => ['admissions.view']],
                    ['label' => __('Waiting List'), 'route' => 'admissions.waitlist.index', 'icon' => 'users', 'enabled' => true, 'permissions' => ['admissions.view']],
                    ['label' => __('Grade Capacity'), 'route' => 'admissions.capacity.index', 'icon' => 'target', 'enabled' => true, 'permissions' => ['admissions.capacity']],
                    ['label' => __('Reports'), 'route' => 'admissions.reports.index', 'icon' => 'bar-chart', 'enabled' => true, 'permissions' => ['admissions.view']],
                ],
            ],
            [
                'title' => __('Human Resources'),
                'items' => [
                    ['label' => __('HR Dashboard'), 'route' => 'hr.dashboard', 'icon' => 'users', 'enabled' => true, 'permissions' => ['hr.view']],
                    ['label' => __('Employees'), 'route' => 'hr.employees.index', 'icon' => 'user-check', 'enabled' => true, 'permissions' => ['hr.view']],
                    ['label' => __('Attendance'), 'route' => 'hr.attendance.index', 'icon' => 'clipboard-check', 'enabled' => true, 'permissions' => ['hr.view']],
                    ['label' => __('Leave Requests'), 'route' => 'hr.leave.index', 'icon' => 'calendar', 'enabled' => true, 'permissions' => ['hr.view']],
                    ['label' => __('My Leave'), 'route' => 'hr.leave.my', 'icon' => 'calendar', 'enabled' => true, 'permissions' => ['hr.view']],
                    ['label' => __('Performance'), 'route' => 'hr.performance.index', 'icon' => 'trending-up', 'enabled' => true, 'permissions' => ['hr.view']],
                    ['label' => __('Training'), 'route' => 'hr.training.index', 'icon' => 'book-open', 'enabled' => true, 'permissions' => ['hr.view']],
                    ['label' => __('Documents'), 'route' => 'hr.documents.index', 'icon' => 'folder', 'enabled' => true, 'permissions' => ['hr.view']],
                    ['label' => __('Recruitment'), 'route' => 'hr.candidates.index', 'icon' => 'user-plus', 'enabled' => true, 'permissions' => ['hr.view']],
                    ['label' => __('Official Letters'), 'route' => 'hr.letters.index', 'icon' => 'file-text', 'enabled' => true, 'permissions' => ['hr.view']],
                    ['label' => __('Contracts'), 'route' => 'hr.contracts.index', 'icon' => 'briefcase', 'enabled' => true, 'permissions' => ['hr.payroll']],
                    ['label' => __('Payroll'), 'route' => 'hr.payroll.index', 'icon' => 'banknote', 'enabled' => true, 'permissions' => ['hr.payroll']],
                    ['label' => __('Departments'), 'route' => 'hr.departments.index', 'icon' => 'layers', 'enabled' => true, 'permissions' => ['hr.view']],
                    ['label' => __('Positions'), 'route' => 'hr.positions.index', 'icon' => 'briefcase', 'enabled' => true, 'permissions' => ['hr.view']],
                    ['label' => __('Leave Types'), 'route' => 'hr.leave-types.index', 'icon' => 'list', 'enabled' => true, 'permissions' => ['hr.view']],
                    ['label' => __('Reports'), 'route' => 'hr.reports.index', 'icon' => 'bar-chart', 'enabled' => true, 'permissions' => ['hr.reports']],
                ],
            ],
            [
                'title' => __('Tools'),
                'items' => [
                    ['label' => __('Reports'), 'route' => 'reports.index', 'icon' => 'bar-chart', 'enabled' => true, 'permissions' => ['reports.view']],
                    ['label' => __('Notifications'), 'route' => 'notifications.index', 'icon' => 'bell', 'enabled' => true, 'permissions' => ['notifications.view']],
                    ['label' => __('Website & News'), 'route' => 'cms.index', 'icon' => 'compass', 'enabled' => true, 'permissions' => ['cms.view']],
                    ['label' => __('Page Sections'), 'route' => 'cms.content-blocks.index', 'icon' => 'layers', 'enabled' => true, 'permissions' => ['cms.view']],
                ],
            ],
            [
                'title' => __('Administration'),
                'items' => [
                    ['label' => __('Staff'), 'route' => 'staff.index', 'icon' => 'users', 'enabled' => true, 'permissions' => ['staff.view']],
                    ['label' => __('Approvals'), 'route' => 'approvals.index', 'icon' => 'check-square', 'enabled' => true, 'permissions' => ['approvals.view']],
                    ['label' => __('Parent Services'), 'route' => 'parent-services.requests.index', 'icon' => 'inbox', 'enabled' => true, 'permissions' => ['parent_services.view']],
                    ['label' => __('Users & Roles'), 'route' => 'users.index', 'icon' => 'users', 'enabled' => true, 'permissions' => ['users.view']],
                    ['label' => __('Role Permissions'), 'route' => 'roles.index', 'icon' => 'shield', 'enabled' => true, 'permissions' => ['users.view']],
                    ['label' => __('Active Sessions'), 'route' => 'security.sessions', 'icon' => 'devices', 'enabled' => true, 'permissions' => ['audit.view']],
                    ['label' => __('Login History'), 'route' => 'security.login-history', 'icon' => 'clock', 'enabled' => true, 'permissions' => ['audit.view']],
                    ['label' => __('Audit Log'), 'route' => 'audit.index', 'icon' => 'shield-check', 'enabled' => true, 'permissions' => ['audit.view']],
                    ['label' => __('Settings'), 'route' => 'settings.index', 'icon' => 'settings', 'enabled' => true, 'permissions' => ['settings.view']],
                ],
            ],
        ];

        if (! $withPlaceholders) {
            return $sidebar;
        }

        foreach ($sidebar as &$section) {
            $section['items'] = array_values(array_filter($section['items'], fn ($item) => $item['enabled'] ?? false));
            $section['items'] = array_map(fn ($item) => $item + ['enabled' => true], $section['items']);
        }

        return array_values(array_filter($sidebar, fn ($section) => ! empty($section['items'])));
    }

    public static function forUser(?object $user, bool $withPlaceholders = false): array
    {
        $sections = self::sections($withPlaceholders);

        if (! $user) {
            return [];
        }

        $roles = $user->roles->pluck('name')->all();

        if (self::portalSections($roles) !== null) {
            return self::portalSections($roles);
        }

        return self::filterForUser($user, $sections);
    }

    /**
     * Navigation for the student and parent portals. Returns null for staff.
     */
    private static function portalSections(array $roles): ?array
    {
        if (in_array(RoleName::Teacher->value, $roles, true)) {
            return [[
                'title' => __('My Classroom'),
                'items' => [
                    ['label' => __('Dashboard'), 'route' => 'cms.teacher.dashboard', 'icon' => 'dashboard', 'enabled' => true],
                    ['label' => __('My Timetable'), 'route' => 'cms.teacher.timetable', 'icon' => 'calendar', 'enabled' => true],
                    ['label' => __('My Classes'), 'route' => 'cms.teacher.classes', 'icon' => 'users', 'enabled' => true],
                    ['label' => __('Attendance'), 'route' => 'cms.teacher.attendance', 'icon' => 'clipboard-check', 'enabled' => true],
                    ['label' => __('Lesson Plans'), 'route' => 'cms.teacher.lesson-plans', 'icon' => 'book-open', 'enabled' => true],
                    ['label' => __('Curriculum'), 'route' => 'cms.teacher.curriculum', 'icon' => 'layers', 'enabled' => true],
                    ['label' => __('Homework'), 'route' => 'cms.teacher.homework', 'icon' => 'pencil', 'enabled' => true],
                    ['label' => __('Assessments & Grades'), 'route' => 'cms.teacher.assessments', 'icon' => 'award', 'enabled' => true],
                    ['label' => __('Behavior'), 'route' => 'cms.teacher.behavior', 'icon' => 'flag', 'enabled' => true],
                    ['label' => __('Student Progress'), 'route' => 'cms.teacher.progress', 'icon' => 'trending-up', 'enabled' => true],
                    ['label' => __('Homeroom'), 'route' => 'cms.teacher.homeroom', 'icon' => 'home', 'enabled' => true],
                    ['label' => __('Messages'), 'route' => 'cms.teacher.messages', 'icon' => 'mail', 'enabled' => true],
                    ['label' => __('Meetings'), 'route' => 'cms.teacher.meetings', 'icon' => 'calendar', 'enabled' => true],
                    ['label' => __('Resources'), 'route' => 'cms.teacher.resources', 'icon' => 'folder', 'enabled' => true],
                    ['label' => __('Reports'), 'route' => 'cms.teacher.reports', 'icon' => 'bar-chart', 'enabled' => true],
                ],
            ]];
        }

        if (in_array(RoleName::Student->value, $roles, true)) {
            return [[
                'title' => __('My School'),
                'items' => [
                    ['label' => __('Dashboard'), 'route' => 'cms.student.dashboard', 'icon' => 'dashboard', 'enabled' => true],
                    ['label' => __('Attendance'), 'route' => 'cms.student.attendance', 'icon' => 'clipboard-check', 'enabled' => true],
                    ['label' => __('Results'), 'route' => 'cms.student.results', 'icon' => 'award', 'enabled' => true],
                    ['label' => __('Profile'), 'route' => 'cms.student.profile', 'icon' => 'user', 'enabled' => true],
                ],
            ]];
        }

        if (in_array(RoleName::Parent->value, $roles, true)) {
            return [[
                'title' => __('My Family'),
                'items' => [
                    ['label' => __('Dashboard'), 'route' => 'cms.parent.dashboard', 'icon' => 'dashboard', 'enabled' => true],
                    ['label' => __('My Children'), 'route' => 'cms.parent.wards', 'icon' => 'users', 'enabled' => true],
                    ['label' => __('Academics'), 'route' => 'cms.parent.academics', 'icon' => 'award', 'enabled' => true],
                    ['label' => __('Attendance'), 'route' => 'cms.parent.attendance', 'icon' => 'clipboard-check', 'enabled' => true],
                    ['label' => __('Homework'), 'route' => 'cms.parent.homework', 'icon' => 'pencil', 'enabled' => true],
                    ['label' => __('Calendar'), 'route' => 'cms.parent.calendar', 'icon' => 'calendar', 'enabled' => true],
                    ['label' => __('Announcements'), 'route' => 'cms.parent.announcements', 'icon' => 'newspaper', 'enabled' => true],
                    ['label' => __('Messages'), 'route' => 'cms.parent.messages', 'icon' => 'mail', 'enabled' => true],
                    ['label' => __('Meetings'), 'route' => 'cms.parent.meetings', 'icon' => 'calendar', 'enabled' => true],
                    ['label' => __('Documents'), 'route' => 'cms.parent.documents', 'icon' => 'folder', 'enabled' => true],
                    ['label' => __('Requests'), 'route' => 'cms.parent.requests', 'icon' => 'inbox', 'enabled' => true],
                    ['label' => __('Notifications'), 'route' => 'cms.parent.notifications', 'icon' => 'bell', 'enabled' => true],
                    ['label' => __('Settings'), 'route' => 'cms.parent.settings', 'icon' => 'settings', 'enabled' => true],
                ],
            ]];
        }

        return null;
    }

    private static function filterForUser(object $user, array $sections): array
    {
        $sections = array_map(function ($section) use ($user) {
            $section['items'] = array_values(array_filter($section['items'], function ($item) use ($user) {
                if (! ($item['enabled'] ?? false)) {
                    return false;
                }

                if (! isset($item['permissions'])) {
                    return true;
                }

                return $user->hasAnyPermission($item['permissions']);
            }));

            return $section;
        }, $sections);

        return array_values(array_filter($sections, fn ($section) => ! empty($section['items'])));
    }
}
