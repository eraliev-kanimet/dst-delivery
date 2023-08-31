<?php

namespace Database\Seeders\Traits;

use App\Models\AttrKey;
use App\Models\Store;

trait ProductAttributeSeeder
{
    protected array $attributes_type1 = [
        'weight',
        'ingredients',
        'calories',
        'proteins',
        'fats',
        'carbohydrates',
        'volume',
        'nutritional_and_energy_value',
        'size',
        'country_of_production',
        'neckline',
        'height_type',
        'insulation',
        'collection',
        'care',
        'lining_material',
        'color',
        'ram',
        'storage',
        'display',
        'memory',
        'camera',
        'height',
        'capacity',
        'temperature_zones',
        'max_load',
        'power',
        'pump_pressure',
        'suction_power',
        'bowl_capacity',
    ];

    protected array $attributes_type2 = [
        'size_on_model',
        'processor',
    ];

    public function createAttributes(Store $store): void
    {
        foreach ($this->attributes_type1 as $value) {
            $this->createAttribute($value, $store);
        }

        foreach ($this->attributes_type2 as $value) {
            $this->createAttribute($value, $store, false);
        }
    }

    protected function createAttribute(string $value, Store $store, bool $translatable = true): void
    {
        $name = [];

        foreach ($store->locales as $locale) {
            $name[$locale] = __("common.attributes.$value", locale: $locale);
        }

        AttrKey::firstOrCreate([
            'slug' => $value,
            'store_id' => $store->id,
        ], [
            'slug' => $value,
            'store_id' => $store->id,
            'name' => $name,
            'translatable' => $translatable,
        ]);
    }
}
