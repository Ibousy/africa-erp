<?php

namespace App\Http\Controllers;

use App\Models\ErpNotification;
use App\Models\Meeting;
use App\Models\User;
use App\Services\ZoomService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MeetingController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index()
    {
        $dept  = auth()->user()->department;
        $query = Meeting::with('organizer')->where('tenant_id', $this->tid());

        if ($dept && !auth()->user()->isAdmin()) {
            $query->whereJsonContains('departments', $dept);
        }

        $upcoming = (clone $query)
            ->where('scheduled_at', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at')
            ->get();

        $past = (clone $query)
            ->where(function ($q) {
                $q->where('scheduled_at', '<', now())->orWhere('status', '!=', 'scheduled');
            })
            ->latest('scheduled_at')
            ->limit(20)
            ->get();

        $departments = User::DEPARTMENTS;

        return view('meetings.index', compact('upcoming', 'past', 'departments'));
    }

    public function create()
    {
        $departments = User::DEPARTMENTS;
        return view('meetings.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'agenda'           => 'nullable|string',
            'scheduled_at'     => 'required|date',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'departments'      => 'required|array|min:1',
            'departments.*'    => 'string',
            'location'         => 'nullable|string|max:200',
            'use_zoom'         => 'nullable|boolean',
        ]);

        $data['tenant_id']    = $this->tid();
        $data['organized_by'] = auth()->id();
        $data['status']       = 'scheduled';

        // Auto-generate Jitsi Meet link (no API needed)
        $roomCode       = 'ERP-' . strtolower(Str::random(4)) . '-' . strtolower(Str::random(4)) . '-' . strtolower(Str::random(4));
        $data['meet_url'] = 'https://meet.jit.si/' . $roomCode;

        // Optional Zoom (if credentials are configured)
        $zoom = app(ZoomService::class);
        if ($zoom->isConfigured()) {
            $zoomData = $zoom->createMeeting(
                $data['title'],
                \Carbon\Carbon::parse($data['scheduled_at']),
                (int) $data['duration_minutes'],
                $data['agenda'] ?? ''
            );
            if ($zoomData) {
                $data['zoom_meeting_id'] = $zoomData['id'];
                $data['zoom_join_url']   = $zoomData['join_url'];
                $data['zoom_start_url']  = $zoomData['start_url'];
                $data['zoom_password']   = $zoomData['password'] ?? null;
            }
        }

        $meeting = Meeting::create($data);

        // Notify ALL users of the tenant (except organizer)
        $users = User::where('tenant_id', $this->tid())
            ->where('id', '!=', auth()->id())
            ->get();

        $when = $meeting->scheduled_at->format('d/m/Y à H:i');
        $body = 'Le ' . $when . ' · ' . $meeting->duration_minutes . ' min · par ' . auth()->user()->name;
        foreach ($users as $u) {
            ErpNotification::notify(
                $u->id,
                $this->tid(),
                'meeting',
                '📅 Réunion — ' . $meeting->title,
                $body,
                route('meetings.show', $meeting),
                'meeting'
            );
        }

        return redirect()->route('meetings.show', $meeting)->with('success', 'Réunion créée et équipes notifiées.');
    }

    public function show(Meeting $meeting)
    {
        abort_if($meeting->tenant_id !== $this->tid(), 403);
        $meeting->load('organizer');
        $departments = User::DEPARTMENTS;
        return view('meetings.show', compact('meeting', 'departments'));
    }

    public function minutes(Request $request, Meeting $meeting)
    {
        abort_if($meeting->tenant_id !== $this->tid(), 403);

        $request->validate([
            'minutes' => 'nullable|string',
            'status'  => 'required|in:scheduled,done,cancelled',
        ]);

        $meeting->update([
            'minutes' => $request->minutes,
            'status'  => $request->status,
        ]);

        return back()->with('success', 'Compte rendu enregistré.');
    }

    public function destroy(Meeting $meeting)
    {
        abort_if($meeting->tenant_id !== $this->tid(), 403);
        abort_if($meeting->organized_by !== auth()->id() && !auth()->user()->isAdmin(), 403);

        // Delete Zoom meeting if it exists
        if ($meeting->zoom_meeting_id) {
            app(ZoomService::class)->deleteMeeting($meeting->zoom_meeting_id);
        }

        $meeting->delete();
        return redirect()->route('meetings.index')->with('success', 'Réunion supprimée.');
    }
}
