<?php

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientNotificationController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\ContractProgressController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\AdditionalCostController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\WorkersController;
use App\Http\Controllers\WorkerPortalController;
use Illuminate\Support\Facades\Route;

    Route::get('/login',[PagesController::class,'loginPage'])->name('login')->middleware('guest');
    Route::post('/login', action: [AuthController::class, 'login'])
        ->name('login.submit')->middleware('guest');
        
    Route::get('/',[PagesController::class,'loginPage'])->middleware('guest');

    
Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])
    ->name('logout');
    Route::get('/dashboard',[PagesController::class,'dashboardPage'])
    ->name('dashboard')->middleware('role:owner,admin');

    //Inventory Routes
    Route::middleware('role:owner,admin')->group(function () {
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [PagesController::class, 'inventoryPage'])
                ->name('overview');
            Route::get('/stock-control', [PagesController::class, 'stockControlPage'])
                ->name('stock-control');
            Route::get('/add-item', [PagesController::class, 'addItemPage'])
                ->name('add-item');
            Route::post('/items', [InventoryController::class, 'store'])
                ->name('create-item');
            Route::get('/edit-item/{inventoryItem}', [PagesController::class, 'editItemPage'])
                ->name('edit-item');
            Route::post('/editItem/{inventoryItem}', [InventoryController::class, 'update'])
                ->name('update-item');
            Route::get('/stock-info', [PagesController::class, 'stockInfoPage'])
                ->name('stock-info');
            Route::post('/delete-item/{inventoryItem}', [InventoryController::class, 'destroy'])
                ->name('delete-item');
        });
    });

    //Clients Routes
    Route::middleware('role:owner,admin')->group(function () {
        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/', [PagesController::class, 'clientPage'])
            ->name('overview');
            Route::get('/add-client', [PagesController::class, 'addClientPage'])
            ->name('add-client');
            Route::post('/create-client', [ClientsController::class, 'createClient'])
            ->name('createClient');
            Route::get('/edit-client/{user}', [PagesController::class, 'editClientPage'])
            ->name('edit-client');
            Route::put('/editClient/{user}', [ClientsController::class, 'update'])
            ->name('update');
            Route::get('/existing-clients', [PagesController::class, 'existingClientsPage'])
            ->name('existing-clients');
            Route::delete('/delete/{user}', [ClientsController::class, 'destroy'])->name('destroy');

        });
    });
    //Invoices Routes
    Route::middleware('role:owner,admin')->group(function () {
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [PagesController::class, 'invoicesPage'])
            ->name('overview');
            Route::patch('/{invoice}/dates', [InvoicesController::class, 'updateDates']);
            Route::patch('/{invoice}/mark-paid', [InvoicesController::class, 'markPaid']);
        
        });
    });
    Route::middleware('role:owner,admin')->group(function () {
        Route::prefix('apartments')->name('apartments.')->group(function(){
            Route::get('/',[PagesController::class,'apartmentsPage'])
            ->name('overview'); 
            Route::get('/existing-projects', [PagesController::class, 'existingProjectsPage'])
            ->name('existing-projects');
            Route::get('/create-project',[PagesController::class,'createProjectPage'])
            ->name('create-project');
            Route::post('/createProject',[ProjectController::class,'createProject'])
            ->name('createProject');
            Route::get('/edit-project/{project}',[PagesController::class,'editProjectPage'])
            ->name('edit-project');
            Route::post('/editProject/{project}',[ProjectController::class,'editProject'])
            ->name('editProject');
            Route::delete('/delete-project/{project}',[ProjectController::class,'deleteProject'])
            ->name('delete-project');
            Route::get('/project/{project}', [PagesController::class, 'projectPage'])
                ->name('project');
        });
    });

    //Settings Routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [PagesController::class, 'settingsPage'])
        ->name('overview');
         Route::get('/export', [ExportController::class, 'exportZip'])
            ->name('export')->middleware('role:owner');
    
    });

    Route::prefix('employees')->name('employees.')->group(function () {
        Route::post('/add', [EmployeesController::class, 'addEmployee'])
        ->name('add')->middleware('role:owner');
        Route::delete('/delete/{user}', [EmployeesController::class, 'deleteEmployee'])
        ->name('delete')->middleware('role:owner');
        Route::post('/edit', [EmployeesController::class, 'editEmployee'])
        ->name('edit')->middleware('role:owner');
        Route::post('/editPassword', [EmployeesController::class, 'editPassword'])
        ->name('editPassword')->middleware('role:owner,admin');
        Route::post('/editAvatar', [EmployeesController::class, 'editAvatar'])
        ->name('editAvatar');
    });  
