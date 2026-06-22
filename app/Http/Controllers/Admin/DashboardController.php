<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'departments' => Department::count(),
                'roles' => Role::count(),
                'permissions' => Permission::count(),
            ],
            'recentAuditLogs' => AuditLog::with('user')->latest()->limit(8)->get(),
        ]);
    }
}
