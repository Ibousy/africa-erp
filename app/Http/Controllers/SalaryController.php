<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = SalaryPayment::with('employee')
            ->where('salary_payments.tenant_id', $this->tid())
            ->join('employees', 'employees.id', '=', 'salary_payments.employee_id')
            ->select('salary_payments.*');

        if ($request->filled('period'))  $query->where('salary_payments.period', $request->period);
        if ($request->filled('status'))  $query->where('salary_payments.status', $request->status);

        $salaries = $query->orderBy('salary_payments.period', 'desc')->paginate(20)->withQueryString();

        $base  = SalaryPayment::where('tenant_id', $this->tid());
        $stats = [
            'total_brut' => (clone $base)->sum('base_salary'),
            'total_net'  => (clone $base)->sum('net_salary'),
            'paye'       => (clone $base)->where('status', 'paye')->count(),
            'en_attente' => (clone $base)->where('status', 'brouillon')->count(),
        ];

        $currentPeriod = now()->format('Y-m');

        return view('hr.salary.index', compact('salaries', 'stats', 'currentPeriod'));
    }

    public function create()
    {
        $employees = Employee::where('tenant_id', $this->tid())
            ->where('status', 'actif')->orderBy('name')->get();
        return view('hr.salary.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period'      => 'required|regex:/^\d{4}-\d{2}$/',
            'base_salary' => 'required|numeric|min:0',
            'bonuses'     => 'nullable|numeric|min:0',
            'deductions'  => 'nullable|numeric|min:0',
            'notes'       => 'nullable|string',
        ]);

        $emp = Employee::where('id', $data['employee_id'])->where('tenant_id', $this->tid())->firstOrFail();

        SalaryPayment::updateOrCreate(
            ['employee_id' => $emp->id, 'period' => $data['period']],
            [
                'tenant_id'   => $this->tid(),
                'base_salary' => $data['base_salary'],
                'bonuses'     => $data['bonuses'] ?? 0,
                'deductions'  => $data['deductions'] ?? 0,
                'status'      => 'brouillon',
                'notes'       => $data['notes'] ?? null,
            ]
        );

        return redirect()->route('hr.salaries.index')->with('success', 'Bulletin créé.');
    }

    public function pdf(SalaryPayment $salary)
    {
        abort_if($salary->tenant_id !== $this->tid(), 403);
        $salary->load(['employee', 'tenant']);
        $pdf = Pdf::loadView('pdf.salary', compact('salary'))->setPaper('a4', 'portrait');
        return $pdf->download('Bulletin-' . $salary->employee->name . '-' . $salary->period . '.pdf');
    }

    public function markPaid(SalaryPayment $salary)
    {
        abort_if($salary->tenant_id !== $this->tid(), 403);
        $salary->update(['status' => 'paye', 'paid_at' => now()]);
        return back()->with('success', 'Salaire marqué comme payé.');
    }

    public function destroy(SalaryPayment $salary)
    {
        abort_if($salary->tenant_id !== $this->tid(), 403);
        $salary->delete();
        return back()->with('success', 'Bulletin supprimé.');
    }

    public function generateBulk(Request $request)
    {
        $request->validate(['period' => 'required|regex:/^\d{4}-\d{2}$/']);
        $period    = $request->period;
        $employees = Employee::where('tenant_id', $this->tid())->where('status', 'actif')->get();
        $created   = 0;

        foreach ($employees as $emp) {
            $exists = SalaryPayment::where('employee_id', $emp->id)->where('period', $period)->exists();
            if (!$exists && $emp->salary > 0) {
                SalaryPayment::create([
                    'tenant_id'   => $this->tid(),
                    'employee_id' => $emp->id,
                    'period'      => $period,
                    'base_salary' => $emp->salary,
                    'bonuses'     => 0,
                    'deductions'  => 0,
                    'status'      => 'brouillon',
                ]);
                $created++;
            }
        }

        return back()->with('success', "$created bulletin(s) généré(s) pour $period.");
    }
}
