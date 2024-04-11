<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GlobalController;
use App\Http\Controllers\MasterData\Business\BankController;
use App\Http\Controllers\MasterData\Business\BranchController;
use App\Http\Controllers\MasterData\Business\CityController;
use App\Http\Controllers\MasterData\Business\ProvinceController;
use App\Http\Controllers\MasterData\Business\SubdistrictController;
use App\Http\Controllers\MasterData\Business\WarehouseController;
use App\Http\Controllers\MasterData\Product\CategoryController;
use App\Http\Controllers\MasterData\Product\ItemController;
use App\Http\Controllers\MasterData\Product\TypeController;
use App\Http\Controllers\MasterData\Product\UnitController;
use App\Http\Controllers\MasterData\Relation\CustomerController;
use App\Http\Controllers\MasterData\Relation\DivisionController;
use App\Http\Controllers\MasterData\Relation\EmployeeController;
use App\Http\Controllers\MasterData\Relation\PositionController;
use App\Http\Controllers\MasterData\Relation\SupplierController;
use App\Http\Controllers\Report\StockBalanceController as ReportStockBalanceController;
use App\Http\Controllers\Report\StockReportController;
use App\Http\Controllers\Setting\GroupUserController;
use App\Http\Controllers\Setting\UserController;
use App\Http\Controllers\Transaction\PurchaseController;
use App\Http\Controllers\Transaction\SaleController;
use App\Http\Controllers\Transaction\Warehouse\IncomingItemController;
use App\Http\Controllers\Transaction\Warehouse\OutgoingItemController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group(['middleware' => ['web']], function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

        Route::controller(GlobalController::class)->group(function () {
            Route::get('autocomplete', 'autoComplete')->name('autocomplete');
            Route::get('ajax-data-table/{action}', 'getAjaxDataTable')->name('ajax-data-table');
            Route::get('get-browse-data', 'getBrowseData')->name('get-browse-data');
            Route::post('getData', 'requestGetData')->name('requestGetData');
        });

        // Route::middleware('checkUserGroupPermission')->group(function () {
            Route::controller(UserController::class)->group(function(){
                Route::get('/profile', 'seeProfile')->name('seeprofile');
                Route::post('/profile/send', 'updateProfileSend');
            });

            Route::prefix('/setting/')->group(function () {
                Route::resource('group-user', GroupUserController::class);
                Route::resource('user', UserController::class);
            });

            Route::prefix('/master-data/')->group(function () {
                Route::prefix('/business/')->group(function () {
                    Route::resource('province', ProvinceController::class);
                    Route::resource('city', CityController::class);
                    Route::resource('subdistrict', SubdistrictController::class);
                    Route::resource('branch', BranchController::class);
                    Route::resource('warehouse', WarehouseController::class);
                    Route::resource('bank', BankController::class);
                });

                Route::prefix('/product/')->group(function () {
                    Route::resource('category', CategoryController::class);
                    Route::resource('item', ItemController::class);
                    Route::resource('unit', UnitController::class);
                    Route::resource('type', TypeController::class);
                });

                Route::prefix('/relation/')->group(function () {
                    Route::resource('customer', CustomerController::class);
                    Route::resource('supplier', SupplierController::class);
                    Route::resource('employee', EmployeeController::class);
                    Route::resource('position', PositionController::class);
                    Route::resource('division', DivisionController::class);
                });
            });

            Route::prefix('/transaction/')->group(function () {
                Route::controller(PurchaseController::class)->group(function () {
                    Route::resource('purchase', PurchaseController::class);
                    Route::post('purchase/update-status/{id}', [PurchaseController::class, 'updateStatus'])->name('update-status-purchase');
                    Route::get('purchase/print/{purchase}', [PurchaseController::class, 'createPDF'])->name('purchase.print');
                });
                Route::controller(SaleController::class)->group(function () {
                    Route::resource('sale', SaleController::class);
                    Route::post('sale/update-status/{id}', [SaleController::class, 'updateStatus'])->name('update-status-sale');
                    Route::get('sale/print/{sale}', [SaleController::class, 'createPDF'])->name('sale.print');
                });
                Route::prefix('/warehouse/')->group(function () {
                    Route::resource('incoming-item', IncomingItemController::class);
                    Route::resource('outgoing-item', OutgoingItemController::class);
                });
            });

            Route::prefix('/report/')->group(function () {
                Route::get('stock-report', [StockReportController::class, 'index'])->name('stock-report.index');
                Route::get('stock-balance', [ReportStockBalanceController::class, 'index'])->name('stock-balance.index');
            });
        // });
    });

    Route::controller(DashboardController::class)->group(function(){
        Route::get('login', 'loginpage')->name('login');
        Route::post('/login/send', 'loginSend');
        Route::get('/logout/send', 'sendLogout')->name('logout');
    });

    Route::controller(GlobalController::class)->group(function(){
        Route::get('/render-warn', 'renderWarn');
        Route::get('/call-helper-function', 'callRenderBody')->name('call-helper-function');
        Route::get('lang/{lang}', ['as' => 'lang.switch', 'uses' => 'switchLang']);
        Route::get('/warning', 'showWarning')->name('warning');
    });
});
