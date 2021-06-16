<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;

/**
 * Class SettingService
 * @package App\Services
 */
class SettingService
{
    /**
     * @return Collection
     */
    public function allSettings(): Collection
    {
        return Setting::all();
    }

    /**
     * @param string $key
     * @return Setting|null
     */
    public function findByKey(string $key): ?Setting
    {
        return Setting::where('key', $key)->first();
    }

    /**
     * @param array $validated
     */
    public function update(array $validated): void
    {
        foreach ($validated as $key => $value) {
            $setting = $this->findByKey($key);
            if (!$setting) {
                $setting = new Setting;
                $setting->fill([
                    'key' => $key,
                    'value' => $value,
                ]);
            } else {
                $setting->value = $value;
            }
            $setting->save();
        }
    }
}
