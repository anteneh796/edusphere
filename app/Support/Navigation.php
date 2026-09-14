<?php

namespace App\Support;

use App\Support\Enums\RoleName;

class Navigation
{
    /**
     * Application navigation grouped into sections.
     *
     * Each item: ['label', 'route', 'icon', 'access' => [roles...], 'enabled' => bool]
     */
    public static function sections(bool $withPlaceholders = false): array
    {
        $sidebar = [
            [
                'title' => 'Overview',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard', 'enabled' => true],
                ],
            ],
            [
                'title' => 'Academics',
                'items' => [
                    ['label' => 'Students', 'route' => 'students.index', 'icon' => 'graduation', 'enabled' => true, 'access' => ['principal', 'registrar', 'teacher', 'accountant']],
                    ['label' => 'Guardians', 'route' => 'guardians.index', 'icon' => 'users', 'enabled' => true, 'access' => ['principal', 'registrar', 'teacher']],
                    ['label' => 'Classes & Subjects', 'route' => 'academics.index', 'icon' => 'book-open', 'enabled' => true, 'access' => ['principal', 'registrar']],
                    ['label' => 'Attendance', 'route' => 'attendance.index', 'icon' => 'clipboard-check', 'enabled' => true, 'access' => ['principal', 'registrar', 'teacher']],
                    ['label' => 'Exams & Results', 'route' => 'exams.index', 'icon' => 'award', 'enabled' => true, 'access' => ['principal', 'registrar', 'teacher']],
                ],
            ],
            [
                'title' => 'Finance',
                'items' => [
                    ['label' => 'Payments', 'route' => 'finance.payments.index', 'icon' => 'banknote', 'enabled' => false, 'access' => ['principal', 'accountant']],
                    ['label' => 'Invoices', 'route' => 'finance.invoices.index', 'icon' => 'receipt', 'enabled' => false, 'access' => ['principal', 'accountant']],
                    ['label' => 'Fee Structures', 'route' => 'finance.fees.index', 'icon' => 'wallet', 'enabled' => false, 'access' => ['principal', 'accountant']],
                ],
            ],
            [
                'title' => 'Tools',
                'items' => [
                    ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'bar-chart', 'enabled' => false, 'access' => ['principal', 'registrar', 'teacher', 'accountant']],
                    ['label' => 'Website & News', 'route' => 'cms.index', 'icon' => 'compass', 'enabled' => false, 'access' => ['principal']],
                ],
            ],
            [
                'title' => 'Administration',
                'items' => [
                    ['label' => 'Users & Roles', 'route' => 'users.index', 'icon' => 'users', 'enabled' => true, 'access' => ['super_admin', 'principal']],
                    ['label' => 'Audit Log', 'route' => 'audit.index', 'icon' => 'shield-check', 'enabled' => true, 'access' => ['super_admin', 'principal']],
                    ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'settings', 'enabled' => true, 'access' => ['super_admin', 'principal']],
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
        $isSuperAdmin = in_array(RoleName::SuperAdmin->value, $roles, true);

        $sections = array_map(function ($section) use ($roles, $isSuperAdmin) {
            $section['items'] = array_values(array_filter($section['items'], function ($item) use ($roles, $isSuperAdmin) {
                if (! ($item['enabled'] ?? false)) {
                    return false;
                }

                if (! isset($item['access'])) {
                    return true;
                }

                return $isSuperAdmin || array_intersect($item['access'], $roles);
            }));

            return $section;
        }, $sections);

        return array_values(array_filter($sections, fn ($section) => ! empty($section['items'])));
    }
}
