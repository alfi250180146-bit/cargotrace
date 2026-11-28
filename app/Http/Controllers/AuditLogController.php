<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'entity_type' => ['nullable', 'string', 'max:50'],
            'action' => ['nullable', Rule::in(['created', 'updated', 'deleted'])],
        ]);

        $logs = AuditLog::with('user')
            ->when($filters['entity_type'] ?? null, fn ($query, $value) => $query->where('entity_type', $value))
            ->when($filters['action'] ?? null, fn ($query, $value) => $query->where('action', $value))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('audit-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'entityTypes' => AuditLog::query()->distinct()->orderBy('entity_type')->pluck('entity_type'),
        ]);
    }
}