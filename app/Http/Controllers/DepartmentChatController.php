<?php

namespace App\Http\Controllers;

use App\Models\DepartmentMessage;
use App\Models\ErpNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DepartmentChatController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    private const DEPARTMENTS = [
        'production'   => 'Production',
        'logistique'   => 'Logistique',
        'commercial'   => 'Commercial',
        'rh'           => 'Ressources Humaines',
        'comptabilite' => 'Comptabilité',
        'qualite'      => 'Qualité',
        'maintenance'  => 'Maintenance',
        'direction'    => 'Direction',
    ];

    public function index(Request $request)
    {
        $myDept    = auth()->user()->department ?? 'direction';
        $withDept  = $request->get('with', $this->defaultPartner($myDept));
        $departments = self::DEPARTMENTS;

        // Mark messages to my dept as read
        DepartmentMessage::where('tenant_id', $this->tid())
            ->where('to_department', $myDept)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Conversation between my dept and selected dept
        $messages = DepartmentMessage::with('sender')
            ->where('tenant_id', $this->tid())
            ->where(function ($q) use ($myDept, $withDept) {
                $q->where(fn($q2) => $q2->where('from_department', $myDept)->where('to_department', $withDept))
                  ->orWhere(fn($q2) => $q2->where('from_department', $withDept)->where('to_department', $myDept));
            })
            ->oldest()
            ->get();

        // Unread counts per department (messages sent TO my dept)
        $unreadCounts = DepartmentMessage::where('tenant_id', $this->tid())
            ->where('to_department', $myDept)
            ->whereNull('read_at')
            ->selectRaw('from_department, COUNT(*) as cnt')
            ->groupBy('from_department')
            ->pluck('cnt', 'from_department');

        return view('chat.index', compact('messages', 'myDept', 'withDept', 'departments', 'unreadCounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'to_department' => 'required|string',
            'body'          => 'required_without:voice|nullable|string|max:2000',
        ]);

        $myDept = auth()->user()->department ?? 'direction';

        DepartmentMessage::create([
            'tenant_id'       => $this->tid(),
            'from_user_id'    => auth()->id(),
            'from_department' => $myDept,
            'to_department'   => $request->to_department,
            'body'            => $request->body,
            'type'            => 'text',
        ]);

        $this->notifyDepartment($request->to_department, $myDept, $request->body ?? '[Message]');

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }
        return back();
    }

    public function storeVoice(Request $request)
    {
        $request->validate([
            'to_department' => 'required|string',
            'audio'         => 'required|file|mimes:webm,ogg,mp3,wav|max:10240',
            'duration'      => 'nullable|integer|min:1',
        ]);

        $myDept = auth()->user()->department ?? 'direction';
        $path   = $request->file('audio')->store('voice-messages/' . $this->tid(), 'public');

        DepartmentMessage::create([
            'tenant_id'       => $this->tid(),
            'from_user_id'    => auth()->id(),
            'from_department' => $myDept,
            'to_department'   => $request->to_department,
            'type'            => 'voice',
            'voice_path'      => $path,
            'voice_duration'  => $request->duration ?? 0,
        ]);

        $this->notifyDepartment($request->to_department, $myDept, '🎤 Message vocal');

        return response()->json(['ok' => true, 'path' => Storage::url($path)]);
    }

    public function messages(Request $request)
    {
        $myDept   = auth()->user()->department ?? 'direction';
        $withDept = $request->get('with');

        DepartmentMessage::where('tenant_id', $this->tid())
            ->where('to_department', $myDept)
            ->where('from_department', $withDept)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = DepartmentMessage::with('sender')
            ->where('tenant_id', $this->tid())
            ->where(function ($q) use ($myDept, $withDept) {
                $q->where(fn($q2) => $q2->where('from_department', $myDept)->where('to_department', $withDept))
                  ->orWhere(fn($q2) => $q2->where('from_department', $withDept)->where('to_department', $myDept));
            })
            ->oldest()
            ->get()
            ->map(fn($m) => [
                'id'       => $m->id,
                'mine'     => $m->from_department === $myDept,
                'sender'   => $m->sender?->name ?? $m->from_department,
                'body'     => $m->body,
                'type'     => $m->type,
                'voice'    => $m->voice_path ? Storage::url($m->voice_path) : null,
                'duration' => $m->voice_duration,
                'time'     => $m->created_at->format('H:i'),
                'date'     => $m->created_at->format('d/m'),
            ]);

        return response()->json($messages);
    }

    private function defaultPartner(string $dept): string
    {
        return match($dept) {
            'production'   => 'logistique',
            'logistique'   => 'production',
            'commercial'   => 'comptabilite',
            'comptabilite' => 'commercial',
            default        => 'direction',
        };
    }

    private function notifyDepartment(string $toDept, string $fromDept, string $preview): void
    {
        $users = User::where('tenant_id', $this->tid())
            ->where(function ($q) use ($toDept) {
                $q->where('department', $toDept)->orWhere('role', 'admin');
            })->get();

        $deptLabel = self::DEPARTMENTS[$fromDept] ?? $fromDept;
        $short     = mb_strlen($preview) > 60 ? mb_substr($preview, 0, 57) . '...' : $preview;

        foreach ($users as $u) {
            ErpNotification::notify(
                $u->id, $this->tid(),
                'message',
                'Message de ' . $deptLabel,
                $short,
                route('chat.index', ['with' => $fromDept]),
                'message'
            );
        }
    }
}
