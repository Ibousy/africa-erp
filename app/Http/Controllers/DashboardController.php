<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\EnergyConsumption;
use App\Models\Invoice;
use App\Models\Machine;
use App\Models\Maintenance;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\QualityControl;

class DashboardController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index()
    {
        $tid = $this->tid();

        $stats = [
            'products_total'          => Product::where('tenant_id', $tid)->where('status', 'actif')->count(),
            'low_stock'               => Product::where('tenant_id', $tid)->whereColumn('quantity_in_stock', '<=', 'min_stock_alert')->where('min_stock_alert', '>', 0)->count(),
            'productions_en_cours'    => ProductionOrder::where('tenant_id', $tid)->where('status', 'en_cours')->count(),
            'machines_en_panne'       => Machine::where('tenant_id', $tid)->where('status', 'en_panne')->count(),
            'maintenances_planifiees' => Maintenance::where('tenant_id', $tid)->where('status', 'planifie')->count(),
            'clients_total'           => Client::where('tenant_id', $tid)->count(),
            'factures_impayees'       => Invoice::where('tenant_id', $tid)->where('type', 'facture')->where('status', 'envoye')->count(),
            'ca_mois'                 => Invoice::where('tenant_id', $tid)->where('type', 'facture')->where('status', 'paye')->whereMonth('issue_date', now()->month)->sum('total'),
        ];

        $low_stock_products = Product::where('tenant_id', $tid)
            ->whereColumn('quantity_in_stock', '<=', 'min_stock_alert')
            ->where('min_stock_alert', '>', 0)->take(5)->get();

        $recent_productions = ProductionOrder::where('tenant_id', $tid)->with('product')->latest()->take(5)->get();

        $upcoming_maintenances = Maintenance::where('tenant_id', $tid)
            ->with('machine')->where('status', 'planifie')
            ->orderBy('scheduled_date')->take(5)->get();

        $energy_this_month = EnergyConsumption::where('tenant_id', $tid)
            ->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total_cost');

        $quality_stats = [
            'total'  => QualityControl::where('tenant_id', $tid)->whereMonth('check_date', now()->month)->count(),
            'passed' => QualityControl::where('tenant_id', $tid)->where('status', 'passe')->whereMonth('check_date', now()->month)->count(),
        ];

        return view('dashboard', compact(
            'stats', 'low_stock_products', 'recent_productions',
            'upcoming_maintenances', 'energy_this_month', 'quality_stats'
        ));
    }
}
