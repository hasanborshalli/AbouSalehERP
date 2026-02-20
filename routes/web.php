<?php

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

    Route::get('/login',[PagesController::class,'loginPage'])->name('login')->middleware('guest');
    Route::post('/login', action: [AuthController::class, 'login'])
        ->name('login.submit')->middleware('guest');
        

    
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
   
   Route::patch('/purchases/{purchase}/void', [AccountingController::class, 'voidPurchase'])
    ->name('purchases.void');

Route::patch('/expenses/{expense}/void', [AccountingController::class, 'voidExpense'])
    ->name('expenses.void');
        });
});
});