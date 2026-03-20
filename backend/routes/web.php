<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\BiomedDataController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\InterventionController;
use App\Http\Controllers\PreventiveMaintenanceController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\SparePartController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PublicComplaintController;
use App\Http\Controllers\DashboardNotificationController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\AccountPasswordController;
use App\Http\Controllers\AdminSecurityController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\OperatorDefectController;
use App\Http\Controllers\AccountProfileController;
use App\Http\Controllers\MaintenanceReportController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\MttrMtbfController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\HierarchieController;
use App\Http\Controllers\ExternalCompanyController;
use App\Http\Controllers\CompanyPerformanceReportController;
use App\Http\Controllers\ExternalInterventionController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Public complaint (no auth)
Route::get('/reclamation', [PublicComplaintController::class, 'index'])
    ->middleware('throttle:public-complaints-view')
    ->name('public.reclamation.index');
Route::get('/reclamation/{service_code}', [PublicComplaintController::class, 'create'])
    ->middleware('throttle:public-complaints-view')
    ->name('public.reclamation.form');
Route::post('/reclamation/{service_code}', [PublicComplaintController::class, 'store'])
    ->middleware('throttle:public-complaints-submit')
    ->name('public.reclamation.store');

// ============================================
// DASHBOARD ROUTES
// ============================================

