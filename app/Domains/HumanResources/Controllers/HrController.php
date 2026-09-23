<?php

namespace App\Domains\HumanResources\Controllers;

use App\Domains\HumanResources\Services\HrDashboardService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HrController extends Controller
{
    public function __construct(private readonly HrDashboardService $dashboard) {}

    public function dashboard(): View
    {
        $this->requirePermission('hr.view');

        $data = $this->dashboard->data();
        $headcountByDepartment = $this->dashboard->headcountByDepartment();
        $upcomingBirthdays = $this->dashboard->upcomingBirthdays();
        $attendance = $this->dashboard->todaysAttendance();

        return view('hr.dashboard', compact('data', 'headcountByDepartment', 'upcomingBirthdays', 'attendance'));
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }
}