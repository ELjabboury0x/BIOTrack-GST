<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class AppSettingsService
{
    private string $settingsPath = 'app_settings/general.json';

    public function all(): array
    {
        if (!Storage::disk('local')->exists($this->settingsPath)) {
            return $this->defaults();
        }

        $content = Storage::disk('local')->get($this->settingsPath);
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            return $this->defaults();
        }

        return array_merge($this->defaults(), $decoded);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();

        return $settings[$key] ?? $default;
    }

    public function int(string $key, int $default): int
    {
        return (int) $this->get($key, $default);
    }

    public function bool(string $key, bool $default): bool
    {
        return (bool) $this->get($key, $default);
    }

    private function defaults(): array
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
