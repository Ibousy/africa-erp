<?php

namespace App\Http\Controllers;

use App\Models\ErpNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index()
    {
        $notifications = ErpNotification::where('user_id', auth()->id())
            ->where('tenant_id', $this->tid())
            ->latest()
            ->get();

        // Delete all after loading so they're gone once read
        ErpNotification::where('user_id', auth()->id())
            ->where('tenant_id', $this->tid())
            ->delete();

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(ErpNotification $notification)
    {
        abort_if($notification->user_id !== auth()->id(), 403);
        $notification->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function markAllRead()
    {
        ErpNotification::where('user_id', auth()->id())
            ->where('tenant_id', $this->tid())
            ->delete();
        return back()->with('success', 'Toutes les notifications supprimées.');
    }

    public function unreadCount()
    {
        $count = ErpNotification::where('user_id', auth()->id())
            ->where('tenant_id', $this->tid())
            ->whereNull('read_at')
            ->count();
        return response()->json(['count' => $count]);
    }

    public function panel()
    {
        $notifications = ErpNotification::where('user_id', auth()->id())
            ->where('tenant_id', $this->tid())
            ->latest()
            ->limit(10)
            ->get();

        $payload = $notifications->map(fn($n) => [
            'id'    => $n->id,
            'title' => $n->title,
            'body'  => $n->body,
            'link'  => $n->link,
            'icon'  => $n->icon,
            'read'  => $n->read_at !== null,
            'time'  => $n->created_at->diffForHumans(),
        ]);

        // Delete after loading so they're gone once seen
        ErpNotification::where('user_id', auth()->id())
            ->where('tenant_id', $this->tid())
            ->delete();

        return response()->json($payload);
    }
}
