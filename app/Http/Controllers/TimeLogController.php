<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TimeLog;
use Illuminate\Http\Request;

class TimeLogController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $date  = $request->get('date', now()->format('Y-m-d'));
        $month = $request->get('month', now()->format('Y-m'));

        $query = TimeLog::with('employee')
            ->where('time_logs.tenant_id', $this->tid())
            ->join('employees', 'employees.id', '=', 'time_logs.employee_id')
            ->select('time_logs.*');

        if ($request->filled('employee_id')) $query->where('time_logs.employee_id', $request->employee_id);
        if ($request->get('view') === 'month') {
            $query->whereRaw("DATE_FORMAT(time_logs.date, '%Y-%m') = ?", [$month]);
        } else {
            $query->where('time_logs.date', $date);
        }

        $logs      = $query->orderBy('time_logs.date', 'desc')->paginate(30)->withQueryString();
        $employees = Employee::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();

        $stats = [
            'present' => TimeLog::where('tenant_id', $this->tid())->where('date', $date)->where('status', 'present')->count(),
            'absent'  => TimeLog::where('tenant_id', $this->tid())->where('date', $date)->where('status', 'absent')->count(),
            'retard'  => TimeLog::where('tenant_id', $this->tid())->where('date', $date)->where('status', 'retard')->count(),
            'total'   => Employee::where('tenant_id', $this->tid())->where('status', 'actif')->count(),
        ];

        return view('hr.timelog.index', compact('logs', 'employees', 'stats', 'date', 'month'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'  => 'required|exists:employees,id',
            'date'         => 'required|date',
            'check_in'     => 'nullable|date_format:H:i',
            'check_out'    => 'nullable|date_format:H:i',
            'status'       => 'required|in:present,absent,retard,conge',
            'notes'        => 'nullable|string|max:255',
        ]);

        Employee::where('id', $data['employee_id'])->where('tenant_id', $this->tid())->firstOrFail();

        $hours = null;
        if (!empty($data['check_in']) && !empty($data['check_out'])) {
            $in  = \Carbon\Carbon::parse($data['check_in']);
            $out = \Carbon\Carbon::parse($data['check_out']);
            $hours = round($out->diffInMinutes($in) / 60, 2);
        }

        TimeLog::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'date' => $data['date']],
            [
                'tenant_id'    => $this->tid(),
                'check_in'     => $data['check_in'] ?? null,
                'check_out'    => $data['check_out'] ?? null,
                'hours_worked' => $hours,
                'status'       => $data['status'],
                'notes'        => $data['notes'] ?? null,
            ]
        );

        return back()->with('success', 'Pointage enregistré.');
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'logs'        => 'required|array',
            'logs.*.eid'  => 'required|exists:employees,id',
            'logs.*.status' => 'required|in:present,absent,retard,conge',
        ]);

        $date = $request->date;
        foreach ($request->logs as $log) {
            Employee::where('id', $log['eid'])->where('tenant_id', $this->tid())->firstOrFail();
            TimeLog::updateOrCreate(
                ['employee_id' => $log['eid'], 'date' => $date],
                [
                    'tenant_id'    => $this->tid(),
                    'check_in'     => $log['check_in'] ?? null,
                    'check_out'    => $log['check_out'] ?? null,
                    'status'       => $log['status'],
                ]
            );
        }

        return back()->with('success', 'Pointage collectif enregistré.');
    }
}
