<?php

/*
|--------------------------------------------------------------------------
| Load The Cached Routes
|--------------------------------------------------------------------------
|
| Here we will decode and unserialize the RouteCollection instance that
| holds all of the route information for an application. This allows
| us to instantaneously load the entire route map into the router.
|
*/

app('router')->setCompiledRoutes(
    array (
  'compiled' => 
  array (
    0 => false,
    1 => 
    array (
      '/_debugbar/open' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.openhandler',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_debugbar/assets/stylesheets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.assets.css',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_debugbar/assets/javascript' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.assets.js',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_debugbar/queries/explain' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.queries.explain',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_ignition/health-check' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ignition.healthCheck',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_ignition/execute-solution' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ignition.executeSolution',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_ignition/update-config' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ignition.updateConfig',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'home',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'login',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'login.submit',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'logout',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reclamation' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'public.reclamation.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/push-subscriptions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'push-subscriptions.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'push-subscriptions.destroy',
          ),
          1 => NULL,
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/profile' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'profile.edit',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'profile.update',
          ),
          1 => NULL,
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/change-password' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.edit',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'password.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/admin/security' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.security.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/admin/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/organisation/gst' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'organisation.gst',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/hierarchie-chu' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'hierarchie.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/hierarchie-chu/import-excel' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'hierarchie.import-excel',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/hierarchie-chu/export-json' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'hierarchie.export-json',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/hierarchie-chu/export-excel' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'hierarchie.export-excel',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/live-metrics' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.live-metrics',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/marches-equipements' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'markets.equipments',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/equipements' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'equipements',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'equipements.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/equipements/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'equipments.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/equipements/export/excel' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'equipements.export.excel',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/equipements/export/pdf' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'equipements.export.pdf',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/equipements/formations/import-pdf' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'formations.import-pdf',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/equipements/formations/export-pdf' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'formations.export-pdf',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/interventions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'interventions',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'interventions.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/interventions/codes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'interventions.codes',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/interventions/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'interventions.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/kpi/mttr-mtbf' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mttr-mtbf',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/kpi/mttr-mtbf/data' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mttr-mtbf.data',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/reclamations' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reclamations.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/notifications/complaints' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.notifications.complaints',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/notifications/complaints/read-all' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.notifications.complaints.read-all',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/maintenance-preventive' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-preventive',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-preventive.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/maintenance-preventive/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-preventive.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/techniciens' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'techniciens',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/techniciens/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'technicians.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/pieces' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'pieces',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'pieces.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/pieces/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'pieces.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/rapports' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'rapports',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/rapports/societes-externes/interventions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'company-performance.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/rapports/societes-externes/interventions/export-excel' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'company-performance.export-excel',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/rapports/societes-externes/interventions/export-pdf' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'company-performance.export-pdf',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/sav-externe/tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sav-tickets.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'sav-tickets.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/sav-externe/tickets/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sav-tickets.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/sav-externe/tickets-old' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::6PJN2QVEcppNo3RY',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
            'POST' => 2,
            'PUT' => 3,
            'PATCH' => 4,
            'DELETE' => 5,
            'OPTIONS' => 6,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/rapports/interventions-internes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-reports.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-reports.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/rapports/interventions-internes/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-reports.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/rapports/interventions-internes/import-corrective' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-reports.import-corrective',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/rapports/interventions-internes/import-corrective-pdf' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-reports.import-corrective-pdf',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-reports.delete-corrective-pdf',
          ),
          1 => NULL,
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/rapports/interventions-internes/maintenance-corrective/manual' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-reports.corrective.manual',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/rapports/interventions-internes/maintenance-corrective/template-excel' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-reports.corrective.template-excel',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/rapports/interventions-internes/maintenance-corrective/export-excel' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-reports.corrective.export-excel',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/parametres' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'parametres',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/parametres/general' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'parametres.general.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/parametres/panel' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'parametres.panel.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/societes-externes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'external-companies.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'external-companies.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/societes-externes/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'external-companies.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/societes-externes/import-excel' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'external-companies.import-excel',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/planning-societes-externes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'planning.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'planning.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/planning-societes-externes/import-excel' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'planning.import-excel',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/planning-societes-externes/sync-contracts' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'planning.sync-contracts',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/planning-societes-externes/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'planning.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/stock-movements' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'stock.movements',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'stock.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/stock-movements/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'stock.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/services' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'services.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'services.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/services/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'services.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/operator/defects/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'operator.defects.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/operator/defects' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'operator.defects.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/user' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::UUGkLeOFI9u01giE',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
    ),
    2 => 
    array (
      0 => '{^(?|/_debugbar/c(?|lockwork/([^/]++)(*:39)|ache/([^/]++)(?:/([^/]++))?(*:73))|/reclamation/([^/]++)(?|(*:105))|/dashboard/(?|admin/users/(?|([^/]++)(?|/(?|edit(*:162)|toggle\\-active(*:184)|reset\\-password(*:207))|(*:216))|create(*:231)|([^/]++)(*:247))|ma(?|rches\\-equipements/(?|([^/]++)(?|(*:294)|/edit(*:307)|(*:315))|import\\-excel(*:337)|equipment/([^/]++)(*:363)|line/([^/]++)(?|/quick\\-action(*:401)|(*:409)))|intenance\\-preventive/([^/]++)(?|/edit(*:457)|(*:465)))|equipements/(?|([^/]++)(?|/edit(*:506)|(*:514))|import\\-excel(*:536)|formations(*:554)|assets/([^/]++)/([^/]++)(*:586)|s(?|ervices(*:605)|alles(*:618))|([^/]++)/status(*:642)|bulk\\-update\\-by\\-designation(*:679)|([^/]++)(*:695))|interventions/([^/]++)(?|/(?|edit(*:737)|cloture(?|(*:755)))|(*:765))|r(?|eclamations/([0-9]+)/status(*:805)|apports/interventions\\-internes/([^/]++)(?|/(?|edit(*:864)|submit(*:878)|validate(*:894)|close(*:907)|generate\\-pdf(*:928)|pdf(*:939))|(*:948)))|notifications/complaints/(?|([0-9]+)(*:994)|archive/([^/]++)(*:1018)|([0-9]+)/attachments/([0-9]+)(*:1056)|([0-9]+)/close(*:1079))|p(?|ieces/([^/]++)(?|/edit(*:1115)|(*:1124))|lanning\\-societes\\-externes/([^/]++)(?|/edit(*:1178)|(*:1187)))|s(?|av\\-externe/tickets/([^/]++)(?|/edit(*:1238)|(*:1247))|ocietes\\-externes/([^/]++)(*:1283)|tock\\-movements/([^/]++)(?|/edit(*:1324)|(*:1333))|ervices/([^/]++)(?|/edit(*:1367)|(*:1376)))|operator/defects/services/([^/]++)/equipments(*:1432)))/?$}sDu',
    ),
    3 => 
    array (
      39 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.clockwork',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      73 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.cache.delete',
            'tags' => NULL,
          ),
          1 => 
          array (
            0 => 'key',
            1 => 'tags',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      105 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'public.reclamation.form',
          ),
          1 => 
          array (
            0 => 'service_code',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'public.reclamation.store',
          ),
          1 => 
          array (
            0 => 'service_code',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      162 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.edit',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      184 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.toggle-active',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      207 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.reset-password',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      216 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.update',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      231 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.create',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      247 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.destroy',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      294 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'markets.show',
          ),
          1 => 
          array (
            0 => 'market',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      307 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'markets.edit',
          ),
          1 => 
          array (
            0 => 'market',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      315 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'markets.update',
          ),
          1 => 
          array (
            0 => 'market',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'markets.destroy',
          ),
          1 => 
          array (
            0 => 'market',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      337 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'markets.equipments.import-excel',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      363 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'markets.equipments.update-equipment',
          ),
          1 => 
          array (
            0 => 'equipment',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      401 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'markets.equipments.line.quick-action',
          ),
          1 => 
          array (
            0 => 'line',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      409 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'markets.equipments.line.destroy',
          ),
          1 => 
          array (
            0 => 'line',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      457 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-preventive.edit',
          ),
          1 => 
          array (
            0 => 'maintenance_preventive',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      465 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-preventive.update',
          ),
          1 => 
          array (
            0 => 'maintenance_preventive',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-preventive.destroy',
          ),
          1 => 
          array (
            0 => 'maintenance_preventive',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      506 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'equipements.edit',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      514 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'equipements.update',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'equipements.destroy',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      536 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'equipements.import-excel',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      554 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'formations.index',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      586 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'equipements.assets.file',
          ),
          1 => 
          array (
            0 => 'asset',
            1 => 'type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      605 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'equipements.services',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      618 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'equipements.rooms',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      642 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'equipements.update-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      679 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'equipements.bulk-update-designation',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      695 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'equipements.show',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      737 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'interventions.edit',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      755 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'interventions.close.form',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'interventions.close',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      765 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'interventions.update',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'interventions.show',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      805 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reclamations.status.update',
          ),
          1 => 
          array (
            0 => 'complaint',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      864 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-reports.edit',
          ),
          1 => 
          array (
            0 => 'maintenanceReport',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      878 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-reports.submit',
          ),
          1 => 
          array (
            0 => 'maintenanceReport',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      894 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-reports.validate',
          ),
          1 => 
          array (
            0 => 'maintenanceReport',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      907 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-reports.close',
          ),
          1 => 
          array (
            0 => 'maintenanceReport',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      928 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-reports.generate-pdf',
          ),
          1 => 
          array (
            0 => 'maintenanceReport',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      939 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-reports.pdf',
          ),
          1 => 
          array (
            0 => 'maintenanceReport',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      948 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'maintenance-reports.update',
          ),
          1 => 
          array (
            0 => 'maintenanceReport',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      994 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.notifications.complaints.show',
          ),
          1 => 
          array (
            0 => 'complaint',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1018 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.notifications.complaints.archive',
          ),
          1 => 
          array (
            0 => 'notificationId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1056 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.notifications.complaints.attachment',
          ),
          1 => 
          array (
            0 => 'complaint',
            1 => 'index',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1079 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.notifications.complaints.close',
          ),
          1 => 
          array (
            0 => 'complaint',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1115 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'pieces.edit',
          ),
          1 => 
          array (
            0 => 'piece',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1124 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'pieces.update',
          ),
          1 => 
          array (
            0 => 'piece',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'pieces.destroy',
          ),
          1 => 
          array (
            0 => 'piece',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1178 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'planning.edit',
          ),
          1 => 
          array (
            0 => 'planning',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1187 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'planning.update',
          ),
          1 => 
          array (
            0 => 'planning',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'planning.destroy',
          ),
          1 => 
          array (
            0 => 'planning',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1238 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sav-tickets.edit',
          ),
          1 => 
          array (
            0 => 'savTicket',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1247 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sav-tickets.update',
          ),
          1 => 
          array (
            0 => 'savTicket',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'sav-tickets.destroy',
          ),
          1 => 
          array (
            0 => 'savTicket',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1283 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'external-companies.destroy',
          ),
          1 => 
          array (
            0 => 'company',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1324 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'stock.edit',
          ),
          1 => 
          array (
            0 => 'movement',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1333 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'stock.update',
          ),
          1 => 
          array (
            0 => 'movement',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'stock.destroy',
          ),
          1 => 
          array (
            0 => 'movement',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1367 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'services.edit',
          ),
          1 => 
          array (
            0 => 'service',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1376 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'services.update',
          ),
          1 => 
          array (
            0 => 'service',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'services.destroy',
          ),
          1 => 
          array (
            0 => 'service',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1432 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'operator.defects.equipments',
          ),
          1 => 
          array (
            0 => 'service',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => NULL,
          1 => NULL,
          2 => NULL,
          3 => NULL,
          4 => false,
          5 => false,
          6 => 0,
        ),
      ),
    ),
    4 => NULL,
  ),
  'attributes' => 
  array (
    'debugbar.openhandler' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_debugbar/open',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\OpenHandlerController@handle',
        'as' => 'debugbar.openhandler',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\OpenHandlerController@handle',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.clockwork' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_debugbar/clockwork/{id}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\OpenHandlerController@clockwork',
        'as' => 'debugbar.clockwork',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\OpenHandlerController@clockwork',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.assets.css' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_debugbar/assets/stylesheets',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\AssetController@css',
        'as' => 'debugbar.assets.css',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\AssetController@css',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.assets.js' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_debugbar/assets/javascript',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\AssetController@js',
        'as' => 'debugbar.assets.js',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\AssetController@js',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.cache.delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => '_debugbar/cache/{key}/{tags?}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\CacheController@delete',
        'as' => 'debugbar.cache.delete',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\CacheController@delete',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.queries.explain' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '_debugbar/queries/explain',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\QueriesController@explain',
        'as' => 'debugbar.queries.explain',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\QueriesController@explain',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ignition.healthCheck' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_ignition/health-check',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'Spatie\\LaravelIgnition\\Http\\Middleware\\RunnableSolutionsEnabled',
        ),
        'uses' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\HealthCheckController@__invoke',
        'controller' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\HealthCheckController',
        'as' => 'ignition.healthCheck',
        'namespace' => NULL,
        'prefix' => '_ignition',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ignition.executeSolution' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '_ignition/execute-solution',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'Spatie\\LaravelIgnition\\Http\\Middleware\\RunnableSolutionsEnabled',
        ),
        'uses' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\ExecuteSolutionController@__invoke',
        'controller' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\ExecuteSolutionController',
        'as' => 'ignition.executeSolution',
        'namespace' => NULL,
        'prefix' => '_ignition',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ignition.updateConfig' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '_ignition/update-config',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'Spatie\\LaravelIgnition\\Http\\Middleware\\RunnableSolutionsEnabled',
        ),
        'uses' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\UpdateConfigController@__invoke',
        'controller' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\UpdateConfigController',
        'as' => 'ignition.updateConfig',
        'namespace' => NULL,
        'prefix' => '_ignition',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'home' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '/',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\HomeController@index',
        'controller' => 'App\\Http\\Controllers\\HomeController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'home',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'guest',
        ),
        'uses' => 'App\\Http\\Controllers\\AuthController@showLogin',
        'controller' => 'App\\Http\\Controllers\\AuthController@showLogin',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'login',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'login.submit' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'guest',
        ),
        'uses' => 'App\\Http\\Controllers\\AuthController@login',
        'controller' => 'App\\Http\\Controllers\\AuthController@login',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'login.submit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\AuthController@logout',
        'controller' => 'App\\Http\\Controllers\\AuthController@logout',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'logout',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'public.reclamation.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reclamation',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'throttle:public-complaints-view',
        ),
        'uses' => 'App\\Http\\Controllers\\PublicComplaintController@index',
        'controller' => 'App\\Http\\Controllers\\PublicComplaintController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'public.reclamation.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'public.reclamation.form' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reclamation/{service_code}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'throttle:public-complaints-view',
        ),
        'uses' => 'App\\Http\\Controllers\\PublicComplaintController@create',
        'controller' => 'App\\Http\\Controllers\\PublicComplaintController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'public.reclamation.form',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'public.reclamation.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'reclamation/{service_code}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'throttle:public-complaints-submit',
        ),
        'uses' => 'App\\Http\\Controllers\\PublicComplaintController@store',
        'controller' => 'App\\Http\\Controllers\\PublicComplaintController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'public.reclamation.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'push-subscriptions.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/push-subscriptions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
        ),
        'uses' => 'App\\Http\\Controllers\\PushSubscriptionController@store',
        'controller' => 'App\\Http\\Controllers\\PushSubscriptionController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'push-subscriptions.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'push-subscriptions.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'dashboard/push-subscriptions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
        ),
        'uses' => 'App\\Http\\Controllers\\PushSubscriptionController@destroy',
        'controller' => 'App\\Http\\Controllers\\PushSubscriptionController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'push-subscriptions.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'profile.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
        ),
        'uses' => 'App\\Http\\Controllers\\AccountProfileController@edit',
        'controller' => 'App\\Http\\Controllers\\AccountProfileController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'profile.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'profile.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'dashboard/profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
        ),
        'uses' => 'App\\Http\\Controllers\\AccountProfileController@update',
        'controller' => 'App\\Http\\Controllers\\AccountProfileController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'profile.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/change-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
        ),
        'uses' => 'App\\Http\\Controllers\\AccountPasswordController@edit',
        'controller' => 'App\\Http\\Controllers\\AccountPasswordController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/change-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
        ),
        'uses' => 'App\\Http\\Controllers\\AccountPasswordController@update',
        'controller' => 'App\\Http\\Controllers\\AccountPasswordController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.security.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/admin/security',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\AdminSecurityController@index',
        'controller' => 'App\\Http\\Controllers\\AdminSecurityController@index',
        'as' => 'admin.security.index',
        'namespace' => NULL,
        'prefix' => '/dashboard/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,ingenieur',
        ),
        'uses' => 'App\\Http\\Controllers\\AdminUserController@index',
        'controller' => 'App\\Http\\Controllers\\AdminUserController@index',
        'as' => 'admin.users.index',
        'namespace' => NULL,
        'prefix' => '/dashboard/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/admin/users/{user}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,ingenieur',
        ),
        'uses' => 'App\\Http\\Controllers\\AdminUserController@edit',
        'controller' => 'App\\Http\\Controllers\\AdminUserController@edit',
        'as' => 'admin.users.edit',
        'namespace' => NULL,
        'prefix' => '/dashboard/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'dashboard/admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,ingenieur',
        ),
        'uses' => 'App\\Http\\Controllers\\AdminUserController@update',
        'controller' => 'App\\Http\\Controllers\\AdminUserController@update',
        'as' => 'admin.users.update',
        'namespace' => NULL,
        'prefix' => '/dashboard/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.toggle-active' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'dashboard/admin/users/{user}/toggle-active',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,ingenieur',
        ),
        'uses' => 'App\\Http\\Controllers\\AdminUserController@toggleActive',
        'controller' => 'App\\Http\\Controllers\\AdminUserController@toggleActive',
        'as' => 'admin.users.toggle-active',
        'namespace' => NULL,
        'prefix' => '/dashboard/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.reset-password' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/admin/users/{user}/reset-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,ingenieur',
        ),
        'uses' => 'App\\Http\\Controllers\\AdminUserController@resetPassword',
        'controller' => 'App\\Http\\Controllers\\AdminUserController@resetPassword',
        'as' => 'admin.users.reset-password',
        'namespace' => NULL,
        'prefix' => '/dashboard/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/admin/users/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,ingenieur',
        ),
        'uses' => 'App\\Http\\Controllers\\AdminUserController@create',
        'controller' => 'App\\Http\\Controllers\\AdminUserController@create',
        'as' => 'admin.users.create',
        'namespace' => NULL,
        'prefix' => '/dashboard/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,ingenieur',
        ),
        'uses' => 'App\\Http\\Controllers\\AdminUserController@store',
        'controller' => 'App\\Http\\Controllers\\AdminUserController@store',
        'as' => 'admin.users.store',
        'namespace' => NULL,
        'prefix' => '/dashboard/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'dashboard/admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,ingenieur',
        ),
        'uses' => 'App\\Http\\Controllers\\AdminUserController@destroy',
        'controller' => 'App\\Http\\Controllers\\AdminUserController@destroy',
        'as' => 'admin.users.destroy',
        'namespace' => NULL,
        'prefix' => '/dashboard/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\BiomedDataController@dashboard',
        'controller' => 'App\\Http\\Controllers\\BiomedDataController@dashboard',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'organisation.gst' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'organisation/gst',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\OrganisationController@index',
        'controller' => 'App\\Http\\Controllers\\OrganisationController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'organisation.gst',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'hierarchie.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/hierarchie-chu',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\HierarchieController@index',
        'controller' => 'App\\Http\\Controllers\\HierarchieController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'hierarchie.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'hierarchie.import-excel' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/hierarchie-chu/import-excel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\HierarchieController@importExcel',
        'controller' => 'App\\Http\\Controllers\\HierarchieController@importExcel',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'hierarchie.import-excel',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'hierarchie.export-json' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/hierarchie-chu/export-json',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\HierarchieController@exportJson',
        'controller' => 'App\\Http\\Controllers\\HierarchieController@exportJson',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'hierarchie.export-json',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'hierarchie.export-excel' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/hierarchie-chu/export-excel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\HierarchieController@exportExcel',
        'controller' => 'App\\Http\\Controllers\\HierarchieController@exportExcel',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'hierarchie.export-excel',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.live-metrics' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/live-metrics',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\BiomedDataController@liveMetrics',
        'controller' => 'App\\Http\\Controllers\\BiomedDataController@liveMetrics',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard.live-metrics',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'markets.equipments' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/marches-equipements',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\BiomedDataController@marketsEquipments',
        'controller' => 'App\\Http\\Controllers\\BiomedDataController@marketsEquipments',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'markets.equipments',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'markets.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/marches-equipements/{market}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\BiomedDataController@showMarket',
        'controller' => 'App\\Http\\Controllers\\BiomedDataController@showMarket',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'markets.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'markets.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/marches-equipements/{market}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\BiomedDataController@editMarket',
        'controller' => 'App\\Http\\Controllers\\BiomedDataController@editMarket',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'markets.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'markets.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'dashboard/marches-equipements/{market}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\BiomedDataController@updateMarket',
        'controller' => 'App\\Http\\Controllers\\BiomedDataController@updateMarket',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'markets.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'markets.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'dashboard/marches-equipements/{market}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\BiomedDataController@destroyMarket',
        'controller' => 'App\\Http\\Controllers\\BiomedDataController@destroyMarket',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'markets.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'markets.equipments.import-excel' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/marches-equipements/import-excel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\BiomedDataController@importMarketsEquipmentsExcel',
        'controller' => 'App\\Http\\Controllers\\BiomedDataController@importMarketsEquipmentsExcel',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'markets.equipments.import-excel',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'markets.equipments.update-equipment' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'dashboard/marches-equipements/equipment/{equipment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\BiomedDataController@updateMarketEquipment',
        'controller' => 'App\\Http\\Controllers\\BiomedDataController@updateMarketEquipment',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'markets.equipments.update-equipment',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'markets.equipments.line.quick-action' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'dashboard/marches-equipements/line/{line}/quick-action',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\BiomedDataController@quickActionMarketImportLine',
        'controller' => 'App\\Http\\Controllers\\BiomedDataController@quickActionMarketImportLine',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'markets.equipments.line.quick-action',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'markets.equipments.line.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'dashboard/marches-equipements/line/{line}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\BiomedDataController@destroyMarketImportLine',
        'controller' => 'App\\Http\\Controllers\\BiomedDataController@destroyMarketImportLine',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'markets.equipments.line.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'equipements' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/equipements',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@index',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'equipements',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'equipments.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/equipements/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@create',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'equipments.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'equipements.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/equipements/{id}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@edit',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'equipements.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'equipements.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/equipements',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@store',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'equipements.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'equipements.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'dashboard/equipements/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@update',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'equipements.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'equipements.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'dashboard/equipements/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@destroy',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'equipements.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'equipements.import-excel' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/equipements/import-excel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@importExcelFile',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@importExcelFile',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'equipements.import-excel',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'equipements.export.excel' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/equipements/export/excel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@exportExcel',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@exportExcel',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'equipements.export.excel',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'equipements.export.pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/equipements/export/pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@exportPdf',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@exportPdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'equipements.export.pdf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'formations.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/equipements/formations',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@formations',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@formations',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'formations.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'formations.import-pdf' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/equipements/formations/import-pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@importFormationPdf',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@importFormationPdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'formations.import-pdf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'formations.export-pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/equipements/formations/export-pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@exportFormationsPdf',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@exportFormationsPdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'formations.export-pdf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'equipements.assets.file' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/equipements/assets/{asset}/{type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@designationAssetFile',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@designationAssetFile',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'equipements.assets.file',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'equipements.services' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/equipements/services',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@servicesByZone',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@servicesByZone',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'equipements.services',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'equipements.rooms' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/equipements/salles',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@roomsByService',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@roomsByService',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'equipements.rooms',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'equipements.update-status' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'dashboard/equipements/{id}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@updateStatus',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'equipements.update-status',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'equipements.bulk-update-designation' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/equipements/bulk-update-by-designation',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@bulkUpdateByDesignation',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@bulkUpdateByDesignation',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'equipements.bulk-update-designation',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'equipements.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/equipements/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'equipment.access',
        ),
        'uses' => 'App\\Http\\Controllers\\EquipmentController@show',
        'controller' => 'App\\Http\\Controllers\\EquipmentController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'equipements.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'interventions' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/interventions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\InterventionController@index',
        'controller' => 'App\\Http\\Controllers\\InterventionController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'interventions',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'interventions.codes' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/interventions/codes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\InterventionController@codes',
        'controller' => 'App\\Http\\Controllers\\InterventionController@codes',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'interventions.codes',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'interventions.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/interventions/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\InterventionController@create',
        'controller' => 'App\\Http\\Controllers\\InterventionController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'interventions.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'interventions.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/interventions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\InterventionController@store',
        'controller' => 'App\\Http\\Controllers\\InterventionController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'interventions.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'interventions.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/interventions/{id}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\InterventionController@edit',
        'controller' => 'App\\Http\\Controllers\\InterventionController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'interventions.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'interventions.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'dashboard/interventions/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\InterventionController@update',
        'controller' => 'App\\Http\\Controllers\\InterventionController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'interventions.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'interventions.close.form' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/interventions/{id}/cloture',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\InterventionController@closeForm',
        'controller' => 'App\\Http\\Controllers\\InterventionController@closeForm',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'interventions.close.form',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'interventions.close' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/interventions/{id}/cloture',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\InterventionController@close',
        'controller' => 'App\\Http\\Controllers\\InterventionController@close',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'interventions.close',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'interventions.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/interventions/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\InterventionController@show',
        'controller' => 'App\\Http\\Controllers\\InterventionController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'interventions.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mttr-mtbf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/kpi/mttr-mtbf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MttrMtbfController@index',
        'controller' => 'App\\Http\\Controllers\\MttrMtbfController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'mttr-mtbf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mttr-mtbf.data' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/kpi/mttr-mtbf/data',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MttrMtbfController@data',
        'controller' => 'App\\Http\\Controllers\\MttrMtbfController@data',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'mttr-mtbf.data',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reclamations.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/reclamations',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'role:admin,ingenieur,major,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\ComplaintController@index',
        'controller' => 'App\\Http\\Controllers\\ComplaintController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'reclamations.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reclamations.status.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'dashboard/reclamations/{complaint}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'role:admin,ingenieur,major,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\ComplaintController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\ComplaintController@updateStatus',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'missing' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:321:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:102:"fn () => \\redirect()->route(\'reclamations.index\')->with(\'error\', \'Cette réclamation n\\\'existe plus.\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000006150000000000000000";}";s:4:"hash";s:44:"0V8Y/nbVt6ufjk+5aphip9z6t0nS9Wpqguaq5sZ7Xvc=";}}',
        'as' => 'reclamations.status.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'complaint' => '[0-9]+',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.notifications.complaints' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/notifications/complaints',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'role:admin,ingenieur,major,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardNotificationController@complaints',
        'controller' => 'App\\Http\\Controllers\\DashboardNotificationController@complaints',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard.notifications.complaints',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.notifications.complaints.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/notifications/complaints/{complaint}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'role:admin,ingenieur,major,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardNotificationController@showComplaint',
        'controller' => 'App\\Http\\Controllers\\DashboardNotificationController@showComplaint',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'missing' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:355:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:136:"fn () => \\redirect()->route(\'reclamations.index\')->with(\'error\', \'Ancienne réclamation introuvable (peut-être supprimée/archivée).\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000006180000000000000000";}";s:4:"hash";s:44:"qoAPjkk4Az+xq8mycTZ2GbD1FGaKsZAXLcyECsROUvc=";}}',
        'as' => 'dashboard.notifications.complaints.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'complaint' => '[0-9]+',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.notifications.complaints.archive' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/notifications/complaints/archive/{notificationId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'role:admin,ingenieur,major,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardNotificationController@showArchivedComplaint',
        'controller' => 'App\\Http\\Controllers\\DashboardNotificationController@showArchivedComplaint',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard.notifications.complaints.archive',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.notifications.complaints.attachment' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/notifications/complaints/{complaint}/attachments/{index}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'role:admin,ingenieur,major,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardNotificationController@attachment',
        'controller' => 'App\\Http\\Controllers\\DashboardNotificationController@attachment',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'missing' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:346:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:127:"fn () => \\redirect()->route(\'reclamations.index\')->with(\'error\', \'Pièce jointe introuvable pour cette ancienne réclamation.\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"000000000000061a0000000000000000";}";s:4:"hash";s:44:"iIB8DFynu7Bqu06pUXw7lAKzscrqJdsfC9XVqqYZlj0=";}}',
        'as' => 'dashboard.notifications.complaints.attachment',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'complaint' => '[0-9]+',
        'index' => '[0-9]+',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.notifications.complaints.close' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'dashboard/notifications/complaints/{complaint}/close',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'role:admin,ingenieur,major,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardNotificationController@closeComplaint',
        'controller' => 'App\\Http\\Controllers\\DashboardNotificationController@closeComplaint',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'missing' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:344:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:125:"fn () => \\redirect()->route(\'reclamations.index\')->with(\'error\', \'Impossible de clôturer: la réclamation est introuvable.\')";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"000000000000061d0000000000000000";}";s:4:"hash";s:44:"kuLtrbEPq/sA3VKkZZzHJfE186/PWj3tkEwuD2wOsg4=";}}',
        'as' => 'dashboard.notifications.complaints.close',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'complaint' => '[0-9]+',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.notifications.complaints.read-all' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/notifications/complaints/read-all',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
          7 => 'role:admin,ingenieur,major,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardNotificationController@markAllComplaintAsRead',
        'controller' => 'App\\Http\\Controllers\\DashboardNotificationController@markAllComplaintAsRead',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard.notifications.complaints.read-all',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-preventive' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/maintenance-preventive',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\PreventiveMaintenanceController@index',
        'controller' => 'App\\Http\\Controllers\\PreventiveMaintenanceController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-preventive',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-preventive.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/maintenance-preventive/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\PreventiveMaintenanceController@create',
        'controller' => 'App\\Http\\Controllers\\PreventiveMaintenanceController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-preventive.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-preventive.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/maintenance-preventive',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\PreventiveMaintenanceController@store',
        'controller' => 'App\\Http\\Controllers\\PreventiveMaintenanceController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-preventive.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-preventive.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/maintenance-preventive/{maintenance_preventive}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\PreventiveMaintenanceController@edit',
        'controller' => 'App\\Http\\Controllers\\PreventiveMaintenanceController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-preventive.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-preventive.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'dashboard/maintenance-preventive/{maintenance_preventive}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\PreventiveMaintenanceController@update',
        'controller' => 'App\\Http\\Controllers\\PreventiveMaintenanceController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-preventive.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-preventive.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'dashboard/maintenance-preventive/{maintenance_preventive}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\PreventiveMaintenanceController@destroy',
        'controller' => 'App\\Http\\Controllers\\PreventiveMaintenanceController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-preventive.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'techniciens' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/techniciens',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\BiomedDataController@techniciens',
        'controller' => 'App\\Http\\Controllers\\BiomedDataController@techniciens',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'techniciens',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'technicians.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/techniciens/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\TechnicianController@create',
        'controller' => 'App\\Http\\Controllers\\TechnicianController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'technicians.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'pieces' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/pieces',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\SparePartController@index',
        'controller' => 'App\\Http\\Controllers\\SparePartController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'pieces',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'pieces.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/pieces/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\SparePartController@create',
        'controller' => 'App\\Http\\Controllers\\SparePartController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'pieces.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'pieces.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/pieces',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\SparePartController@store',
        'controller' => 'App\\Http\\Controllers\\SparePartController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'pieces.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'pieces.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/pieces/{piece}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\SparePartController@edit',
        'controller' => 'App\\Http\\Controllers\\SparePartController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'pieces.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'pieces.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'dashboard/pieces/{piece}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\SparePartController@update',
        'controller' => 'App\\Http\\Controllers\\SparePartController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'pieces.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'pieces.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'dashboard/pieces/{piece}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\SparePartController@destroy',
        'controller' => 'App\\Http\\Controllers\\SparePartController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'pieces.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'rapports' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/rapports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\BiomedDataController@rapports',
        'controller' => 'App\\Http\\Controllers\\BiomedDataController@rapports',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'rapports',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'company-performance.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/rapports/societes-externes/interventions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\CompanyPerformanceReportController@index',
        'controller' => 'App\\Http\\Controllers\\CompanyPerformanceReportController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'company-performance.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'company-performance.export-excel' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/rapports/societes-externes/interventions/export-excel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\CompanyPerformanceReportController@exportExcel',
        'controller' => 'App\\Http\\Controllers\\CompanyPerformanceReportController@exportExcel',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'company-performance.export-excel',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'company-performance.export-pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/rapports/societes-externes/interventions/export-pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\CompanyPerformanceReportController@exportPdf',
        'controller' => 'App\\Http\\Controllers\\CompanyPerformanceReportController@exportPdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'company-performance.export-pdf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sav-tickets.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/sav-externe/tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\ExternalInterventionController@index',
        'controller' => 'App\\Http\\Controllers\\ExternalInterventionController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'sav-tickets.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sav-tickets.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/sav-externe/tickets/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\ExternalInterventionController@create',
        'controller' => 'App\\Http\\Controllers\\ExternalInterventionController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'sav-tickets.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sav-tickets.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/sav-externe/tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\ExternalInterventionController@store',
        'controller' => 'App\\Http\\Controllers\\ExternalInterventionController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'sav-tickets.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sav-tickets.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/sav-externe/tickets/{savTicket}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\ExternalInterventionController@edit',
        'controller' => 'App\\Http\\Controllers\\ExternalInterventionController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'sav-tickets.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sav-tickets.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'dashboard/sav-externe/tickets/{savTicket}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\ExternalInterventionController@update',
        'controller' => 'App\\Http\\Controllers\\ExternalInterventionController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'sav-tickets.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sav-tickets.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'dashboard/sav-externe/tickets/{savTicket}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\ExternalInterventionController@destroy',
        'controller' => 'App\\Http\\Controllers\\ExternalInterventionController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'sav-tickets.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::6PJN2QVEcppNo3RY' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
        2 => 'POST',
        3 => 'PUT',
        4 => 'PATCH',
        5 => 'DELETE',
        6 => 'OPTIONS',
      ),
      'uri' => 'dashboard/sav-externe/tickets-old',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => '\\Illuminate\\Routing\\RedirectController@__invoke',
        'controller' => '\\Illuminate\\Routing\\RedirectController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::6PJN2QVEcppNo3RY',
      ),
      'fallback' => false,
      'defaults' => 
      array (
        'destination' => '/dashboard/sav-externe/tickets',
        'status' => 301,
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-reports.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/rapports/interventions-internes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MaintenanceReportController@index',
        'controller' => 'App\\Http\\Controllers\\MaintenanceReportController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-reports.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-reports.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/rapports/interventions-internes/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MaintenanceReportController@create',
        'controller' => 'App\\Http\\Controllers\\MaintenanceReportController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-reports.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-reports.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/rapports/interventions-internes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MaintenanceReportController@store',
        'controller' => 'App\\Http\\Controllers\\MaintenanceReportController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-reports.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-reports.import-corrective' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/rapports/interventions-internes/import-corrective',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MaintenanceReportController@importCorrectiveFromBilan',
        'controller' => 'App\\Http\\Controllers\\MaintenanceReportController@importCorrectiveFromBilan',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-reports.import-corrective',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-reports.import-corrective-pdf' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/rapports/interventions-internes/import-corrective-pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MaintenanceReportController@importCorrectivePdf',
        'controller' => 'App\\Http\\Controllers\\MaintenanceReportController@importCorrectivePdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-reports.import-corrective-pdf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-reports.delete-corrective-pdf' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'dashboard/rapports/interventions-internes/import-corrective-pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MaintenanceReportController@deleteCorrectivePdf',
        'controller' => 'App\\Http\\Controllers\\MaintenanceReportController@deleteCorrectivePdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-reports.delete-corrective-pdf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-reports.corrective.manual' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/rapports/interventions-internes/maintenance-corrective/manual',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MaintenanceReportController@correctiveManual',
        'controller' => 'App\\Http\\Controllers\\MaintenanceReportController@correctiveManual',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-reports.corrective.manual',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-reports.corrective.template-excel' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/rapports/interventions-internes/maintenance-corrective/template-excel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MaintenanceReportController@correctiveTemplate',
        'controller' => 'App\\Http\\Controllers\\MaintenanceReportController@correctiveTemplate',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-reports.corrective.template-excel',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-reports.corrective.export-excel' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/rapports/interventions-internes/maintenance-corrective/export-excel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MaintenanceReportController@exportCorrectiveExcel',
        'controller' => 'App\\Http\\Controllers\\MaintenanceReportController@exportCorrectiveExcel',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-reports.corrective.export-excel',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-reports.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/rapports/interventions-internes/{maintenanceReport}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MaintenanceReportController@edit',
        'controller' => 'App\\Http\\Controllers\\MaintenanceReportController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-reports.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-reports.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'dashboard/rapports/interventions-internes/{maintenanceReport}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MaintenanceReportController@update',
        'controller' => 'App\\Http\\Controllers\\MaintenanceReportController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-reports.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-reports.submit' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'dashboard/rapports/interventions-internes/{maintenanceReport}/submit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MaintenanceReportController@submit',
        'controller' => 'App\\Http\\Controllers\\MaintenanceReportController@submit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-reports.submit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-reports.validate' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'dashboard/rapports/interventions-internes/{maintenanceReport}/validate',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MaintenanceReportController@validateReport',
        'controller' => 'App\\Http\\Controllers\\MaintenanceReportController@validateReport',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-reports.validate',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-reports.close' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'dashboard/rapports/interventions-internes/{maintenanceReport}/close',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MaintenanceReportController@close',
        'controller' => 'App\\Http\\Controllers\\MaintenanceReportController@close',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-reports.close',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-reports.generate-pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/rapports/interventions-internes/{maintenanceReport}/generate-pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MaintenanceReportController@generatePDF',
        'controller' => 'App\\Http\\Controllers\\MaintenanceReportController@generatePDF',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-reports.generate-pdf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'maintenance-reports.pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/rapports/interventions-internes/{maintenanceReport}/pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\MaintenanceReportController@exportPdf',
        'controller' => 'App\\Http\\Controllers\\MaintenanceReportController@exportPdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'maintenance-reports.pdf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'parametres' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/parametres',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\SettingsController@index',
        'controller' => 'App\\Http\\Controllers\\SettingsController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'parametres',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'parametres.general.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/parametres/general',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\SettingsController@updateGeneral',
        'controller' => 'App\\Http\\Controllers\\SettingsController@updateGeneral',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'parametres.general.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'parametres.panel.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/parametres/panel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\SettingsController@updatePanel',
        'controller' => 'App\\Http\\Controllers\\SettingsController@updatePanel',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'parametres.panel.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'external-companies.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/societes-externes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\ExternalCompanyController@index',
        'controller' => 'App\\Http\\Controllers\\ExternalCompanyController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'external-companies.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'external-companies.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/societes-externes/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\ExternalCompanyController@create',
        'controller' => 'App\\Http\\Controllers\\ExternalCompanyController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'external-companies.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'external-companies.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/societes-externes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\ExternalCompanyController@store',
        'controller' => 'App\\Http\\Controllers\\ExternalCompanyController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'external-companies.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'external-companies.import-excel' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/societes-externes/import-excel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\ExternalCompanyController@importExcel',
        'controller' => 'App\\Http\\Controllers\\ExternalCompanyController@importExcel',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'external-companies.import-excel',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'external-companies.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'dashboard/societes-externes/{company}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\ExternalCompanyController@destroy',
        'controller' => 'App\\Http\\Controllers\\ExternalCompanyController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'external-companies.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'planning.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/planning-societes-externes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\PlanningController@index',
        'controller' => 'App\\Http\\Controllers\\PlanningController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'planning.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'planning.import-excel' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/planning-societes-externes/import-excel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\PlanningController@importContractsExcel',
        'controller' => 'App\\Http\\Controllers\\PlanningController@importContractsExcel',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'planning.import-excel',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'planning.sync-contracts' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/planning-societes-externes/sync-contracts',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\PlanningController@syncFromContracts',
        'controller' => 'App\\Http\\Controllers\\PlanningController@syncFromContracts',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'planning.sync-contracts',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'planning.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/planning-societes-externes/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\PlanningController@create',
        'controller' => 'App\\Http\\Controllers\\PlanningController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'planning.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'planning.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/planning-societes-externes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\PlanningController@store',
        'controller' => 'App\\Http\\Controllers\\PlanningController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'planning.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'planning.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/planning-societes-externes/{planning}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\PlanningController@edit',
        'controller' => 'App\\Http\\Controllers\\PlanningController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'planning.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'planning.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'dashboard/planning-societes-externes/{planning}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\PlanningController@update',
        'controller' => 'App\\Http\\Controllers\\PlanningController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'planning.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'planning.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'dashboard/planning-societes-externes/{planning}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\PlanningController@destroy',
        'controller' => 'App\\Http\\Controllers\\PlanningController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'planning.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'stock.movements' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/stock-movements',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\StockMovementController@movements',
        'controller' => 'App\\Http\\Controllers\\StockMovementController@movements',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'stock.movements',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'stock.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/stock-movements/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\StockMovementController@create',
        'controller' => 'App\\Http\\Controllers\\StockMovementController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'stock.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'stock.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/stock-movements',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\StockMovementController@store',
        'controller' => 'App\\Http\\Controllers\\StockMovementController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'stock.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'stock.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/stock-movements/{movement}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\StockMovementController@edit',
        'controller' => 'App\\Http\\Controllers\\StockMovementController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'stock.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'stock.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'dashboard/stock-movements/{movement}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\StockMovementController@update',
        'controller' => 'App\\Http\\Controllers\\StockMovementController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'stock.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'stock.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'dashboard/stock-movements/{movement}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\StockMovementController@destroy',
        'controller' => 'App\\Http\\Controllers\\StockMovementController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'stock.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'services.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/services',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'as' => 'services.index',
        'uses' => 'App\\Http\\Controllers\\ServiceController@index',
        'controller' => 'App\\Http\\Controllers\\ServiceController@index',
        'namespace' => NULL,
        'prefix' => '/dashboard',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'services.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/services/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'as' => 'services.create',
        'uses' => 'App\\Http\\Controllers\\ServiceController@create',
        'controller' => 'App\\Http\\Controllers\\ServiceController@create',
        'namespace' => NULL,
        'prefix' => '/dashboard',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'services.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/services',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'as' => 'services.store',
        'uses' => 'App\\Http\\Controllers\\ServiceController@store',
        'controller' => 'App\\Http\\Controllers\\ServiceController@store',
        'namespace' => NULL,
        'prefix' => '/dashboard',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'services.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/services/{service}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'as' => 'services.edit',
        'uses' => 'App\\Http\\Controllers\\ServiceController@edit',
        'controller' => 'App\\Http\\Controllers\\ServiceController@edit',
        'namespace' => NULL,
        'prefix' => '/dashboard',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'services.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'dashboard/services/{service}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'as' => 'services.update',
        'uses' => 'App\\Http\\Controllers\\ServiceController@update',
        'controller' => 'App\\Http\\Controllers\\ServiceController@update',
        'namespace' => NULL,
        'prefix' => '/dashboard',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'services.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'dashboard/services/{service}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:admin,manager,major,ingenieur,technicien,technician',
        ),
        'as' => 'services.destroy',
        'uses' => 'App\\Http\\Controllers\\ServiceController@destroy',
        'controller' => 'App\\Http\\Controllers\\ServiceController@destroy',
        'namespace' => NULL,
        'prefix' => '/dashboard',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'operator.defects.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/operator/defects/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:ingenieur,major,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\OperatorDefectController@create',
        'controller' => 'App\\Http\\Controllers\\OperatorDefectController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'operator.defects.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'operator.defects.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard/operator/defects',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:ingenieur,major,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\OperatorDefectController@store',
        'controller' => 'App\\Http\\Controllers\\OperatorDefectController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'operator.defects.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'operator.defects.equipments' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/operator/defects/services/{service}/equipments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'prevent-back-history',
          3 => 'force-password-change',
          4 => 'enforce-account-security',
          5 => 'major-read-only',
          6 => 'role:ingenieur,major,technicien,technician',
        ),
        'uses' => 'App\\Http\\Controllers\\OperatorDefectController@equipmentsByService',
        'controller' => 'App\\Http\\Controllers\\OperatorDefectController@equipmentsByService',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'operator.defects.equipments',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::UUGkLeOFI9u01giE' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/user',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
        ),
        'uses' => 'O:47:"Laravel\\SerializableClosure\\SerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Signed":2:{s:12:"serializable";s:297:"O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:79:"function (\\Illuminate\\Http\\Request $request) {
    return $request->user();
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000005d70000000000000000";}";s:4:"hash";s:44:"cqmkUYeL9RZxx75w8lEWy3jS59Fg0aBe+f3vRZUm+2M=";}}',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::UUGkLeOFI9u01giE',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
  ),
)
);