Route::middleware(['auth', 'prevent-back-history', 'force-password-change', 'enforce-account-security', 'major-read-only'])->group(function () {
    Route::post('/dashboard/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
    Route::delete('/dashboard/push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');

    Route::get('/dashboard/profile', [AccountProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/dashboard/profile', [AccountProfileController::class, 'update'])->name('profile.update');

    Route::get('/dashboard/change-password', [AccountPasswordController::class, 'edit'])->name('password.edit');
    Route::post('/dashboard/change-password', [AccountPasswordController::class, 'update'])->name('password.update');

    Route::prefix('/dashboard/admin')->name('admin.')->group(function () {
        Route::middleware('role:admin')->group(function () {
            Route::get('/security', [AdminSecurityController::class, 'index'])->name('security.index');
        });

        Route::middleware('role:admin,ingenieur')->group(function () {
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
            Route::patch('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');
            Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
        });

        Route::middleware('role:admin,ingenieur')->group(function () {
            Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
            Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
            Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        });
    });

    Route::middleware('role:admin,manager,major,ingenieur,technicien,technician')->group(function () {
        // Main Dashboard
        Route::get('/dashboard', [BiomedDataController::class, 'dashboard'])->name('dashboard');
        Route::get('/organisation/gst', [OrganisationController::class, 'index'])->name('organisation.gst');
        Route::get('/dashboard/hierarchie-chu', [HierarchieController::class, 'index'])->name('hierarchie.index');
        Route::post('/dashboard/hierarchie-chu/import-excel', [HierarchieController::class, 'importExcel'])->name('hierarchie.import-excel');
        Route::get('/dashboard/hierarchie-chu/export-json', [HierarchieController::class, 'exportJson'])->name('hierarchie.export-json');
        Route::get('/dashboard/hierarchie-chu/export-excel', [HierarchieController::class, 'exportExcel'])->name('hierarchie.export-excel');
        Route::get('/dashboard/live-metrics', [BiomedDataController::class, 'liveMetrics'])->name('dashboard.live-metrics');
        Route::get('/dashboard/marches-equipements', [BiomedDataController::class, 'marketsEquipments'])->name('markets.equipments');
        Route::get('/dashboard/marches-equipements/{market}', [BiomedDataController::class, 'showMarket'])->name('markets.show');
        Route::get('/dashboard/marches-equipements/{market}/edit', [BiomedDataController::class, 'editMarket'])->name('markets.edit');
        Route::put('/dashboard/marches-equipements/{market}', [BiomedDataController::class, 'updateMarket'])->name('markets.update');
        Route::delete('/dashboard/marches-equipements/{market}', [BiomedDataController::class, 'destroyMarket'])->name('markets.destroy');
        Route::post('/dashboard/marches-equipements/import-excel', [BiomedDataController::class, 'importMarketsEquipmentsExcel'])->name('markets.equipments.import-excel');
        Route::patch('/dashboard/marches-equipements/equipment/{equipment}', [BiomedDataController::class, 'updateMarketEquipment'])->name('markets.equipments.update-equipment');
        Route::patch('/dashboard/marches-equipements/line/{line}/quick-action', [BiomedDataController::class, 'quickActionMarketImportLine'])->name('markets.equipments.line.quick-action');
        Route::delete('/dashboard/marches-equipements/line/{line}', [BiomedDataController::class, 'destroyMarketImportLine'])->name('markets.equipments.line.destroy');

        // Équipements Module
        Route::middleware('equipment.access')->group(function () {
            Route::get('/dashboard/equipements', [EquipmentController::class, 'index'])->name('equipements');
            Route::get('/dashboard/equipements/create', [EquipmentController::class, 'create'])->name('equipments.create');
            Route::get('/dashboard/equipements/{id}/edit', [EquipmentController::class, 'edit'])->name('equipements.edit');
            Route::post('/dashboard/equipements', [EquipmentController::class, 'store'])->name('equipements.store');
            Route::put('/dashboard/equipements/{id}', [EquipmentController::class, 'update'])->name('equipements.update');
            Route::delete('/dashboard/equipements/{id}', [EquipmentController::class, 'destroy'])->name('equipements.destroy');
            Route::post('/dashboard/equipements/import-excel', [EquipmentController::class, 'importExcelFile'])->name('equipements.import-excel');
            Route::get('/dashboard/equipements/export/excel', [EquipmentController::class, 'exportExcel'])->name('equipements.export.excel');
            Route::get('/dashboard/equipements/export/pdf', [EquipmentController::class, 'exportPdf'])->name('equipements.export.pdf');
            Route::get('/dashboard/equipements/formations', [EquipmentController::class, 'formations'])->name('formations.index');
            Route::post('/dashboard/equipements/formations/import-pdf', [EquipmentController::class, 'importFormationPdf'])->name('formations.import-pdf');
            Route::get('/dashboard/equipements/formations/export-pdf', [EquipmentController::class, 'exportFormationsPdf'])->name('formations.export-pdf');
            Route::get('/dashboard/equipements/assets/{asset}/{type}', [EquipmentController::class, 'designationAssetFile'])->name('equipements.assets.file');
            Route::get('/dashboard/equipements/services', [EquipmentController::class, 'servicesByZone'])->name('equipements.services');
            Route::get('/dashboard/equipements/salles', [EquipmentController::class, 'roomsByService'])->name('equipements.rooms');
            Route::patch('/dashboard/equipements/{id}/status', [EquipmentController::class, 'updateStatus'])->name('equipements.update-status');
            Route::post('/dashboard/equipements/bulk-update-by-designation', [EquipmentController::class, 'bulkUpdateByDesignation'])->name('equipements.bulk-update-designation');
            Route::get('/dashboard/equipements/{id}', [EquipmentController::class, 'show'])->name('equipements.show');
        });

        // Interventions Module
        Route::get('/dashboard/interventions', [InterventionController::class, 'index'])->name('interventions');
        Route::get('/dashboard/interventions/codes', [InterventionController::class, 'codes'])->name('interventions.codes');
        Route::get('/dashboard/interventions/create', [InterventionController::class, 'create'])->name('interventions.create');
        Route::post('/dashboard/interventions', [InterventionController::class, 'store'])->name('interventions.store');
        Route::get('/dashboard/interventions/{id}/edit', [InterventionController::class, 'edit'])->name('interventions.edit');
        Route::put('/dashboard/interventions/{id}', [InterventionController::class, 'update'])->name('interventions.update');
        Route::get('/dashboard/interventions/{id}/cloture', [InterventionController::class, 'closeForm'])->name('interventions.close.form');
        Route::post('/dashboard/interventions/{id}/cloture', [InterventionController::class, 'close'])->name('interventions.close');
        Route::get('/dashboard/interventions/{id}', [InterventionController::class, 'show'])->name('interventions.show');

        // KPI MTTR/MTBF Module
        Route::get('/dashboard/kpi/mttr-mtbf', [MttrMtbfController::class, 'index'])->name('mttr-mtbf');
        Route::get('/dashboard/kpi/mttr-mtbf/data', [MttrMtbfController::class, 'data'])->name('mttr-mtbf.data');

        // Complaints module (engineer + technicians only)
        Route::middleware('role:admin,ingenieur,major,technicien,technician')->group(function () {
            Route::get('/dashboard/reclamations', [ComplaintController::class, 'index'])->name('reclamations.index');
            Route::patch('/dashboard/reclamations/{complaint}/status', [ComplaintController::class, 'updateStatus'])
                ->whereNumber('complaint')
                ->missing(fn () => redirect()->route('reclamations.index')->with('error', 'Cette réclamation n\'existe plus.'))
                ->name('reclamations.status.update');

            Route::get('/dashboard/notifications/complaints', [DashboardNotificationController::class, 'complaints'])
                ->name('dashboard.notifications.complaints');
            Route::get('/dashboard/notifications/complaints/{complaint}', [DashboardNotificationController::class, 'showComplaint'])
                ->whereNumber('complaint')
                ->missing(fn () => redirect()->route('reclamations.index')->with('error', 'Ancienne réclamation introuvable (peut-être supprimée/archivée).'))
                ->name('dashboard.notifications.complaints.show');
            Route::get('/dashboard/notifications/complaints/archive/{notificationId}', [DashboardNotificationController::class, 'showArchivedComplaint'])
                ->name('dashboard.notifications.complaints.archive');
            Route::get('/dashboard/notifications/complaints/{complaint}/attachments/{index}', [DashboardNotificationController::class, 'attachment'])
                ->whereNumber('complaint')
                ->whereNumber('index')
                ->missing(fn () => redirect()->route('reclamations.index')->with('error', 'Pièce jointe introuvable pour cette ancienne réclamation.'))
                ->name('dashboard.notifications.complaints.attachment');
            Route::patch('/dashboard/notifications/complaints/{complaint}/close', [DashboardNotificationController::class, 'closeComplaint'])
                ->whereNumber('complaint')
                ->missing(fn () => redirect()->route('reclamations.index')->with('error', 'Impossible de clôturer: la réclamation est introuvable.'))
                ->name('dashboard.notifications.complaints.close');
            Route::post('/dashboard/notifications/complaints/read-all', [DashboardNotificationController::class, 'markAllComplaintAsRead'])
                ->name('dashboard.notifications.complaints.read-all');
        });

        // Maintenance Préventive Module
        Route::get('/dashboard/maintenance-preventive', [PreventiveMaintenanceController::class, 'index'])->name('maintenance-preventive');
        Route::get('/dashboard/maintenance-preventive/create', [PreventiveMaintenanceController::class, 'create'])->name('maintenance-preventive.create');
        Route::post('/dashboard/maintenance-preventive', [PreventiveMaintenanceController::class, 'store'])->name('maintenance-preventive.store');
        Route::get('/dashboard/maintenance-preventive/{maintenance_preventive}/edit', [PreventiveMaintenanceController::class, 'edit'])->name('maintenance-preventive.edit');
        Route::put('/dashboard/maintenance-preventive/{maintenance_preventive}', [PreventiveMaintenanceController::class, 'update'])->name('maintenance-preventive.update');
        Route::delete('/dashboard/maintenance-preventive/{maintenance_preventive}', [PreventiveMaintenanceController::class, 'destroy'])->name('maintenance-preventive.destroy');

        // Techniciens Module
        Route::get('/dashboard/techniciens', [BiomedDataController::class, 'techniciens'])->name('techniciens');
        Route::get('/dashboard/techniciens/create', [TechnicianController::class, 'create'])->name('technicians.create');

        // Pièces de Rechange Module
        Route::get('/dashboard/pieces', [SparePartController::class, 'index'])->name('pieces');
        Route::get('/dashboard/pieces/create', [SparePartController::class, 'create'])->name('pieces.create');
        Route::post('/dashboard/pieces', [SparePartController::class, 'store'])->name('pieces.store');
        Route::get('/dashboard/pieces/{piece}/edit', [SparePartController::class, 'edit'])->name('pieces.edit');
        Route::put('/dashboard/pieces/{piece}', [SparePartController::class, 'update'])->name('pieces.update');
        Route::delete('/dashboard/pieces/{piece}', [SparePartController::class, 'destroy'])->name('pieces.destroy');

        // Rapports Module
        Route::get('/dashboard/rapports', [BiomedDataController::class, 'rapports'])->name('rapports');
        Route::get('/dashboard/rapports/societes-externes/interventions', [CompanyPerformanceReportController::class, 'index'])->name('company-performance.index');
        Route::get('/dashboard/rapports/societes-externes/interventions/export-excel', [CompanyPerformanceReportController::class, 'exportExcel'])->name('company-performance.export-excel');
        Route::get('/dashboard/rapports/societes-externes/interventions/export-pdf', [CompanyPerformanceReportController::class, 'exportPdf'])->name('company-performance.export-pdf');
        Route::get('/dashboard/sav-externe/tickets', [ExternalInterventionController::class, 'index'])->name('sav-tickets.index');
        Route::get('/dashboard/sav-externe/tickets/create', [ExternalInterventionController::class, 'create'])->name('sav-tickets.create');
        Route::post('/dashboard/sav-externe/tickets', [ExternalInterventionController::class, 'store'])->name('sav-tickets.store');
        Route::get('/dashboard/sav-externe/tickets/{savTicket}/edit', [ExternalInterventionController::class, 'edit'])->name('sav-tickets.edit');
        Route::put('/dashboard/sav-externe/tickets/{savTicket}', [ExternalInterventionController::class, 'update'])->name('sav-tickets.update');
        Route::delete('/dashboard/sav-externe/tickets/{savTicket}', [ExternalInterventionController::class, 'destroy'])->name('sav-tickets.destroy');
        // Backward-compat alias
        Route::permanentRedirect('/dashboard/sav-externe/tickets-old', '/dashboard/sav-externe/tickets');
        Route::get('/dashboard/rapports/interventions-internes', [MaintenanceReportController::class, 'index'])->name('maintenance-reports.index');
        Route::get('/dashboard/rapports/interventions-internes/create', [MaintenanceReportController::class, 'create'])->name('maintenance-reports.create');
        Route::post('/dashboard/rapports/interventions-internes', [MaintenanceReportController::class, 'store'])->name('maintenance-reports.store');
        Route::post('/dashboard/rapports/interventions-internes/import-corrective', [MaintenanceReportController::class, 'importCorrectiveFromBilan'])->name('maintenance-reports.import-corrective');
        Route::post('/dashboard/rapports/interventions-internes/import-corrective-pdf', [MaintenanceReportController::class, 'importCorrectivePdf'])->name('maintenance-reports.import-corrective-pdf');
        Route::delete('/dashboard/rapports/interventions-internes/import-corrective-pdf', [MaintenanceReportController::class, 'deleteCorrectivePdf'])->name('maintenance-reports.delete-corrective-pdf');
        Route::get('/dashboard/rapports/interventions-internes/maintenance-corrective/manual', [MaintenanceReportController::class, 'correctiveManual'])->name('maintenance-reports.corrective.manual');
        Route::get('/dashboard/rapports/interventions-internes/maintenance-corrective/template-excel', [MaintenanceReportController::class, 'correctiveTemplate'])->name('maintenance-reports.corrective.template-excel');
        Route::get('/dashboard/rapports/interventions-internes/maintenance-corrective/export-excel', [MaintenanceReportController::class, 'exportCorrectiveExcel'])->name('maintenance-reports.corrective.export-excel');
        Route::get('/dashboard/rapports/interventions-internes/{maintenanceReport}/edit', [MaintenanceReportController::class, 'edit'])->name('maintenance-reports.edit');
        Route::put('/dashboard/rapports/interventions-internes/{maintenanceReport}', [MaintenanceReportController::class, 'update'])->name('maintenance-reports.update');
        Route::patch('/dashboard/rapports/interventions-internes/{maintenanceReport}/submit', [MaintenanceReportController::class, 'submit'])->name('maintenance-reports.submit');
        Route::patch('/dashboard/rapports/interventions-internes/{maintenanceReport}/validate', [MaintenanceReportController::class, 'validateReport'])->name('maintenance-reports.validate');
        Route::patch('/dashboard/rapports/interventions-internes/{maintenanceReport}/close', [MaintenanceReportController::class, 'close'])->name('maintenance-reports.close');
        Route::get('/dashboard/rapports/interventions-internes/{maintenanceReport}/generate-pdf', [MaintenanceReportController::class, 'generatePDF'])->name('maintenance-reports.generate-pdf');
        Route::get('/dashboard/rapports/interventions-internes/{maintenanceReport}/pdf', [MaintenanceReportController::class, 'exportPdf'])->name('maintenance-reports.pdf');

        // Paramètres Module
        Route::get('/dashboard/parametres', [SettingsController::class, 'index'])->name('parametres');
        Route::post('/dashboard/parametres/general', [SettingsController::class, 'updateGeneral'])->name('parametres.general.update');
        Route::post('/dashboard/parametres/panel', [SettingsController::class, 'updatePanel'])->name('parametres.panel.update');

        // Planning + Stock
        Route::get('/dashboard/societes-externes', [ExternalCompanyController::class, 'index'])->name('external-companies.index');
        Route::get('/dashboard/societes-externes/create', [ExternalCompanyController::class, 'create'])->name('external-companies.create');
        Route::post('/dashboard/societes-externes', [ExternalCompanyController::class, 'store'])->name('external-companies.store');
        Route::post('/dashboard/societes-externes/import-excel', [ExternalCompanyController::class, 'importExcel'])->name('external-companies.import-excel');
        Route::delete('/dashboard/societes-externes/{company}', [ExternalCompanyController::class, 'destroy'])->name('external-companies.destroy');

        Route::get('/dashboard/planning-societes-externes', [PlanningController::class, 'index'])->name('planning.index');
        Route::post('/dashboard/planning-societes-externes/import-excel', [PlanningController::class, 'importContractsExcel'])->name('planning.import-excel');
        Route::post('/dashboard/planning-societes-externes/sync-contracts', [PlanningController::class, 'syncFromContracts'])->name('planning.sync-contracts');
        Route::get('/dashboard/planning-societes-externes/create', [PlanningController::class, 'create'])->name('planning.create');
        Route::post('/dashboard/planning-societes-externes', [PlanningController::class, 'store'])->name('planning.store');
        Route::get('/dashboard/planning-societes-externes/{planning}/edit', [PlanningController::class, 'edit'])->name('planning.edit');
        Route::put('/dashboard/planning-societes-externes/{planning}', [PlanningController::class, 'update'])->name('planning.update');
        Route::delete('/dashboard/planning-societes-externes/{planning}', [PlanningController::class, 'destroy'])->name('planning.destroy');

        Route::get('/dashboard/stock-movements', [StockMovementController::class, 'movements'])->name('stock.movements');
        Route::get('/dashboard/stock-movements/create', [StockMovementController::class, 'create'])->name('stock.create');
        Route::post('/dashboard/stock-movements', [StockMovementController::class, 'store'])->name('stock.store');
        Route::get('/dashboard/stock-movements/{movement}/edit', [StockMovementController::class, 'edit'])->name('stock.edit');
        Route::put('/dashboard/stock-movements/{movement}', [StockMovementController::class, 'update'])->name('stock.update');
        Route::delete('/dashboard/stock-movements/{movement}', [StockMovementController::class, 'destroy'])->name('stock.destroy');

        // Services Module
        Route::resource('/dashboard/services', ServiceController::class)->except('show');
    });

    // Nouvelle réclamation uniquement ingénieur + techniciens
    Route::middleware('role:ingenieur,major,technicien,technician')->group(function () {
        Route::get('/dashboard/operator/defects/create', [OperatorDefectController::class, 'create'])->name('operator.defects.create');
        Route::post('/dashboard/operator/defects', [OperatorDefectController::class, 'store'])->name('operator.defects.store');
        Route::get('/dashboard/operator/defects/services/{service}/equipments', [OperatorDefectController::class, 'equipmentsByService'])->name('operator.defects.equipments');
    });
});
