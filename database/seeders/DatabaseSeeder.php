<?php

namespace Database\Seeders;

use App\Models\AccountingTransaction;
use App\Models\Client;
use App\Models\EnergyConsumption;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lead;
use App\Models\Machine;
use App\Models\Maintenance;
use App\Models\MrpPlan;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\ProductionTask;
use App\Models\PurchaseOrder;
use App\Models\QualityControl;
use App\Models\Shipment;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Tenant de démonstration
        $tenant = Tenant::create([
            'company_name'        => 'AfricaERP Démo',
            'slug'                => 'africaerp-demo',
            'industry'            => 'Matériaux de construction',
            'country'             => 'Sénégal',
            'city'                => 'Dakar',
            'address'             => 'Zone industrielle de Mbao',
            'phone'               => '+221 33 800 00 00',
            'email'               => 'demo@africaerp.com',
            'theme'               => 'orange',
            'plan'                => 'pro',
            'status'              => 'active',
            'subscribed_at'       => now(),
            'onboarding_complete' => true,
        ]);

        $tid = $tenant->id;

        // Modules
        foreach ([
            'stock', 'production', 'mrp', 'quality', 'maintenance',
            'logistics', 'purchases', 'sales', 'crm', 'accounting', 'hr', 'energy', 'users',
        ] as $module) {
            TenantModule::create(['tenant_id' => $tid, 'module' => $module, 'enabled' => true]);
        }

        // Utilisateurs
        $admin = User::create([
            'tenant_id' => $tid, 'name' => 'Ibrahima Diallo',
            'email' => 'admin@africaerp.com', 'password' => Hash::make('password'),
            'role' => 'admin', 'phone' => '+221 77 100 00 01', 'is_active' => true,
        ]);
        $manager = User::create([
            'tenant_id' => $tid, 'name' => 'Fatou Sow',
            'email' => 'manager@africaerp.com', 'password' => Hash::make('password'),
            'role' => 'manager', 'phone' => '+221 77 100 00 02', 'is_active' => true,
        ]);
        $operator = User::create([
            'tenant_id' => $tid, 'name' => 'Moussa Ndiaye',
            'email' => 'operateur@africaerp.com', 'password' => Hash::make('password'),
            'role' => 'operateur', 'phone' => '+221 77 100 00 03', 'is_active' => true,
        ]);

        // Produits
        $products = collect([
            ['code' => 'MP-001', 'name' => 'Ciment Portland',    'category' => 'Matière première', 'unit' => 'kg',    'quantity_in_stock' => 5000,  'min_stock_alert' => 1000, 'unit_price' => 150],
            ['code' => 'MP-002', 'name' => 'Sable de rivière',   'category' => 'Matière première', 'unit' => 'kg',    'quantity_in_stock' => 12000, 'min_stock_alert' => 2000, 'unit_price' => 30],
            ['code' => 'MP-003', 'name' => 'Gravier 10/20',      'category' => 'Matière première', 'unit' => 'kg',    'quantity_in_stock' => 800,   'min_stock_alert' => 1000, 'unit_price' => 40],
            ['code' => 'MP-004', 'name' => 'Fer à béton 12mm',   'category' => 'Matière première', 'unit' => 'barre', 'quantity_in_stock' => 250,   'min_stock_alert' => 50,   'unit_price' => 8500],
            ['code' => 'PF-001', 'name' => 'Parpaing 15x20x40', 'category' => 'Produit fini',     'unit' => 'pièce', 'quantity_in_stock' => 3200,  'min_stock_alert' => 500,  'unit_price' => 350],
            ['code' => 'PF-002', 'name' => 'Dalle béton 50x50', 'category' => 'Produit fini',     'unit' => 'pièce', 'quantity_in_stock' => 120,   'min_stock_alert' => 200,  'unit_price' => 2500],
            ['code' => 'PF-003', 'name' => 'Hourdis 16',         'category' => 'Produit fini',     'unit' => 'pièce', 'quantity_in_stock' => 450,   'min_stock_alert' => 100,  'unit_price' => 600],
            ['code' => 'CS-001', 'name' => 'Huile moteur 15W40', 'category' => 'Consommable',      'unit' => 'litre', 'quantity_in_stock' => 45,    'min_stock_alert' => 20,   'unit_price' => 3500],
        ])->map(fn($p) => Product::create(array_merge($p, ['tenant_id' => $tid, 'status' => 'actif', 'description' => null])));

        // Machines
        $machines = collect([
            ['code' => 'MCH-001', 'name' => 'Centrale à béton CBM-500',   'type' => 'Centrale à béton', 'location' => 'Hall A',  'status' => 'actif',    'power_kw' => 45,  'purchase_date' => '2021-03-15'],
            ['code' => 'MCH-002', 'name' => 'Presse hydraulique PH-200T', 'type' => 'Presse',           'location' => 'Hall B',  'status' => 'actif',    'power_kw' => 30,  'purchase_date' => '2020-06-10'],
            ['code' => 'MCH-003', 'name' => 'Vibrateur béton VB-100',     'type' => 'Vibrateur',        'location' => 'Hall A',  'status' => 'en_panne', 'power_kw' => 5,   'purchase_date' => '2019-11-20'],
            ['code' => 'MCH-004', 'name' => 'Compresseur 50L',            'type' => 'Compresseur',      'location' => 'Atelier', 'status' => 'actif',    'power_kw' => 2.2, 'purchase_date' => '2022-01-08'],
        ])->map(fn($m) => Machine::create(array_merge($m, ['tenant_id' => $tid])));

        // Maintenances
        Maintenance::create(['tenant_id' => $tid, 'machine_id' => $machines[2]->id, 'type' => 'corrective', 'description' => 'Remplacement moteur électrique défectueux', 'scheduled_date' => now()->addDays(3)->toDateString(),  'status' => 'planifie', 'cost' => 150000, 'technician_id' => $operator->id]);
        Maintenance::create(['tenant_id' => $tid, 'machine_id' => $machines[0]->id, 'type' => 'preventive', 'description' => 'Vidange et nettoyage trimestriel',           'scheduled_date' => now()->addDays(10)->toDateString(), 'status' => 'planifie', 'cost' => 25000,  'technician_id' => $operator->id]);
        Maintenance::create(['tenant_id' => $tid, 'machine_id' => $machines[1]->id, 'type' => 'preventive', 'description' => 'Contrôle hydraulique annuel',                'scheduled_date' => now()->subDays(5)->toDateString(),  'completed_date' => now()->subDays(5)->toDateString(), 'status' => 'termine', 'cost' => 75000, 'technician_id' => $operator->id]);

        // Clients
        $clients = collect([
            ['code' => 'CLI-001', 'name' => 'BTP Sénégal SARL',             'contact_person' => 'Papa Diop',         'phone' => '+221 33 820 10 10', 'email' => 'contact@btpsenegal.sn', 'city' => 'Dakar',       'country' => 'Sénégal'],
            ['code' => 'CLI-002', 'name' => 'Groupe Constructions Modernes', 'contact_person' => 'Aminata Ba',        'phone' => '+221 77 555 22 33', 'email' => 'a.ba@gcm.sn',          'city' => 'Thiès',       'country' => 'Sénégal'],
            ['code' => 'CLI-003', 'name' => 'Immobilière du Sahel',          'contact_person' => 'Cheikh Fall',       'phone' => '+221 76 100 50 50', 'city' => 'Saint-Louis',            'country' => 'Sénégal'],
            ['code' => 'CLI-004', 'name' => 'BCI Bâtiment',                 'contact_person' => 'Mouhamadou Lamine', 'phone' => '+221 33 821 30 30', 'city' => 'Dakar',                  'country' => 'Sénégal'],
        ])->map(fn($c) => Client::create(array_merge($c, ['tenant_id' => $tid])));

        // Ordres de fabrication
        $order1 = ProductionOrder::create(['tenant_id' => $tid, 'reference' => 'OF-20240401-0001', 'product_id' => $products[4]->id, 'quantity_planned' => 5000, 'quantity_produced' => 4200, 'status' => 'en_cours',  'start_date' => now()->subDays(10)->toDateString(), 'end_date' => now()->addDays(5)->toDateString(), 'user_id' => $manager->id]);
        ProductionTask::create(['production_order_id' => $order1->id, 'name' => 'Préparation béton',   'status' => 'fait',     'assigned_to' => $operator->id, 'completed_at' => now()->subDays(8)]);
        ProductionTask::create(['production_order_id' => $order1->id, 'name' => 'Moulage parpaings',   'status' => 'en_cours', 'assigned_to' => $operator->id, 'started_at' => now()->subDays(3)]);
        ProductionTask::create(['production_order_id' => $order1->id, 'name' => 'Séchage et contrôle', 'status' => 'todo',     'assigned_to' => $manager->id]);

        $order2 = ProductionOrder::create(['tenant_id' => $tid, 'reference' => 'OF-20240415-0002', 'product_id' => $products[5]->id, 'quantity_planned' => 300,  'quantity_produced' => 300, 'status' => 'termine', 'start_date' => now()->subDays(20)->toDateString(), 'end_date' => now()->subDays(5)->toDateString(), 'user_id' => $manager->id]);
        ProductionOrder::create(['tenant_id' => $tid, 'reference' => 'OF-20240418-0003', 'product_id' => $products[6]->id, 'quantity_planned' => 1000, 'quantity_produced' => 0,   'status' => 'brouillon', 'start_date' => now()->addDays(2)->toDateString(), 'user_id' => $admin->id]);

        // Mouvements de stock
        StockMovement::create(['tenant_id' => $tid, 'product_id' => $products[0]->id, 'type' => 'entree', 'quantity' => 2000, 'reference' => 'BL-2024-001',      'reason' => 'Réapprovisionnement',        'user_id' => $manager->id]);
        StockMovement::create(['tenant_id' => $tid, 'product_id' => $products[0]->id, 'type' => 'sortie', 'quantity' => 1200, 'reference' => 'OF-20240401-0001', 'reason' => 'Production parpaings',       'user_id' => $operator->id]);
        StockMovement::create(['tenant_id' => $tid, 'product_id' => $products[4]->id, 'type' => 'sortie', 'quantity' => 500,  'reference' => 'VT-2024-001',      'reason' => 'Vente client BTP Sénégal',   'user_id' => $manager->id]);

        // Contrôles qualité
        QualityControl::create(['tenant_id' => $tid, 'production_order_id' => $order1->id, 'product_id' => $products[4]->id, 'checked_by' => $manager->id, 'check_date' => now()->subDays(2)->toDateString(), 'quantity_checked' => 500, 'quantity_defective' => 12, 'status' => 'passe',  'notes' => 'Lot conforme, quelques défauts de surface mineurs']);
        QualityControl::create(['tenant_id' => $tid, 'production_order_id' => $order2->id, 'product_id' => $products[5]->id, 'checked_by' => $manager->id, 'check_date' => now()->subDays(6)->toDateString(), 'quantity_checked' => 300, 'quantity_defective' => 18, 'status' => 'echoue', 'notes' => 'Taux de défaut supérieur à 5%']);

        // Factures
        $inv1 = Invoice::create(['tenant_id' => $tid, 'reference' => 'FAC-20240410-0001', 'client_id' => $clients[0]->id, 'type' => 'facture', 'status' => 'paye',    'issue_date' => now()->subDays(15)->toDateString(), 'due_date' => now()->subDays(5)->toDateString(),  'tax_rate' => 18, 'subtotal' => 875000,  'tax_amount' => 157500, 'total' => 1032500, 'user_id' => $manager->id]);
        InvoiceItem::create(['invoice_id' => $inv1->id, 'product_id' => $products[4]->id, 'description' => 'Parpaing 15x20x40', 'quantity' => 2500, 'unit_price' => 350, 'total' => 875000]);

        $inv2 = Invoice::create(['tenant_id' => $tid, 'reference' => 'FAC-20240418-0002', 'client_id' => $clients[1]->id, 'type' => 'facture', 'status' => 'envoye',  'issue_date' => now()->subDays(5)->toDateString(),  'due_date' => now()->addDays(25)->toDateString(), 'tax_rate' => 18, 'subtotal' => 750000,  'tax_amount' => 135000, 'total' => 885000,  'user_id' => $manager->id]);
        InvoiceItem::create(['invoice_id' => $inv2->id, 'product_id' => $products[5]->id, 'description' => 'Dalle béton 50x50', 'quantity' => 300, 'unit_price' => 2500, 'total' => 750000]);

        $dev1 = Invoice::create(['tenant_id' => $tid, 'reference' => 'DEV-20240420-0001', 'client_id' => $clients[2]->id, 'type' => 'devis', 'status' => 'brouillon', 'issue_date' => now()->toDateString(), 'due_date' => now()->addDays(30)->toDateString(), 'tax_rate' => 18, 'subtotal' => 2100000, 'tax_amount' => 378000, 'total' => 2478000, 'user_id' => $admin->id]);
        InvoiceItem::create(['invoice_id' => $dev1->id, 'product_id' => $products[4]->id, 'description' => 'Parpaing 15x20x40',        'quantity' => 5000, 'unit_price' => 350,   'total' => 1750000]);
        InvoiceItem::create(['invoice_id' => $dev1->id, 'product_id' => $products[6]->id, 'description' => 'Hourdis 16',               'quantity' => 500,  'unit_price' => 600,   'total' => 300000]);
        InvoiceItem::create(['invoice_id' => $dev1->id, 'product_id' => null,             'description' => 'Livraison et déchargement', 'quantity' => 1,    'unit_price' => 50000, 'total' => 50000]);

        // Consommations énergie (30 derniers jours)
        for ($i = 0; $i < 30; $i++) {
            $date = now()->subDays($i)->toDateString();
            foreach ($machines->where('status', '!=', 'en_panne') as $machine) {
                if ($machine->power_kw > 0) {
                    $hours = rand(6, 10);
                    $kwh   = round($machine->power_kw * $hours * (0.85 + (rand(0, 30) / 100)), 2);
                    EnergyConsumption::create(['tenant_id' => $tid, 'machine_id' => $machine->id, 'date' => $date, 'kwh_consumed' => $kwh, 'cost_per_kwh' => 100, 'total_cost' => round($kwh * 100, 2), 'hours_used' => $hours]);
                }
            }
        }

        // Logistique (expéditions)
        Shipment::create(['tenant_id' => $tid, 'reference' => 'EXP-2024-001', 'type' => 'sortant', 'contact_name' => 'BTP Sénégal SARL', 'origin_destination' => 'Dakar — Chantier Almadies', 'status' => 'livre', 'product_id' => $products[4]->id, 'quantity' => 1000, 'carrier' => 'Transport Diallo', 'departure_date' => now()->subDays(10)->toDateString(), 'arrival_date' => now()->subDays(8)->toDateString(), 'stock_processed' => true]);
        Shipment::create(['tenant_id' => $tid, 'reference' => 'REC-2024-001', 'type' => 'entrant', 'contact_name' => 'Cimaf Sénégal',    'origin_destination' => 'Usine Cimaf — Dakar',        'status' => 'en_transit', 'product_id' => $products[0]->id, 'quantity' => 3000, 'carrier' => 'Senpost Fret', 'departure_date' => now()->subDays(2)->toDateString(), 'arrival_date' => now()->addDays(1)->toDateString()]);

        // RH (employés)
        Employee::create(['tenant_id' => $tid, 'name' => 'Aliou Badji',      'position' => 'Chef de production',    'department' => 'Production',  'phone' => '+221 77 200 01 01', 'hire_date' => '2019-04-15', 'salary' => 350000, 'status' => 'actif']);
        Employee::create(['tenant_id' => $tid, 'name' => 'Ndèye Diop',       'position' => 'Comptable',              'department' => 'Finance',     'phone' => '+221 77 200 01 02', 'email' => 'n.diop@demo.sn', 'hire_date' => '2020-09-01', 'salary' => 280000, 'status' => 'actif']);
        Employee::create(['tenant_id' => $tid, 'name' => 'Oumar Sarr',       'position' => 'Technicien maintenance', 'department' => 'Maintenance', 'phone' => '+221 76 200 01 03', 'hire_date' => '2021-01-10', 'salary' => 220000, 'status' => 'actif']);
        Employee::create(['tenant_id' => $tid, 'name' => 'Mariama Coulibaly', 'position' => 'Magasinière',            'department' => 'Logistique',  'phone' => '+221 77 200 01 04', 'hire_date' => '2022-03-07', 'salary' => 180000, 'status' => 'conge']);

        // Comptabilité
        AccountingTransaction::create(['tenant_id' => $tid, 'type' => 'recette', 'category' => 'Vente produits', 'amount' => 1032500, 'date' => now()->subDays(5)->toDateString(),  'description' => 'Paiement facture FAC-20240410-0001', 'payment_method' => 'Virement', 'reference' => 'FAC-20240410-0001']);
        AccountingTransaction::create(['tenant_id' => $tid, 'type' => 'depense', 'category' => 'Matières premières', 'amount' => 450000, 'date' => now()->subDays(8)->toDateString(),  'description' => 'Achat ciment Portland — 3000 kg',    'payment_method' => 'Chèque',   'reference' => 'ACH-2024-001']);
        AccountingTransaction::create(['tenant_id' => $tid, 'type' => 'depense', 'category' => 'Salaires',           'amount' => 1030000,'date' => now()->subDays(15)->toDateString(), 'description' => 'Salaires mars 2024',                 'payment_method' => 'Virement']);
        AccountingTransaction::create(['tenant_id' => $tid, 'type' => 'depense', 'category' => 'Énergie',            'amount' => 185000, 'date' => now()->subDays(20)->toDateString(), 'description' => 'Facture SENELEC — mars 2024',        'payment_method' => 'Virement', 'reference' => 'SEN-2024-03']);

        // Fournisseurs & Achats
        $sup1 = Supplier::create(['tenant_id' => $tid, 'name' => 'Cimaf Sénégal',      'contact_name' => 'Ibrahima Sy',    'phone' => '+221 33 832 00 00', 'email' => 'ventes@cimaf.sn',       'country' => 'Sénégal', 'address' => 'Zone industrielle Mbao']);
        $sup2 = Supplier::create(['tenant_id' => $tid, 'name' => 'Fer & Acier Dakar',  'contact_name' => 'Djibril Ndiaye', 'phone' => '+221 77 300 02 02', 'email' => 'd.ndiaye@feracier.sn',  'country' => 'Sénégal', 'address' => 'Rue 10, Parcelles Assainies']);
        PurchaseOrder::create(['tenant_id' => $tid, 'supplier_id' => $sup1->id, 'reference' => 'CMD-2024-001', 'status' => 'recu',     'order_date' => now()->subDays(12)->toDateString(), 'expected_date' => now()->subDays(5)->toDateString(), 'total_amount' => 450000, 'notes' => 'Livraison conforme']);
        PurchaseOrder::create(['tenant_id' => $tid, 'supplier_id' => $sup2->id, 'reference' => 'CMD-2024-002', 'status' => 'confirme', 'order_date' => now()->subDays(3)->toDateString(),  'expected_date' => now()->addDays(4)->toDateString(),  'total_amount' => 637500]);

        // CRM (prospects)
        Lead::create(['tenant_id' => $tid, 'name' => 'Samba Diallo',    'company' => 'Résidence Les Palmes',  'phone' => '+221 77 400 01 01', 'source' => 'Bouche à oreille', 'status' => 'qualifie',   'estimated_value' => 5000000, 'notes' => 'Projet de 50 villas — intéressé parpaings + dalles']);
        Lead::create(['tenant_id' => $tid, 'name' => 'Rokhaya Gueye',   'company' => 'SCI Horizon 2030',      'phone' => '+221 76 400 01 02', 'source' => 'Salon BTP',        'status' => 'proposition', 'estimated_value' => 2800000]);
        Lead::create(['tenant_id' => $tid, 'name' => 'Babacar Mbaye',   'company' => null,                    'phone' => '+221 77 400 01 03', 'source' => 'Site web',         'status' => 'nouveau',    'estimated_value' => 350000]);
        Lead::create(['tenant_id' => $tid, 'name' => 'Aïssatou Traoré', 'company' => 'Constructions Sahel',   'phone' => '+221 33 400 01 04', 'source' => 'Recommandation',   'status' => 'gagne',      'estimated_value' => 1200000, 'notes' => 'Commande signée']);

        // MRP
        MrpPlan::create(['tenant_id' => $tid, 'product_id' => $products[0]->id, 'quantity_needed' => 8000, 'quantity_available' => 5000, 'shortage' => 3000, 'planned_date' => now()->addDays(14)->toDateString(), 'status' => 'ouvert',   'notes' => 'Prévoir réapprovisionnement urgent']);
        MrpPlan::create(['tenant_id' => $tid, 'product_id' => $products[4]->id, 'quantity_needed' => 2000, 'quantity_available' => 3200, 'shortage' => 0,    'planned_date' => now()->addDays(7)->toDateString(),  'status' => 'confirme', 'notes' => 'Stock suffisant']);
    }
}
