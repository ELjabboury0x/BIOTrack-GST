<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class TechnicianPlcController extends Controller
{
    public function status()
    {
        $plcStatus = [
            ['name' => 'PLC-01', 'status' => 'online', 'last_seen' => now()->subSeconds(15)],
            ['name' => 'PLC-02', 'status' => 'online', 'last_seen' => now()->subMinute()],
            ['name' => 'PLC-03', 'status' => 'warning', 'last_seen' => now()->subMinutes(3)],
        ];

        return view('pages.technician.plc-status', [
            'plcStatus' => $plcStatus,
        ]);
    }

    public function logs()
    {
        $path = storage_path('logs/laravel.log');
        $lines = [];

        if (File::exists($path)) {
            $content = File::get($path);
            $allLines = preg_split('/\r\n|\r|\n/', $content) ?: [];
            $lines = array_values(array_filter(array_slice($allLines, -120), fn ($line) => trim((string) $line) !== ''));
        }

        return view('pages.technician.plc-logs', [
            'lines' => $lines,
        ]);
    }
}
