<?php

namespace App\Http\Controllers;

use App\Services\AppSettingsService;

class AdminSecurityController extends Controller
{
    public function index(AppSettingsService $settingsService)
    {
        return view('pages.admin.security', [
            'settings' => $settingsService->all(),
        ]);
    }
}
