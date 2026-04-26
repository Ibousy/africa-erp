<?php

namespace App\Http\Controllers;

use App\Models\AccountingTransaction;
use App\Models\ClientPayment;
use App\Models\SupplierPayment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = AccountingTransaction::where('tenant_id', $this->tid());
        if ($request->filled('type'))  $query->where('type', $request->type);
        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->month);
            $query->whereYear('date', $year)->whereMonth('date', $month);
        }

        $transactions = $query->orderByDesc('date')->paginate(20)->withQueryString();

        $base  = AccountingTransaction::where('tenant_id', $this->tid());
        $stats = [
            'recettes_month' => (clone $base)->where('type', 'recette')->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount'),
            'depenses_month' => (clone $base)->where('type', 'depense')->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount'),
            'recettes_year'  => (clone $base)->where('type', 'recette')->whereYear('date', now()->year)->sum('amount'),
            'depenses_year'  => (clone $base)->where('type', 'depense')->whereYear('date', now()->year)->sum('amount'),
        ];
        $stats['solde_month'] = $stats['recettes_month'] - $stats['depenses_month'];
        $stats['solde_year']  = $stats['recettes_year']  - $stats['depenses_year'];

        return view('accounting.index', compact('transactions', 'stats'));
    }

    public function create()
    {
        return view('accounting.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'           => 'required|in:recette,depense',
            'category'       => 'required|string|max:100',
            'amount'         => 'required|numeric|min:0.01',
            'date'           => 'required|date',
            'description'    => 'required|string|max:255',
            'reference'      => 'nullable|string|max:50',
            'payment_method' => 'nullable|string|max:50',
            'notes'          => 'nullable|string',
        ]);

        $data['tenant_id'] = $this->tid();
        AccountingTransaction::create($data);
        return redirect()->route('accounting.index')->with('success', 'Transaction enregistrée.');
    }

    public function reports(Request $request)
    {
        $year = (int) $request->get('year', now()->year);

        $base = AccountingTransaction::where('tenant_id', $this->tid())->whereYear('date', $year);

        // Mensuel: recettes vs dépenses
        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $rec = (clone $base)->where('type', 'recette')->whereMonth('date', $m)->sum('amount');
            $dep = (clone $base)->where('type', 'depense')->whereMonth('date', $m)->sum('amount');
            $monthly[$m] = ['recettes' => $rec, 'depenses' => $dep, 'solde' => $rec - $dep];
        }

        // Compte de résultat annuel
        $totalRecettes = (clone $base)->where('type', 'recette')->sum('amount');
        $totalDepenses = (clone $base)->where('type', 'depense')->sum('amount');
        $resultatNet   = $totalRecettes - $totalDepenses;

        // Recettes par catégorie
        $recettesCategories = (clone $base)->where('type', 'recette')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')->orderByDesc('total')->get();

        // Dépenses par catégorie
        $depensesCategories = (clone $base)->where('type', 'depense')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')->orderByDesc('total')->get();

        // Trésorerie cumulée (solde au fil des mois)
        $tresorerie = [];
        $cumul = 0;
        foreach ($monthly as $m => $data) {
            $cumul += $data['solde'];
            $tresorerie[$m] = $cumul;
        }

        // Encaissements clients et paiements fournisseurs de l'année
        $encaissementsClients = 0;
        $paiementsFournisseurs = 0;
        if (class_exists(ClientPayment::class)) {
            $encaissementsClients = ClientPayment::where('tenant_id', $this->tid())
                ->whereYear('payment_date', $year)->sum('amount');
        }
        if (class_exists(SupplierPayment::class)) {
            $paiementsFournisseurs = SupplierPayment::where('tenant_id', $this->tid())
                ->whereYear('payment_date', $year)->sum('amount');
        }

        $years = range(now()->year - 3, now()->year + 1);

        return view('accounting.reports', compact(
            'year', 'years', 'monthly', 'totalRecettes', 'totalDepenses', 'resultatNet',
            'recettesCategories', 'depensesCategories', 'tresorerie',
            'encaissementsClients', 'paiementsFournisseurs'
        ));
    }

    public function edit(AccountingTransaction $accounting)
    {
        abort_if($accounting->tenant_id !== $this->tid(), 403);
        return view('accounting.edit', compact('accounting'));
    }

    public function update(Request $request, AccountingTransaction $accounting)
    {
        abort_if($accounting->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'type'           => 'required|in:recette,depense',
            'category'       => 'required|string|max:100',
            'amount'         => 'required|numeric|min:0.01',
            'date'           => 'required|date',
            'description'    => 'required|string|max:255',
            'reference'      => 'nullable|string|max:50',
            'payment_method' => 'nullable|string|max:50',
            'notes'          => 'nullable|string',
        ]);

        $accounting->update($data);
        return redirect()->route('accounting.index')->with('success', 'Transaction mise à jour.');
    }

    public function destroy(AccountingTransaction $accounting)
    {
        abort_if($accounting->tenant_id !== $this->tid(), 403);
        $accounting->delete();
        return redirect()->route('accounting.index')->with('success', 'Transaction supprimée.');
    }
}
