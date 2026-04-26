<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PerformanceReview;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = PerformanceReview::with('employee')
            ->where('performance_reviews.tenant_id', $this->tid())
            ->join('employees', 'employees.id', '=', 'performance_reviews.employee_id')
            ->select('performance_reviews.*');

        if ($request->filled('period')) $query->where('performance_reviews.period', $request->period);

        $reviews = $query->orderBy('performance_reviews.reviewed_at', 'desc')->paginate(20)->withQueryString();

        return view('hr.performance.index', compact('reviews'));
    }

    public function create()
    {
        $employees = Employee::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        return view('hr.performance.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'         => 'required|exists:employees,id',
            'period'              => 'required|string|max:10',
            'punctuality_score'   => 'required|integer|min:0|max:5',
            'productivity_score'  => 'required|integer|min:0|max:5',
            'quality_score'       => 'required|integer|min:0|max:5',
            'teamwork_score'      => 'required|integer|min:0|max:5',
            'initiative_score'    => 'required|integer|min:0|max:5',
            'strengths'           => 'nullable|string',
            'improvements'        => 'nullable|string',
            'notes'               => 'nullable|string',
            'reviewed_at'         => 'required|date',
        ]);

        Employee::where('id', $data['employee_id'])->where('tenant_id', $this->tid())->firstOrFail();

        $data['tenant_id'] = $this->tid();
        PerformanceReview::create($data);

        return redirect()->route('hr.performance.index')->with('success', 'Évaluation enregistrée.');
    }

    public function destroy(PerformanceReview $performance)
    {
        abort_if($performance->tenant_id !== $this->tid(), 403);
        $performance->delete();
        return back()->with('success', 'Évaluation supprimée.');
    }
}