// Accounting Routes
Route::middleware('role:owner,admin')->group(function () {
    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::get('/', [PagesController::class, 'accountingPage'])->name('overview');

        Route::get('/purchases', [PagesController::class, 'accountingPurchasesPage'])->name('purchases');
        Route::post('/purchases', [AccountingController::class, 'storePurchase'])->name('purchases.store');

        Route::get('/expenses', [PagesController::class, 'accountingExpensesPage'])->name('expenses');
        Route::post('/expenses', [AccountingController::class, 'storeExpense'])->name('expenses.store');

        Route::get('/ledger', [PagesController::class, 'ledgerDetailPage'])->name('ledger');
        Route::get('/ledger/export/excel', [App\Http\Controllers\LedgerExportController::class, 'exportExcel'])->name('ledger.export.excel');
        Route::get('/ledger/export/pdf',   [App\Http\Controllers\LedgerExportController::class, 'exportPdf'])->name('ledger.export.pdf');
   
   Route::patch('/purchases/{purchase}/void', [AccountingController::class, 'voidPurchase'])
    ->name('purchases.void');

   Route::post('/purchases/receipt/void', [AccountingController::class, 'voidReceipt'])
    ->name('purchases.receipt.void');

Route::patch('/expenses/{expense}/void', [AccountingController::class, 'voidExpense'])
    ->name('expenses.void');
        });
});
// ---------------------------
// Client Portal Routes
// ---------------------------
Route::middleware(['role:client'])->prefix('client')->name('client.')->group(function () {
    // Contracts (cards)
    Route::get('/contracts', [ClientPortalController::class, 'contractsHome'])->name('contracts');
    Route::get('/contracts/overview', [ClientPortalController::class, 'contractsOverview'])->name('contracts.overview');
    Route::get('/contracts/manager', [ClientPortalController::class, 'contractsManager'])->name('contracts.manager');
    Route::get('/contracts/documents', [ClientPortalController::class, 'contractsDocuments'])->name('contracts.documents');
    Route::get('/contracts/progress', [ClientPortalController::class, 'contractsProgress'])->name('contracts.progress');
    Route::get('/contracts/{contract}/pdf', [ClientPortalController::class, 'viewContractPdf'])->name('contracts.pdf.view');
    Route::get('/contracts/{contract}/pdf/download', [ClientPortalController::class, 'downloadContractPdf'])->name('contracts.pdf.download');
    
    // Invoices (cards)
    Route::get('/invoices', [ClientPortalController::class, 'invoicesHome'])->name('invoices');
    Route::get('/invoices/list', [ClientPortalController::class, 'invoicesList'])->name('invoices.list');
    Route::get('/invoices/receipts', [ClientPortalController::class, 'invoicesReceipts'])->name('invoices.receipts');
    Route::get('/invoices/download-center', [ClientPortalController::class, 'invoicesDownloadCenter'])->name('invoices.download-center');
    Route::post('/invoices/download-center/zip', [ClientPortalController::class, 'downloadUnpaidInvoicesZip'])->name('invoices.unpaid.zip');
    Route::get('/invoices/payments', [ClientPortalController::class, 'invoicesPayments'])->name('invoices.payments');
    Route::get('/invoices/{invoice}/pdf', [ClientPortalController::class, 'viewInvoicePdf'])->name('invoices.pdf.view');
    Route::get('/invoices/{invoice}/pdf/download', [ClientPortalController::class, 'downloadInvoicePdf'])->name('invoices.pdf.download');
    Route::get('/invoices/{invoice}/receipt/download', [ClientPortalController::class, 'downloadInvoiceReceipt'])->name('invoices.receipt.download');

    // Settings
    Route::get('/settings', [ClientPortalController::class, 'settingsHome'])->name('settings');
    Route::post('/settings/profile', [ClientPortalController::class, 'updateProfile'])->name('settings.profile.update');
    Route::post('/settings/password', [ClientPortalController::class, 'updatePassword'])->name('settings.password.update');
    Route::post('/settings/avatar', [ClientPortalController::class, 'updateAvatar'])->name('settings.avatar.update');

    // Notifications
    Route::get('/notifications', [ClientNotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{notification}/read', [ClientNotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [ClientNotificationController::class, 'markAllRead'])->name('notifications.readAll');
    });

Route::middleware([ 'role:owner,admin'])->group(function () {
    Route::get('/contracts/{contract}/progress', [ContractProgressController::class, 'index'])->name('contracts.progress.editor');
    Route::post('/contracts/{contract}/progress', [ContractProgressController::class, 'store'])->name('contracts.progress.store');
    Route::post('/contracts/{contract}/progress/{item}', [ContractProgressController::class, 'update'])->name('contracts.progress.update');
    Route::delete('/contracts/{contract}/progress/{item}', [ContractProgressController::class, 'destroy'])->name('contracts.progress.destroy');
});

// ── Reports ──────────────────────────────────────────────────
Route::middleware('role:owner,admin')->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportsController::class, 'index'])->name('index');
    Route::get('/project',            [ReportsController::class, 'byProject'])->name('project');
    Route::get('/project/{project}',  [ReportsController::class, 'byProject'])->name('project.show');
    Route::get('/apartment',          [ReportsController::class, 'byApartment'])->name('apartment');
    Route::get('/apartment/{apartment}', [ReportsController::class, 'byApartment'])->name('apartment.show');

    // New report pages
    Route::get('/pl',                  [ReportsController::class, 'profitLoss'])->name('pl');
    Route::get('/sales-pipeline',      [ReportsController::class, 'salesPipeline'])->name('sales-pipeline');
    Route::get('/outstanding-invoices',[ReportsController::class, 'outstandingInvoices'])->name('outstanding-invoices');
    Route::get('/worker-payments',     [ReportsController::class, 'workerPayments'])->name('worker-payments');
    Route::get('/operating-expenses',  [ReportsController::class, 'operatingExpenses'])->name('operating-expenses');
    Route::get('/inventory',           [ReportsController::class, 'inventoryReport'])->name('inventory');

    // Exports
    Route::get('/export/{type}/excel', [App\Http\Controllers\ReportExportController::class, 'excel'])->name('export.excel');
    Route::get('/export/{type}/pdf',   [App\Http\Controllers\ReportExportController::class, 'pdf'])->name('export.pdf');
});

