<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = Leave::with('employee')
            ->where('leaves.tenant_id', $this->tid())
            ->join('employees', 'employees.id', '=', 'leaves.employee_id')
            ->select('leaves.*');

        if ($request->filled('status')) {
            $query->where('leaves.status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('leaves.type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where('employees.name', 'like', '%' . $request->search . '%');
        }

        $leaves = $query->orderBy('leaves.start_date', 'desc')->paginate(20)->withQueryString();

        $base = Leave::where('tenant_id', $this->tid());
        $stats = [
            'total'      => (clone $base)->count(),
            'en_attente' => (clone $base)->where('status', 'en_attente')->count(),
            'approuve'   => (clone $base)->where('status', 'approuve')->count(),
            'refuse'     => (clone $base)->where('status', 'refuse')->count(),
        ];

        return view('hr.leaves.index', compact('leaves', 'stats'));
    }

    public function create()
    {
        $employees = Employee::where('tenant_id', $this->tid())
            ->where('status', 'actif')
            ->orderBy('name')
            ->get();

        return view('hr.leaves.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type'        => 'required|in:' . implode(',', array_keys(Leave::TYPES)),
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'nullable|string|max:500',
            'notes'       => 'nullable|string|max:500',
        ]);

        // Verify employee belongs to this tenant
        $employee = Employee::where('id', $data['employee_id'])
            ->where('tenant_id', $this->tid())
            ->firstOrFail();

        $start = \Carbon\Carbon::parse($data['start_date']);
        $end   = \Carbon\Carbon::parse($data['end_date']);
        $days  = $start->diffInWeekdays($end) + 1;

        Leave::create([
            'tenant_id'   => $this->tid(),
            'employee_id' => $employee->id,
            'type'        => $data['type'],
            'start_date'  => $data['start_date'],
            'end_date'    => $data['end_date'],
            'days'        => max(1, $days),
            'status'      => 'en_attente',
            'reason'      => $data['reason'] ?? null,
            'notes'       => $data['notes'] ?? null,
        ]);

        return redirect()->route('hr.leaves.index')->with('success', 'Demande de congé enregistrée.');
    }

    public function updateStatus(Request $request, Leave $leave)
    {
        abort_if($leave->tenant_id !== $this->tid(), 403);
        $request->validate(['status' => 'required|in:approuve,refuse,en_attente']);

        $leave->update(['status' => $request->status]);

        // Sync employee status if approved/refused
        if ($request->status === 'approuve') {
            $leave->employee->update(['status' => 'conge']);
        } elseif ($request->status === 'refuse' || $request->status === 'en_attente') {
            // Only revert if no other approved leave is current
            $hasOther = Leave::where('employee_id', $leave->employee_id)
                ->where('id', '!=', $leave->id)
                ->where('status', 'approuve')
                ->whereDate('end_date', '>=', now())
                ->exists();
            if (!$hasOther) {
                $leave->employee->update(['status' => 'actif']);
            }
        }

        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(Leave $leave)
    {
        abort_if($leave->tenant_id !== $this->tid(), 403);
        $leave->delete();
        return redirect()->route('hr.leaves.index')->with('success', 'Congé supprimé.');
    }
}
