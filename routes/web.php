<?php

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\DepartmentChatController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MaterialRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RenewalController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\BomController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientPaymentController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnergyController;
use App\Http\Controllers\HrController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LogisticsController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MrpController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\QualityController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\RecruitmentController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\TenantSettingsController;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Super Admin panel
Route::middleware('auth')->group(function () {
    Route::get('/superadmin', [SuperAdminController::class, 'index'])->name('superadmin.index');
    Route::get('/superadmin/create', [SuperAdminController::class, 'create'])->name('superadmin.create');
    Route::post('/superadmin', [SuperAdminController::class, 'store'])->name('superadmin.store');
    Route::post('/superadmin/stop-impersonate', [SuperAdminController::class, 'stopImpersonate'])->name('superadmin.stop-impersonate');
    Route::get('/superadmin/renewals', [SuperAdminController::class, 'renewals'])->name('superadmin.renewals');
    Route::post('/superadmin/renewals/{renewal}/approve', [SuperAdminController::class, 'approveRenewal'])->name('superadmin.renewals.approve');
    Route::post('/superadmin/renewals/{renewal}/reject', [SuperAdminController::class, 'rejectRenewal'])->name('superadmin.renewals.reject');
    Route::get('/superadmin/{tenant}', [SuperAdminController::class, 'show'])->name('superadmin.show');
    Route::patch('/superadmin/{tenant}', [SuperAdminController::class, 'update'])->name('superadmin.update');
    Route::delete('/superadmin/{tenant}', [SuperAdminController::class, 'destroy'])->name('superadmin.destroy');
    Route::post('/superadmin/{tenant}/impersonate', [SuperAdminController::class, 'impersonate'])->name('superadmin.impersonate');
    Route::post('/superadmin/{tenant}/extend', [SuperAdminController::class, 'extend'])->name('superadmin.extend');
    Route::post('/superadmin/{tenant}/provision', [SuperAdminController::class, 'provision'])->name('superadmin.provision');
    Route::post('/superadmin/{tenant}/suspend', [SuperAdminController::class, 'suspend'])->name('superadmin.suspend');
    Route::post('/superadmin/{tenant}/activate', [SuperAdminController::class, 'activate'])->name('superadmin.activate');
    Route::post('/superadmin/{tenant}/modules/enable-all', [SuperAdminController::class, 'enableAllModules'])->name('superadmin.modules.enable-all');
    Route::post('/superadmin/{tenant}/modules/disable-all', [SuperAdminController::class, 'disableAllModules'])->name('superadmin.modules.disable-all');
    Route::post('/superadmin/{tenant}/modules/{module}/toggle', [SuperAdminController::class, 'toggleModule'])->name('superadmin.modules.toggle');
});

// Landing page publique
Route::get('/', fn() => view('welcome'))->name('welcome');

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Inscription (étape 1 — sans tenant)
Route::get('/register', [OnboardingController::class, 'showStep1'])->name('register')->middleware('guest');
Route::post('/register', [OnboardingController::class, 'step1'])->middleware('guest');

// Onboarding (auth requis, sans vérification d'onboarding)
Route::middleware('auth')->group(function () {
    Route::get('/onboarding/company', [OnboardingController::class, 'showStep2'])->name('onboarding.step2');
    Route::post('/onboarding/company', [OnboardingController::class, 'step2']);
    Route::get('/onboarding/modules', [OnboardingController::class, 'showStep3'])->name('onboarding.step3');
    Route::post('/onboarding/modules', [OnboardingController::class, 'step3']);
    Route::get('/onboarding/theme', [OnboardingController::class, 'showStep4'])->name('onboarding.step4');
    Route::post('/onboarding/theme', [OnboardingController::class, 'step4']);
    Route::get('/onboarding/success', [OnboardingController::class, 'success'])->name('onboarding.success');
});

// Abonnement expiré + renouvellement (sans tenant.active ni onboarding)
Route::middleware('auth')->group(function () {
    Route::get('/subscription/expired', fn() => view('subscription.expired'))->name('subscription.expired');
    Route::get('/subscription/renew', [RenewalController::class, 'show'])->name('subscription.renew');
    Route::post('/subscription/renew', [RenewalController::class, 'store'])->name('subscription.renew.store');
});

