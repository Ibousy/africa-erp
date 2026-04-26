<?php

namespace App\Http\Controllers;

use App\Models\ErpNotification;
use App\Models\RenewalRequest;
use App\Models\User;
use Illuminate\Http\Request;

class RenewalController extends Controller
{
    public function show()
    {
        $user   = auth()->user();
        $tenant = $user->tenant;

        if (!$tenant) return redirect()->route('dashboard');

        // Demande déjà en attente → page de confirmation
        $pending = $tenant->pendingRenewal();
        if ($pending) {
            return view('subscription.pending', compact('pending', 'tenant'));
        }

        $lastRequest = $tenant->renewalRequests()->latest()->first();

        return view('subscription.renew', compact('tenant', 'lastRequest'));
    }

    public function store(Request $request)
    {
        $user   = auth()->user();
        $tenant = $user->tenant;

        if (!$tenant) return redirect()->route('dashboard');

        // Bloquer les doubles soumissions
        if ($tenant->pendingRenewal()) {
            return redirect()->route('subscription.renew')
                ->with('error', 'Une demande est déjà en attente de traitement.');
        }

        $data = $request->validate([
            'plan'              => 'required|in:starter,pro,enterprise',
            'duration_months'   => 'required|integer|in:1,3,6,12',
            'payment_method'    => 'required|in:especes,virement,mobile,cheque',
            'payment_reference' => 'nullable|string|max:191',
            'notes'             => 'nullable|string|max:500',
        ]);

        $amount = RenewalRequest::computeAmount($data['plan'], (int) $data['duration_months']);

        $renewal = RenewalRequest::create([
            'tenant_id'         => $tenant->id,
            'plan'              => $data['plan'],
            'duration_months'   => $data['duration_months'],
            'amount'            => $amount,
            'status'            => 'pending',
            'payment_method'    => $data['payment_method'],
            'payment_reference' => $data['payment_reference'] ?? null,
            'notes'             => $data['notes'] ?? null,
        ]);

        // Notifier l'administrateur de la plateforme (super admin)
        $superAdmin = User::where('role', 'super_admin')->first();
        if ($superAdmin) {
            ErpNotification::create([
                'user_id'   => $superAdmin->id,
                'tenant_id' => $tenant->id,
                'type'      => 'renewal_request',
                'title'     => '📋 Nouvelle demande de renouvellement',
                'body'      => "{$tenant->company_name} demande un renouvellement {$data['plan']} ({$data['duration_months']} mois).",
                'link'      => '/superadmin/renewals',
                'icon'      => 'refresh',
            ]);
        }

        return view('subscription.pending', compact('renewal', 'tenant'));
    }
}
