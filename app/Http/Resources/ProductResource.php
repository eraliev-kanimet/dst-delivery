<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\Content;
use App\Models\Selection;
use App\Service\ProductSelectionService;
use App\Service\ProductService;
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
            'category' => ProductService::new()->category($this->resource->category, $locale),
            'name' => $content->name,
            'description' => $content->description,
            'is_available' => (bool)$this->resource->is_available,
            'images' => getImages($this->resource->images->values),
            'attributes' => $this->getAttributes($this->resource->productAttributes ?? []),
            'selections' => $this->getSelections($this->resource->selections),
            'preview' => $this->resource->preview,
        ];
    }

    public function getAttributes(Collection|array $attributes): array
    {
        $locale = self::$locale;
        $service = ProductService::new();
        $array = [];

        foreach ($attributes as $attribute) {
            $array[] = [
                'attribute' => $attribute->attribute,
                'name' => __('common.attributes.' . $attribute->attribute),
                'value' => $service->getAttributeValue($attribute->type, $attribute->{'value' . $attribute->type}, $locale)
            ];
        }

        return $array;
    }

    protected function getSelections(Collection $selections): array
    {
        $locale = self::$locale;
        $service = ProductSelectionService::new();

        $array = [];

        /** @var Selection $selection */
        foreach ($selections as $selection) {
            $array[] = [
                'id' => $selection->id,
                'quantity' => $selection->quantity,
                'price' => $selection->price,
                'is_available' => $selection->is_available,
                'images' => getImages($selection->images ?? []),
                'attributes' => $service->getAttributes($selection->properties ?? [], $locale),
            ];
        }

        return $array;
    }
}
