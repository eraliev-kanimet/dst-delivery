<?php

namespace App\Service;

use App\Models\Attribute;

class ProductService
{
    public static function new(): static
    {
        return new self;
    }

    public function getAttributesName(): array
    {
        return [
            'size' => 'Size',
            'color' => 'Color',
            'neckline' => 'Neckline',
            'country_of_production' => 'Country of production',
            'size_on_model' => 'Size on model',
            'height_type' => 'Height type',
            'insulation' => 'Insulation',
            'collection' => 'Collection',
            'care' => 'Care',
            'lining_material' => 'Lining material',
            'ram' => 'RAM',
            'storage' => 'Storage',
            'processor' => 'Processor',
            'display' => 'Display',
            'memory' => 'Memory',
            'camera' => 'Camera',
            'height' => 'Height',
            'capacity' => 'Capacity',
            'temperature_zones' => 'Temperature zones',
            'max_load' => 'Max load',
            'power' => 'Power',
            'pump_pressure' => 'Pump pressure',
            'bowl_capacity' => 'Bowl Capacity',
            'suction_power' => 'Suction power',
        ];
    }

    public function isAttributeType1($value): bool
    {
        return in_array($value, [
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
        ]);
    }

    public function isAttributeType2($value): bool
    {
        return in_array($value, [
            'size_on_model',
            'processor',
        ]);
    }

    public function createAttributes(array $attributes, int $product_id): void
    {
        foreach ($attributes as $attribute) {
            $type = 0;

            if ($this->isAttributeType1($attribute['attribute'])) {
                $type = 1;
            } else if ($this->isAttributeType2($attribute['attribute'])) {
                $type = 2;
            }

            if ($type) {
                Attribute::create([
                    'product_id' => $product_id,
                    'type' => $type,
                    'attribute' => $attribute['attribute'],
                    'value' . $type => $attribute['value' . $type]
                ]);
            }
        }
    }
}
