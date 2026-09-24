<?php

/**
 * RBAC source of truth.
 *
 * Role name => list of permission names (module.action).
 * Every permanent role carries exactly these permissions; administrators can
 * refine individual roles from the Roles & Permissions screen.
 *
 * EduSphere is intentionally a single-school deployment. Do not reintroduce
 * tenant or multi-school assumptions without an explicit scope decision.
 */

return [

    'roles' => [
        'super_admin' => [
            'dashboard.view',
            'students.view', 'students.create', 'students.edit', 'students.delete', 'students.export',
            'students.medical', 'students.documents', 'students.transfer', 'students.promote',
                        'academics.view', 'academics.create', 'academics.edit', 'academics.delete',
            'attendance.view', 'attendance.create', 'attendance.edit', 'attendance.delete',
            'attendance.approve', 'attendance.configure',
            'exams.view', 'exams.create', 'exams.edit', 'exams.delete', 'exams.publish', 'exams.export',
            'cms.view', 'cms.create', 'cms.edit', 'cms.delete', 'cms.publish',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'staff.view', 'staff.create', 'staff.edit',
            'approvals.view', 'approvals.create', 'approvals.approve',
            'notifications.view',
            'settings.view', 'settings.edit',
            'audit.view',
            'reports.view', 'reports.export',
            'parent_services.view', 'parent_services.process',
            'admissions.view', 'admissions.create', 'admissions.edit', 'admissions.delete', 'admissions.approve', 'admissions.enroll', 'admissions.verify', 'admissions.capacity',
            'hr.view', 'hr.create', 'hr.edit', 'hr.delete', 'hr.leave.approve', 'hr.payroll', 'hr.reports',
        ],

        'school_admin' => [
            'dashboard.view',
            'students.view', 'students.create', 'students.edit',
            'students.medical', 'students.documents', 'students.transfer', 'students.promote',

            'academics.view', 'academics.create', 'academics.edit',
            'attendance.view',
            'attendance.approve', 'attendance.configure',
            'exams.view',
            'cms.view', 'cms.create', 'cms.edit', 'cms.delete',
            'staff.view', 'staff.create', 'staff.edit',
            'approvals.view', 'approvals.create', 'approvals.approve',
            'notifications.view',
            'reports.view', 'reports.export',
            'settings.view',
            'parent_services.view', 'parent_services.process',
            'admissions.view', 'admissions.create', 'admissions.edit', 'admissions.approve', 'admissions.enroll', 'admissions.verify', 'admissions.capacity',
            'hr.view', 'hr.create', 'hr.edit', 'hr.delete', 'hr.payroll',
        ],

        'principal' => [
            'dashboard.view',
            'students.view', 'students.create', 'students.edit', 'students.delete', 'students.export',
            'students.medical', 'students.documents', 'students.transfer', 'students.promote',
                        'academics.view', 'academics.create', 'academics.edit', 'academics.delete',
            'attendance.view', 'attendance.create', 'attendance.edit',
            'attendance.approve', 'attendance.configure',
            'exams.view', 'exams.create', 'exams.edit', 'exams.delete', 'exams.publish',
            'cms.view', 'cms.create', 'cms.edit', 'cms.delete', 'cms.publish',
            'staff.view', 'staff.create', 'staff.edit',
            'approvals.view', 'approvals.create', 'approvals.approve',
            'notifications.view',
            'audit.view',
            'reports.view', 'reports.export',
            'parent_services.view', 'parent_services.process',
            'admissions.view', 'admissions.create', 'admissions.edit', 'admissions.approve', 'admissions.enroll', 'admissions.verify', 'admissions.capacity',
            'hr.view', 'hr.create', 'hr.leave.approve', 'hr.reports',
        ],

        'vice_principal' => [
            'dashboard.view',
            'students.view', 'students.create', 'students.edit',
            'students.medical', 'students.documents', 'students.transfer', 'students.promote',

            'academics.view', 'academics.create', 'academics.edit',
            'attendance.view', 'attendance.create', 'attendance.edit',
            'attendance.approve',
            'exams.view', 'exams.create', 'exams.edit',
            'staff.view',
            'approvals.view', 'approvals.create', 'approvals.approve',
            'notifications.view',
            'reports.view', 'reports.export',
            'admissions.view', 'admissions.create', 'admissions.edit', 'admissions.approve', 'admissions.enroll', 'admissions.verify', 'admissions.capacity',
            'hr.view', 'hr.leave.approve',
        ],

        'registrar' => [
            'dashboard.view',
            'students.view', 'students.create', 'students.edit', 'students.export',
            'students.medical', 'students.documents', 'students.transfer', 'students.promote',

            'academics.view',
            'attendance.view', 'attendance.create', 'attendance.edit',
            'attendance.approve',
            'exams.view', 'exams.create', 'exams.edit', 'exams.publish', 'exams.export',
            'staff.view',
            'approvals.view', 'approvals.create',
            'notifications.view',
            'reports.view', 'reports.export',
            'parent_services.view', 'parent_services.process',
            'admissions.view', 'admissions.create', 'admissions.edit', 'admissions.verify', 'admissions.approve', 'admissions.enroll',
            'hr.view',
        ],


        'hr_officer' => [
            'dashboard.view',
            'staff.view', 'staff.create', 'staff.edit',
            'approvals.view', 'approvals.create',
            'notifications.view',
            'reports.view',
            'hr.view', 'hr.create', 'hr.edit', 'hr.delete', 'hr.leave.approve', 'hr.payroll', 'hr.reports',
        ],

        'reception' => [
            'dashboard.view',
            'students.view', 'students.create',
            'approvals.view', 'approvals.create',
            'notifications.view',
            'admissions.view', 'admissions.create', 'admissions.edit',
        ],

        'teacher' => [
            'dashboard.view',
            'students.view',

            'attendance.view', 'attendance.create', 'attendance.edit',
            'exams.view',
            'reports.view',
            'timetables.view',
            'lesson_plans.view', 'lesson_plans.create', 'lesson_plans.edit', 'lesson_plans.delete',
            'curriculum.view', 'curriculum.create', 'curriculum.edit',
            'homework.view', 'homework.create', 'homework.edit', 'homework.grade',
            'assessments.view', 'assessments.create', 'assessments.edit', 'assessments.grade',
            'behavior.view', 'behavior.create', 'behavior.edit',
            'progress.view',
            'homeroom.view',
            'messages.view', 'messages.create',
            'meetings.view', 'meetings.update',
            'resources.view', 'resources.create', 'resources.edit',
        ],

        'student' => [
            'dashboard.view',
            'exams.view',
        ],

        'parent' => [
            'dashboard.view',
            'exams.view',
        ],
    ],

    'role_labels' => [
        'super_admin' => 'Super Administrator',
        'school_admin' => 'School Administrator',
        'principal' => 'Principal',
        'vice_principal' => 'Vice Principal',
        'registrar' => 'Registrar',
        'hr_officer' => 'HR Officer',
        'reception' => 'Reception',
        'teacher' => 'Teacher',
        'student' => 'Student',
        'parent' => 'Parent',
    ],

    /**
     * Default idle-session timeout (minutes) per role. Can be overridden per
     * school from Settings > Security. A missing value falls back to the
     * global default.
     */
    'session_timeouts' => [
        'super_admin' => 20,
        'principal' => 30,
        'school_admin' => 30,
        'vice_principal' => 30,
        'registrar' => 30,
        'hr_officer' => 30,
        'reception' => 45,
        'teacher' => 45,
        'student' => 30,
        'parent' => 30,
    ],
];
