<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSelection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductResource extends BaseResource
{
    /**
     * @var Product
     */
    public $resource;

    public function toArray(Request $request): array
    {
        $locale = self::$locale;
        $fallback_locale = self::$fallback_locale;

        return [
            'id' => $this->resource->id,
            'category' => $this->category($this->resource->category),
            'name' => $this->resource->name[$locale] ?? $this->resource->name[$fallback_locale],
            'description' => $this->resource->description[$locale] ?? $this->resource->description[$fallback_locale],
            'is_available' => (bool) $this->resource->is_available,
            'images' => $this->resource->getImages(),
            'properties' => $this->getProperties($this->resource->properties),
            'selections' => $this->getSelections($this->resource->selections),
        ];
    }

    protected function category(?Category $category): ?array
    {
        if ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name[self::$locale] ?? $category->name[self::$fallback_locale],
                'category' => $this->category($category->category),
            ];
        }

        return null;
    }

    protected function getProperties(array $properties): array
    {
        $locale = self::$locale;
        $fallback_locale = self::$fallback_locale;

        $array = [];

        foreach ($properties as $property) {
            $array[] = [
                'name' => $property['name'][$locale] ?? $property['name'][$fallback_locale],
                'properties' => $property['properties'][$locale] ?? $property['properties'][$fallback_locale],
            ];
        }

        return $array;
    }

    protected function getSelections(Collection $selections): array
    {
        $locale = self::$locale;
        $fallback_locale = self::$fallback_locale;

        $array = [];

        /** @var ProductSelection $selection */
        foreach ($selections as $selection) {
            $array[] = [
                'id' => $selection->id,
                'quantity' => $selection->quantity,
                'price' => $selection->price,
                'is_available' => $selection->is_available,
                'properties' => $selection->properties[$locale] ?? $selection->properties[$fallback_locale],
            ];
        }

        return $array;
    }
}
