<?php

namespace App\Http\Resources;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\Content;
use App\Models\Selection;
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

        /** @var Content $content */
        $content = $this->resource->{"content_$locale"};

        return [
            'id' => $this->resource->id,
            'category' => $this->category($this->resource->category),
            'name' => $content->name,
            'description' => $content->description,
            'is_available' => (bool)$this->resource->is_available,
            'images' => getImages($this->resource->images->values),
            'attributes' => $this->getAttributes($this->resource->productAttributes ?? []),
            'selections' => $this->getSelections($this->resource->selections),
            'preview' => $this->resource->preview,
        ];
    }

    protected function category(?Category $category): ?array
    {
        if ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name[self::$locale],
                'category' => $this->category($category->category),
            ];
        }

        return null;
    }

    public function getAttributes(Collection|array $attributes): array
    {
        $locale = self::$locale;
        $array = [];

        /** @var Attribute $attribute */
        foreach ($attributes as $attribute) {
            $array[] = [
                'attribute' => $attribute->attribute,
                'name' => __('common.attributes.' . $attribute->attribute),
                'value' => $this->getAttributeValue($attribute->type, $attribute->{'value' . $attribute->type}, $locale)
            ];
        }

        return $array;
    }

    protected function getAttributeValue(int $type, array|string $value, string $locale)
    {
        return match ($type) {
            1 => $value[$locale],
            default => $value
        };
    }

    protected function getSelections(Collection $selections): array
    {
        $locale = self::$locale;

        $array = [];

        /** @var Selection $selection */
        foreach ($selections as $selection) {
            $attributes = [];

            foreach ($selection->properties ?? [] as $attribute) {
                $attributes[] = [
                    'attribute' => $attribute['attribute'],
                    'name' => __('common.attributes.' . $attribute['attribute']),
                    'value' => $this->getAttributeValue($attribute['type'], $attribute['value' . $attribute['type']], $locale)
                ];
            }

            $array[] = [
                'id' => $selection->id,
                'quantity' => $selection->quantity,
                'price' => $selection->price,
                'is_available' => $selection->is_available,
                'images' => getImages($selection->images ?? []),
                'attributes' => $attributes,
            ];
        }

        return $array;
    }
}
