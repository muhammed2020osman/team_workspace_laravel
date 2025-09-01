<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DynamicTableController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DriverAuthController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\{CompanySettingsController,PayrollController,AdvancesController,TransactionsController,ExpensesController, DailyReportsController, DashboardController};
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Admin Authentication Routes
Route::prefix('admin')->group(function () {
    // Public routes (no authentication required)
    Route::post('/register', [AdminAuthController::class, 'register']);
    Route::post('/login', [AdminAuthController::class, 'login']);

    // Protected routes (authentication required)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::post('/logout-all', [AdminAuthController::class, 'logoutAll']);
        Route::get('/profile', [AdminAuthController::class, 'profile']);
        Route::put('/profile', [AdminAuthController::class, 'updateProfile']);
        Route::post('/change-password', [AdminAuthController::class, 'changePassword']);
    });
});

// Driver Authentication Routes
Route::prefix('driver')->group(function () {
    // Public routes (no authentication required)
    Route::post('/register', [DriverAuthController::class, 'register']);
    Route::post('/login', [DriverAuthController::class, 'login']);

    // Protected routes (authentication required)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [DriverAuthController::class, 'logout']);
        Route::post('/logout-all', [DriverAuthController::class, 'logoutAll']);
        Route::get('/profile', [DriverAuthController::class, 'profile']);
        Route::put('/profile', [DriverAuthController::class, 'updateProfile']);
        Route::post('/change-password', [DriverAuthController::class, 'changePassword']);
    });
});

// User Authentication Routes
Route::prefix('auth/user')->middleware(['api'])->group(function () {
    // Public routes (no authentication required)

    // Protected routes (authentication required)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [UserAuthController::class, 'logout']);
        Route::post('/logout-all', [UserAuthController::class, 'logoutAll']);
        Route::get('/user', [UserAuthController::class, 'profile']);
        Route::get('/profile', [UserAuthController::class, 'profile']);
        Route::put('/profile', [UserAuthController::class, 'updateProfile']);
        Route::post('/change-password', [UserAuthController::class, 'changePassword']);
    });
});

Route::get('/test', [DynamicTableController::class, 'index']);

// Test route for role-based access control
// Test routes for role-based access control
Route::middleware(['auth:sanctum'])->group(function () {

    // Test route accessible by any authenticated user
    Route::get('/test-role-access', function () {
        $user = auth()->user();
        $role = 'unknown';

        if ($user instanceof \App\Models\Admin) {
            $role = 'admin';
        } elseif ($user instanceof \App\Models\Driver) {
            $role = 'driver';
        } elseif ($user instanceof \App\Models\User) {
            $role = $user->role ?? 'user';
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'تم اختبار نظام الصلاحيات بنجاح',
            'payload' => [
                'user_type' => get_class($user),
                'role' => $role,
                'authenticated' => auth()->check(),
                'guard' => auth()->getDefaultDriver()
            ]
        ]);
    });

    // Test routes with specific role requirements
    Route::get('/test-admin-access', function () {
        return response()->json([
            'status' => true,
            'message' => 'مرحباً، أنت مدير عام!',
            'payload' => ['user' => auth()->user()]
        ]);
    })->middleware('role:super_admin');

    Route::get('/test-manager-access', function () {
        return response()->json([
            'status' => true,
            'message' => 'مرحباً، أنت مدير شركة!',
            'payload' => ['user' => auth()->user()]
        ]);
    })->middleware('role:manager');

    Route::get('/test-driver-access', function () {
        return response()->json([
            'status' => true,
            'message' => 'مرحباً، أنت سائق!',
            'payload' => ['user' => auth()->user()]
        ]);
    })->middleware('role:driver');

    Route::get('/test-user-access', function () {
        return response()->json([
            'status' => true,
            'message' => 'مرحباً، أنت مستخدم عادي!',
            'payload' => ['user' => auth()->user()]
        ]);
    })->middleware('role:user');
});

