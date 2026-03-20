<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    private string $settingsPath = 'app_settings/general.json';

    public function index()
    {
        $settings = $this->loadSettings();
        $activeTab = request()->string('tab')->toString() ?: session('active_tab', 'general');

        if (!in_array($activeTab, ['general', 'security', 'notifications', 'integrations', 'system'], true)) {
            $activeTab = 'general';
        }

        return view('pages.parametres', [
            'settings' => $settings,
            'activeTab' => $activeTab,
        ]);
    }

    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:120',
            'support_email' => 'required|email|max:150',
            'timezone' => 'required|string|max:100',
            'language' => 'required|string|max:20',
        ]);

        $this->saveSettings($validated);

        return redirect()
            ->route('parametres', ['tab' => 'general'])
            ->with('success', 'Paramètres enregistrés avec succès.')
            ->with('active_tab', 'general');
    }

    public function updatePanel(Request $request)
    {
        $section = $request->input('section');
        $settings = $this->loadSettings();

        if ($section === 'security') {
            $validated = $request->validate([
                'password_min_length' => 'required|integer|min:8|max:32',
                'session_timeout_minutes' => 'required|integer|min:5|max:1440',
                'require_uppercase' => 'nullable|boolean',
                'require_numbers' => 'nullable|boolean',
                'require_symbols' => 'nullable|boolean',
                'force_password_rotation_days' => 'required|integer|min:0|max:365',
            ]);

            $settings = array_merge($settings, [
                'password_min_length' => $validated['password_min_length'],
                'session_timeout_minutes' => $validated['session_timeout_minutes'],
                'require_uppercase' => $request->boolean('require_uppercase'),
                'require_numbers' => $request->boolean('require_numbers'),
                'require_symbols' => $request->boolean('require_symbols'),
                'force_password_rotation_days' => $validated['force_password_rotation_days'],
            ]);
        } elseif ($section === 'notifications') {
            $validated = $request->validate([
                'notifications_email' => 'nullable|boolean',
                'notifications_urgent_interventions' => 'nullable|boolean',
                'notifications_monthly_reports' => 'nullable|boolean',
                'notification_digest_hour' => 'required|integer|min:0|max:23',
            ]);

            $settings = array_merge($settings, [
                'notifications_email' => $request->boolean('notifications_email'),
                'notifications_urgent_interventions' => $request->boolean('notifications_urgent_interventions'),
                'notifications_monthly_reports' => $request->boolean('notifications_monthly_reports'),
                'notification_digest_hour' => $validated['notification_digest_hour'],
            ]);
        } elseif ($section === 'integrations') {
            $validated = $request->validate([
                'smtp_host' => 'nullable|string|max:190',
                'smtp_port' => 'nullable|integer|min:1|max:65535',
                'smtp_username' => 'nullable|string|max:190',
                'external_api_base_url' => 'nullable|url|max:255',
                'external_api_key' => 'nullable|string|max:255',
            ]);

            $settings = array_merge($settings, $validated);
        } elseif ($section === 'system') {
            $validated = $request->validate([
                'date_format' => 'required|string|in:d/m/Y,Y-m-d,m/d/Y',
                'items_per_page' => 'required|integer|min:5|max:200',
                'maintenance_mode_message' => 'nullable|string|max:500',
            ]);

            $settings = array_merge($settings, $validated);
        } else {
            return redirect()
                ->route('parametres', ['tab' => 'general'])
                ->with('error', 'Section de configuration invalide.');
        }

        $this->saveSettings($settings);

        return redirect()
            ->route('parametres', ['tab' => $section])
            ->with('success', 'Configuration mise à jour avec succès.')
            ->with('active_tab', $section);
    }

    private function loadSettings(): array
    {
        if (!Storage::disk('local')->exists($this->settingsPath)) {
            return $this->defaultSettings();
        }

        $content = Storage::disk('local')->get($this->settingsPath);
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            return $this->defaultSettings();
        }

        return array_merge($this->defaultSettings(), $decoded);
    }

    private function saveSettings(array $settings): void
    {
        Storage::disk('local')->put(
            $this->settingsPath,
            json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function defaultSettings(): array
    {
        return [
            'company_name' => 'GST Tanger',
            'support_email' => 'support@gst.ma',
            'timezone' => 'Africa/Casablanca',
            'language' => 'fr',
            'password_min_length' => 12,
            'session_timeout_minutes' => 120,
            'require_uppercase' => true,
            'require_numbers' => true,
            'require_symbols' => true,
            'force_password_rotation_days' => 90,
            'notifications_email' => true,
            'notifications_urgent_interventions' => true,
            'notifications_monthly_reports' => false,
            'notification_digest_hour' => 8,
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_username' => '',
            'external_api_base_url' => '',
            'external_api_key' => '',
            'date_format' => 'd/m/Y',
            'items_per_page' => 25,
            'maintenance_mode_message' => '',
        ];
    }
}
