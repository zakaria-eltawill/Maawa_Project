<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = Audit::query()->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->filled('actor')) {
            $actor = $request->actor;
            $query->where(function ($q) use ($actor) {
                $q->where('actor_name', 'like', "%{$actor}%")
                    ->orWhere('actor_email', 'like', "%{$actor}%");
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $perPage = min((int) $request->get('per_page', 20), 100);
        $audits = $query->paginate($perPage)->withQueryString();

        $actions = Audit::select('action')->distinct()->orderBy('action')->pluck('action');
        $entityTypes = Audit::select('entity_type')->distinct()->orderBy('entity_type')->pluck('entity_type');

        return view('admin.audit.index', [
            'audits' => $audits,
            'actions' => $actions,
            'entityTypes' => $entityTypes,
        ]);
    }

    public function show(string $id)
    {
        $audit = Audit::findOrFail($id);

        return view('admin.audit.show', compact('audit'));
    }
}
