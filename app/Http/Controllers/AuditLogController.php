<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->type === UserType::ADMIN, 403);

        $auditLogs = AuditLog::with('user')
            ->latest()
            ->paginate(25);

        return view('audit-logs.index', compact('auditLogs'));
    }
}