// Dynamic Table API Routes - Protected with Sanctum authentication
Route::prefix('v1')->middleware(['api'])->group(function () {

    Route::prefix('auth')->middleware(['api'])->group(function () {
        // Public routes (no authentication required)
        Route::post('/register', [UserAuthController::class, 'register']);
        Route::post('/login', [UserAuthController::class, 'login']);
        Route::post('/logout', [UserAuthController::class, 'logout']);
        Route::post('/logout-all', [UserAuthController::class, 'logoutAll']);
        Route::get('/profile', [UserAuthController::class, 'profile']);
        Route::get('/user', [UserAuthController::class, 'user'])->middleware('auth:sanctum');
        Route::put('/profile', [UserAuthController::class, 'updateProfile']);
        Route::post('/change-password', [UserAuthController::class, 'changePassword']);
    });

    // Role-based protected routes
    Route::
    middleware(
        [
            'auth:sanctum'
            ]
        )->
    group(function () {

        // Super Admin Routes - Only for super_admin role
        Route::prefix('admin')->middleware(['role:super_admin'])->group(function () {


            // General admin routes
            registerDynamicTableRoutes();
        });

        // Company Manager Routes - Only for manager role
        Route::prefix('company')
        ->middleware(['role:company_member'])

        ->group(function () {

            Route::prefix('dashboard')
             ->group(function () {
                // Dashboard Statistics Routes
                Route::get('/statistics', [DashboardController::class, 'getStatistics']);
                Route::get('/quick-summary', [DashboardController::class, 'getQuickSummary']);
                Route::get('/real-time-updates', [DashboardController::class, 'getRealTimeUpdates']);

                // Individual Statistics Routes - تم إزالة معظمها لأنها تحتاج معاملات
                // Route::get('/overview', [DashboardController::class, 'getOverviewStats']);
                // Route::get('/drivers', [DashboardController::class, 'getDriversStats']);
                // Route::get('/orders', [DashboardController::class, 'getOrdersStats']);
                // Route::get('/payroll', [DashboardController::class, 'getPayrollStats']);
                // Route::get('/reports', [DashboardController::class, 'getReportsStats']);
                // Route::get('/expenses', [DashboardController::class, 'getExpensesStats']);
                // Route::get('/financial', [DashboardController::class, 'getFinancialStats']);

                // الراوترات التي تعمل بدون معاملات
                Route::get('/recent-activity', [DashboardController::class, 'getRecentActivityForApi']);
                Route::get('/charts', [DashboardController::class, 'getChartsDataForApi']);

                // الراوترات التي تحتاج معاملات - تم تعطيلها
                Route::get('/top-drivers', [DashboardController::class, 'getTopPerformingDrivers']);
                Route::get('/orders-trend', [DashboardController::class, 'getOrdersTrendChart']);
                Route::get('/revenue-trend', [DashboardController::class, 'getRevenueTrendChart']);
                Route::get('/expenses-trend', [DashboardController::class, 'getExpensesTrendChart']);
                Route::get('/drivers-performance', [DashboardController::class, 'getDriversPerformanceChart']);
                Route::get('/cash-flow', [DashboardController::class, 'getCashFlowData']);

            });
            Route::get('/payrolls/drivers', [PayrollController::class, 'getDrivers']);
           // Payroll Routes

    // Get payroll details for a driver
    Route::get('payroll/details/{id}', [\App\Http\Controllers\PayrollDetailsController::class, 'getPayrollDetails']);

    // Get specific driver payroll details within a payroll run
    Route::get('payrolls/runs/{payrollId}/drivers/{driverId}/details', [\App\Http\Controllers\PayrollDetailsController::class, 'getPayrollDetails']);

    // Get payroll run details (for the details page)
    // Route::get('payroll/details/{id}', [PayrollController::class, 'show']);

            Route::resource('payrolls', PayrollController::class);
            Route::get('/settings/delivery_platforms', [CompanySettingsController::class, 'delivery_platforms']);
            Route::post('/settings/delivery_platform', [CompanySettingsController::class, 'storedelivery_platform']);
            Route::get('/settings/delivery_platform/{id}', [CompanySettingsController::class, 'showdelivery_platform']);

            // Daily Reports routes
            Route::get('/daily_reports', [DailyReportsController::class, 'index']);
            Route::get('/daily_reports/{id}', [DailyReportsController::class, 'showReport']);
            Route::post('/daily_reports', [DailyReportsController::class, 'store']);
            Route::put('/daily_reports/{id}', [DailyReportsController::class, 'update']);
            Route::delete('/daily_reports/{id}', [DailyReportsController::class, 'destroy']);
                Route::resource('expenses', ExpensesController::class);
                Route::get('/expenses/list/accounts', [ExpensesController::class, 'listaccounts']);
                Route::resource('transactions', TransactionsController::class);
                Route::resource('advances', AdvancesController::class);

                registerDynamicTableRoutes();
        });
    });
});



Route::prefix('company/tables/payroll_runs')->middleware(['api', 'auth:sanctum'])->group(function () {
    Route::get('/', [PayrollController::class, 'index']);
    Route::post('/', [PayrollController::class, 'store']);
    Route::get('/{id}', [PayrollController::class, 'show']);
    Route::put('/{id}', [PayrollController::class, 'update']);
    Route::delete('/{id}', [PayrollController::class, 'destroy']);
    Route::get('/{id}/debug', [PayrollController::class, 'debug']);
    Route::post('/{id}/create-sample-data', [PayrollController::class, 'createSampleData']);
    Route::get('/statistics', [PayrollController::class, 'statistics']);

    // Payroll details route
    Route::get('/{payrollId}/drivers/{driverId}/details', [\App\Http\Controllers\PayrollDetailsController::class, 'getPayrollDetails']);
});

function registerDynamicTableRoutes()
{

    Route::prefix('tables')->middleware(['api', 'auth:sanctum'])->group(function () {





        Route::get('/{table}/{id}', [DynamicTableController::class, 'showRecord'])
            ->where('id', '[0-9]+')
            ->name('tables.show');

        Route::put('/{table}/{id}', [DynamicTableController::class, 'update'])
            ->where('id', '[0-9]+')
            ->name('tables.update');

        Route::delete('/{table}/{id}', [DynamicTableController::class, 'destroyRecord'])
            ->where('id', '[0-9]+')
            ->name('tables.destroy');

        Route::get('/{table}', [DynamicTableController::class, 'index'])
            ->where('table', '[a-zA-Z_][a-zA-Z0-9_]*')
            ->name('tables.index');
        Route::get('/{table}/list', [DynamicTableController::class, 'list'])
            ->where('table', '[a-zA-Z_][a-zA-Z0-9_]*')
            ->name('tables.list');
        Route::post('/{table}', [DynamicTableController::class, 'store'])
            ->where('table', '[a-zA-Z_][a-zA-Z0-9_]*')
            ->name('tables.store');
    });
}
