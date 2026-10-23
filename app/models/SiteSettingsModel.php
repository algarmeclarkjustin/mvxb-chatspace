<?php
declare(strict_types=1);

final class SiteSettingsModel
{
    public function __construct(private mysqli $db)
    {
    }

    public function all(): array
    {
        $settings = ['site_name' => 'MVXB ChatSpace', 'site_logo' => ''];
        $statement = $this->db->prepare('SELECT setting_key, setting_value FROM site_settings');
        $statement->execute();
        foreach ($statement->get_result()->fetch_all(MYSQLI_ASSOC) as $setting) {
            $settings[$setting['setting_key']] = $setting['setting_value'];
        }
        return $settings;
    }

    public function saveBranding(string $siteName, ?string $logo): void
    {
        $statement = $this->db->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        $key = 'site_name';
        $statement->bind_param('ss', $key, $siteName);
        $statement->execute();
        if ($logo !== null) {
            $key = 'site_logo';
            $statement->bind_param('ss', $key, $logo);
            $statement->execute();
        }
    }
}