// Application principale (auth + onboarding complet + abonnement actif)
Route::middleware(['auth', 'onboarding.complete', 'tenant.active', 'tenant.db'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil utilisateur (accessible à tous les rôles)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Paramètres entreprise
    Route::get('/settings/company', [TenantSettingsController::class, 'show'])->name('settings.company');
    Route::put('/settings/company', [TenantSettingsController::class, 'update']);

    // Stock
    Route::resource('products', ProductController::class)->except(['show']);
    Route::get('/products/{product}/bom', [BomController::class, 'show'])->name('products.bom.show');
    Route::post('/products/{product}/bom', [BomController::class, 'store'])->name('products.bom.store');
    Route::delete('/products/{product}/bom/{bom}', [BomController::class, 'destroy'])->name('products.bom.destroy');
    Route::get('/stock', [StockMovementController::class, 'index'])->name('stock.index');
    Route::get('/stock/create', [StockMovementController::class, 'create'])->name('stock.create');
    Route::post('/stock', [StockMovementController::class, 'store'])->name('stock.store');
    Route::get('/stock/{stock}/edit', [StockMovementController::class, 'edit'])->name('stock.edit');
    Route::put('/stock/{stock}', [StockMovementController::class, 'update'])->name('stock.update');
    Route::delete('/stock/{stock}', [StockMovementController::class, 'destroy'])->name('stock.destroy');

    // Production
    Route::resource('production', ProductionOrderController::class);
    Route::get('/production-costs', [ProductionOrderController::class, 'costs'])->name('production.costs');
    Route::post('/production/{production}/tasks', [ProductionOrderController::class, 'storeTask'])->name('production.tasks.store');
    Route::patch('/production/tasks/{task}', [ProductionOrderController::class, 'updateTask'])->name('production.tasks.update');

    // Machines & Maintenance
    Route::resource('machines', MachineController::class)->except(['show']);
    Route::resource('maintenance', MaintenanceController::class)->except(['show']);

    // Clients & Factures
    Route::resource('clients', ClientController::class)->except(['show']);
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::resource('invoices', InvoiceController::class)->except(['edit', 'update']);
    Route::patch('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status');

    // Qualité
    Route::get('/quality', [QualityController::class, 'index'])->name('quality.index');
    Route::get('/quality/create', [QualityController::class, 'create'])->name('quality.create');
    Route::post('/quality', [QualityController::class, 'store'])->name('quality.store');
    Route::get('/quality/{quality}', [QualityController::class, 'show'])->name('quality.show');
    Route::get('/quality/{quality}/edit', [QualityController::class, 'edit'])->name('quality.edit');
    Route::put('/quality/{quality}', [QualityController::class, 'update'])->name('quality.update');
    Route::delete('/quality/{quality}', [QualityController::class, 'destroy'])->name('quality.destroy');

    // Énergie
    Route::get('/energy', [EnergyController::class, 'index'])->name('energy.index');
    Route::get('/energy/create', [EnergyController::class, 'create'])->name('energy.create');
    Route::post('/energy', [EnergyController::class, 'store'])->name('energy.store');
    Route::get('/energy/{energy}/edit', [EnergyController::class, 'edit'])->name('energy.edit');
    Route::put('/energy/{energy}', [EnergyController::class, 'update'])->name('energy.update');
    Route::delete('/energy/{energy}', [EnergyController::class, 'destroy'])->name('energy.destroy');

    // MRP
    Route::get('/mrp', [MrpController::class, 'index'])->name('mrp.index');
    Route::get('/mrp/create', [MrpController::class, 'create'])->name('mrp.create');
    Route::post('/mrp', [MrpController::class, 'store'])->name('mrp.store');
    Route::post('/mrp/recalculate', [MrpController::class, 'recalculate'])->name('mrp.recalculate');
    Route::get('/mrp/{mrp}/edit', [MrpController::class, 'edit'])->name('mrp.edit');
    Route::put('/mrp/{mrp}', [MrpController::class, 'update'])->name('mrp.update');
    Route::delete('/mrp/{mrp}', [MrpController::class, 'destroy'])->name('mrp.destroy');

    // Logistique (expéditions)
    Route::get('/logistics', [LogisticsController::class, 'index'])->name('logistics.index');
    Route::get('/logistics/stock', [LogisticsController::class, 'stock'])->name('logistics.stock');
    Route::get('/logistics/create', [LogisticsController::class, 'create'])->name('logistics.create');
    Route::post('/logistics', [LogisticsController::class, 'store'])->name('logistics.store');
    Route::get('/logistics/{logistic}/edit', [LogisticsController::class, 'edit'])->name('logistics.edit');
    Route::put('/logistics/{logistic}', [LogisticsController::class, 'update'])->name('logistics.update');
    Route::patch('/logistics/{logistic}/status', [LogisticsController::class, 'updateStatus'])->name('logistics.status');
    Route::get('/logistics/{logistic}/document', [LogisticsController::class, 'document'])->name('logistics.document');
    Route::delete('/logistics/{logistic}', [LogisticsController::class, 'destroy'])->name('logistics.destroy');

    // Transporteurs
    Route::get('/carriers', [CarrierController::class, 'index'])->name('carriers.index');
    Route::post('/carriers', [CarrierController::class, 'store'])->name('carriers.store');
    Route::patch('/carriers/{carrier}', [CarrierController::class, 'update'])->name('carriers.update');
    Route::delete('/carriers/{carrier}', [CarrierController::class, 'destroy'])->name('carriers.destroy');

    // Retours marchandises
    Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::get('/returns/create', [ReturnController::class, 'create'])->name('returns.create');
    Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');
    Route::patch('/returns/{return}/process', [ReturnController::class, 'processReturn'])->name('returns.process');
    Route::delete('/returns/{return}', [ReturnController::class, 'destroy'])->name('returns.destroy');

    // RH — Employés
    Route::get('/hr', [HrController::class, 'index'])->name('hr.index');
    Route::get('/hr/create', [HrController::class, 'create'])->name('hr.create');
    Route::post('/hr', [HrController::class, 'store'])->name('hr.store');
    Route::get('/hr/{hr}/edit', [HrController::class, 'edit'])->name('hr.edit');
    Route::put('/hr/{hr}', [HrController::class, 'update'])->name('hr.update');
    Route::delete('/hr/{hr}', [HrController::class, 'destroy'])->name('hr.destroy');

    // RH — Congés
    Route::get('/hr/leaves', [LeaveController::class, 'index'])->name('hr.leaves.index');
    Route::get('/hr/leaves/create', [LeaveController::class, 'create'])->name('hr.leaves.create');
    Route::post('/hr/leaves', [LeaveController::class, 'store'])->name('hr.leaves.store');
    Route::patch('/hr/leaves/{leave}/status', [LeaveController::class, 'updateStatus'])->name('hr.leaves.status');
    Route::delete('/hr/leaves/{leave}', [LeaveController::class, 'destroy'])->name('hr.leaves.destroy');

    // RH — Paie
    Route::get('/hr/salaries', [SalaryController::class, 'index'])->name('hr.salaries.index');
    Route::get('/hr/salaries/create', [SalaryController::class, 'create'])->name('hr.salaries.create');
    Route::post('/hr/salaries', [SalaryController::class, 'store'])->name('hr.salaries.store');
    Route::post('/hr/salaries/generate', [SalaryController::class, 'generateBulk'])->name('hr.salaries.generate');
    Route::patch('/hr/salaries/{salary}/pay', [SalaryController::class, 'markPaid'])->name('hr.salaries.pay');
    Route::delete('/hr/salaries/{salary}', [SalaryController::class, 'destroy'])->name('hr.salaries.destroy');

    // RH — Pointage
    Route::get('/hr/timelogs', [TimeLogController::class, 'index'])->name('hr.timelogs.index');
    Route::post('/hr/timelogs', [TimeLogController::class, 'store'])->name('hr.timelogs.store');
    Route::post('/hr/timelogs/bulk', [TimeLogController::class, 'bulkStore'])->name('hr.timelogs.bulk');

    // RH — Recrutement
    Route::get('/hr/recruitment', [RecruitmentController::class, 'index'])->name('hr.recruitment.index');
    Route::get('/hr/recruitment/create', [RecruitmentController::class, 'create'])->name('hr.recruitment.create');
    Route::post('/hr/recruitment', [RecruitmentController::class, 'store'])->name('hr.recruitment.store');
    Route::get('/hr/recruitment/{posting}', [RecruitmentController::class, 'show'])->name('hr.recruitment.show');
    Route::patch('/hr/recruitment/{posting}/status', [RecruitmentController::class, 'updateStatus'])->name('hr.recruitment.status');
    Route::post('/hr/recruitment/{posting}/apply', [RecruitmentController::class, 'storeApplication'])->name('hr.recruitment.apply');
    Route::patch('/hr/recruitment/applications/{application}', [RecruitmentController::class, 'updateApplication'])->name('hr.recruitment.application.update');
    Route::delete('/hr/recruitment/{posting}', [RecruitmentController::class, 'destroy'])->name('hr.recruitment.destroy');

    // RH — Évaluations
    Route::get('/hr/performance', [PerformanceController::class, 'index'])->name('hr.performance.index');
    Route::get('/hr/performance/create', [PerformanceController::class, 'create'])->name('hr.performance.create');
    Route::post('/hr/performance', [PerformanceController::class, 'store'])->name('hr.performance.store');
    Route::delete('/hr/performance/{performance}', [PerformanceController::class, 'destroy'])->name('hr.performance.destroy');

    // Comptabilité
    Route::get('/accounting', [AccountingController::class, 'index'])->name('accounting.index');
    Route::get('/accounting/reports', [AccountingController::class, 'reports'])->name('accounting.reports');
    Route::get('/accounting/create', [AccountingController::class, 'create'])->name('accounting.create');
    Route::post('/accounting', [AccountingController::class, 'store'])->name('accounting.store');
    Route::get('/accounting/{accounting}/edit', [AccountingController::class, 'edit'])->name('accounting.edit');
    Route::put('/accounting/{accounting}', [AccountingController::class, 'update'])->name('accounting.update');
    Route::delete('/accounting/{accounting}', [AccountingController::class, 'destroy'])->name('accounting.destroy');

    // Achats
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::patch('/purchases/{purchase}/status', [PurchaseController::class, 'updateStatus'])->name('purchases.status');
    Route::post('/purchases/suppliers', [PurchaseController::class, 'storeSupplier'])->name('purchases.suppliers.store');
    Route::delete('/purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');

    // Paiements fournisseurs
    Route::get('/supplier-payments', [SupplierPaymentController::class, 'index'])->name('supplier-payments.index');
    Route::post('/supplier-payments', [SupplierPaymentController::class, 'store'])->name('supplier-payments.store');
    Route::delete('/supplier-payments/{payment}', [SupplierPaymentController::class, 'destroy'])->name('supplier-payments.destroy');

    // PDF downloads
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::get('/quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf');
    Route::get('/hr/salaries/{salary}/pdf', [SalaryController::class, 'pdf'])->name('hr.salaries.pdf');

    // Commercial — Devis
    Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
    Route::post('/quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    Route::patch('/quotes/{quote}/status', [QuoteController::class, 'updateStatus'])->name('quotes.status');
    Route::post('/quotes/{quote}/convert', [QuoteController::class, 'convertToOrder'])->name('quotes.convert');
    Route::delete('/quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');

    // Commercial — Commandes clients
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [CustomerOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [CustomerOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [CustomerOrderController::class, 'updateStatus'])->name('orders.status');
    Route::delete('/orders/{order}', [CustomerOrderController::class, 'destroy'])->name('orders.destroy');

    // Commercial — Paiements clients
    Route::get('/client-payments', [ClientPaymentController::class, 'index'])->name('client-payments.index');
    Route::post('/client-payments', [ClientPaymentController::class, 'store'])->name('client-payments.store');
    Route::delete('/client-payments/{payment}', [ClientPaymentController::class, 'destroy'])->name('client-payments.destroy');

    // CRM
    Route::get('/crm', [CrmController::class, 'index'])->name('crm.index');
    Route::get('/crm/create', [CrmController::class, 'create'])->name('crm.create');
    Route::post('/crm', [CrmController::class, 'store'])->name('crm.store');
    Route::get('/crm/{crm}/edit', [CrmController::class, 'edit'])->name('crm.edit');
    Route::put('/crm/{crm}', [CrmController::class, 'update'])->name('crm.update');
    Route::delete('/crm/{crm}', [CrmController::class, 'destroy'])->name('crm.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.count');
    Route::get('/notifications/panel', [NotificationController::class, 'panel'])->name('notifications.panel');

    // Demandes matières (Production → Logistique)
    Route::get('/material-requests', [MaterialRequestController::class, 'index'])->name('material-requests.index');
    Route::get('/material-requests/create', [MaterialRequestController::class, 'create'])->name('material-requests.create');
    Route::post('/material-requests', [MaterialRequestController::class, 'store'])->name('material-requests.store');
    Route::get('/material-requests/{materialRequest}', [MaterialRequestController::class, 'show'])->name('material-requests.show');
    Route::get('/material-requests-logistics', [MaterialRequestController::class, 'logistics'])->name('material-requests.logistics');
    Route::post('/material-requests/{materialRequest}/respond', [MaterialRequestController::class, 'respond'])->name('material-requests.respond');
    Route::delete('/material-requests/{materialRequest}', [MaterialRequestController::class, 'destroy'])->name('material-requests.destroy');

    // Chat inter-départements
    Route::get('/chat', [DepartmentChatController::class, 'index'])->name('chat.index');
    Route::post('/chat', [DepartmentChatController::class, 'store'])->name('chat.store');
    Route::post('/chat/voice', [DepartmentChatController::class, 'storeVoice'])->name('chat.voice');
    Route::get('/chat/messages', [DepartmentChatController::class, 'messages'])->name('chat.messages');

    // Meetings
    Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::get('/meetings/create', [MeetingController::class, 'create'])->name('meetings.create');
    Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::get('/meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
    Route::patch('/meetings/{meeting}/minutes', [MeetingController::class, 'minutes'])->name('meetings.minutes');
    Route::delete('/meetings/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');

    // Administration
    Route::resource('users', UserController::class)->except(['show']);
});
