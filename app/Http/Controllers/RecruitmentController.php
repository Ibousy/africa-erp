<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\Request;

class RecruitmentController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index()
    {
        $postings = JobPosting::where('tenant_id', $this->tid())
            ->withCount('applications')
            ->latest()->paginate(15);

        $stats = [
            'ouvert'   => JobPosting::where('tenant_id', $this->tid())->where('status', 'ouvert')->count(),
            'candidats'=> JobApplication::where('tenant_id', $this->tid())->count(),
            'embauche' => JobApplication::where('tenant_id', $this->tid())->where('status', 'embauche')->count(),
        ];

        return view('hr.recruitment.index', compact('postings', 'stats'));
    }

    public function create()
    {
        return view('hr.recruitment.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:191',
            'department'  => 'nullable|string|max:100',
            'type'        => 'required|in:cdi,cdd,stage,freelance',
            'description' => 'nullable|string',
            'requirements'=> 'nullable|string',
            'salary_min'  => 'nullable|numeric|min:0',
            'salary_max'  => 'nullable|numeric|min:0',
            'closes_at'   => 'nullable|date',
        ]);

        $data['tenant_id'] = $this->tid();
        $data['status']    = 'ouvert';
        JobPosting::create($data);

        return redirect()->route('hr.recruitment.index')->with('success', 'Offre d\'emploi publiée.');
    }

    public function show(JobPosting $posting)
    {
        abort_if($posting->tenant_id !== $this->tid(), 403);
        $posting->load('applications');
        return view('hr.recruitment.show', compact('posting'));
    }

    public function updateStatus(Request $request, JobPosting $posting)
    {
        abort_if($posting->tenant_id !== $this->tid(), 403);
        $request->validate(['status' => 'required|in:ouvert,ferme,pourvue']);
        $posting->update(['status' => $request->status]);
        return back()->with('success', 'Statut mis à jour.');
    }

    public function storeApplication(Request $request, JobPosting $posting)
    {
        abort_if($posting->tenant_id !== $this->tid(), 403);
        $data = $request->validate([
            'applicant_name' => 'required|string|max:191',
            'email'          => 'nullable|email|max:191',
            'phone'          => 'nullable|string|max:30',
            'notes'          => 'nullable|string',
        ]);

        JobApplication::create([
            'tenant_id'      => $this->tid(),
            'job_posting_id' => $posting->id,
            'applicant_name' => $data['applicant_name'],
            'email'          => $data['email'] ?? null,
            'phone'          => $data['phone'] ?? null,
            'notes'          => $data['notes'] ?? null,
            'status'         => 'nouveau',
            'applied_at'     => now(),
        ]);

        return back()->with('success', 'Candidature enregistrée.');
    }

    public function updateApplication(Request $request, JobApplication $application)
    {
        abort_if($application->tenant_id !== $this->tid(), 403);
        $request->validate(['status' => 'required|in:nouveau,examine,convoque,embauche,refuse']);
        $application->update(['status' => $request->status]);
        return back()->with('success', 'Candidature mise à jour.');
    }

    public function destroy(JobPosting $posting)
    {
        abort_if($posting->tenant_id !== $this->tid(), 403);
        $posting->delete();
        return redirect()->route('hr.recruitment.index')->with('success', 'Offre supprimée.');
    }
}