// ── Additional Costs & Apartment Materials ────────────────────
Route::middleware('role:owner,admin')->group(function () {
    // Project additional costs
    Route::post('/projects/{project}/costs', [AdditionalCostController::class, 'storeProjectCost'])->name('projects.costs.store');
    Route::patch('/projects/{project}/costs/{cost}/settle', [AdditionalCostController::class, 'settleProjectCost'])->name('projects.costs.settle');
    Route::delete('/projects/{project}/costs/{cost}', [AdditionalCostController::class, 'destroyProjectCost'])->name('projects.costs.destroy');

    // Apartment additional costs
    Route::post('/apartments/{apartment}/costs', [AdditionalCostController::class, 'storeApartmentCost'])->name('apartments.costs.store');
    Route::patch('/apartments/{apartment}/costs/{cost}/settle', [AdditionalCostController::class, 'settleApartmentCost'])->name('apartments.costs.settle');
    Route::delete('/apartments/{apartment}/costs/{cost}', [AdditionalCostController::class, 'destroyApartmentCost'])->name('apartments.costs.destroy');

    // Apartment materials (post-creation)
    Route::post('/apartments/{apartment}/materials', [AdditionalCostController::class, 'storeApartmentMaterial'])->name('apartments.materials.store');
    Route::delete('/apartments/{apartment}/materials/{material}', [AdditionalCostController::class, 'destroyApartmentMaterial'])->name('apartments.materials.destroy');

    // Project materials (post-creation)
    Route::post('/projects/{project}/materials', [AdditionalCostController::class, 'storeProjectMaterial'])->name('projects.materials.store');
    Route::delete('/projects/{project}/materials/{material}', [AdditionalCostController::class, 'destroyProjectMaterial'])->name('projects.materials.destroy');
});

// ── Workers (admin/owner) ─────────────────────────────────────
Route::middleware(['auth', 'role:owner,admin'])->prefix('workers')->name('workers.')->group(function () {
    Route::get('/',                                      [WorkersController::class, 'index'])->name('index');
    Route::get('/create',                                [WorkersController::class, 'createPage'])->name('create');
    Route::post('/',                                     [WorkersController::class, 'store'])->name('store');
    Route::get('/{worker}',                              [WorkersController::class, 'show'])->name('show');
    Route::post('/{worker}/contracts',                   [WorkersController::class, 'addContract'])->name('addContract');
    Route::patch('/payments/{payment}/mark-paid',        [WorkersController::class, 'markPaid'])->name('payments.markPaid');
    Route::get('/contracts/{contract}/pdf',              [WorkersController::class, 'contractPdf'])->name('contract.pdf');
    Route::get('/contracts/{contract}/pdf/download',     [WorkersController::class, 'contractPdfDownload'])->name('contract.pdf.download');
    Route::get('/payments/{payment}/receipt/download',   [WorkersController::class, 'paymentReceiptDownload'])->name('payments.receipt');
});

// ── Worker Portal ─────────────────────────────────────────────
Route::middleware(['auth', 'role:worker'])->prefix('worker')->name('worker.')->group(function () {
    Route::get('/home',                                         [WorkerPortalController::class, 'home'])->name('home');
    Route::get('/contracts',                                    [WorkerPortalController::class, 'contractsList'])->name('contracts');
    Route::get('/contracts/{contract}/pdf',                     [WorkerPortalController::class, 'viewContractPdf'])->name('contracts.pdf.view');
    Route::get('/contracts/{contract}/pdf/download',            [WorkerPortalController::class, 'downloadContractPdf'])->name('contracts.pdf.download');
    Route::get('/payments',                                     [WorkerPortalController::class, 'paymentsList'])->name('payments');
    Route::get('/payments/{payment}/receipt',                   [WorkerPortalController::class, 'downloadReceipt'])->name('payments.receipt');
    Route::get('/settings',                                     [WorkerPortalController::class, 'settings'])->name('settings');
    Route::post('/settings/profile',                            [WorkerPortalController::class, 'updateProfile'])->name('settings.profile.update');
    Route::post('/settings/password',                           [WorkerPortalController::class, 'updatePassword'])->name('settings.password.update');
    Route::post('/settings/avatar',                             [WorkerPortalController::class, 'updateAvatar'])->name('settings.avatar.update');
    // Notification mark-read (used by navbar bell)
    Route::post('/notifications/{notification}/read',           [WorkerPortalController::class, 'markNotificationRead'])->name('notifications.read');
});
});