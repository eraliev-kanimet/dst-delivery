<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\Content;
use App\Models\Selection;
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
            'attributes' => $this->resource->attr->map(function ($attr) use ($locale) {
                return [
                    'attribute' => $attr->attr_key_id,
                    'name' => $attr->attrKey->name[$locale],
                    'value' => $attr->attrKey->translatable ? $attr->value[$locale] : $attr->value['default']
                ];
            }),
            'selections' => $this->getSelections($this->resource->selections),
            'preview' => $this->resource->preview,
        ];
    }

    protected function getSelections(Collection $selections): array
    {
        $locale = self::$locale;
        $array = [];

        /** @var Selection $selection */
        foreach ($selections as $selection) {
            $array[] = [
                'id' => $selection->id,
                'quantity' => $selection->quantity,
                'price' => $selection->price,
                'is_available' => $selection->is_available,
                'images' => getImages($selection->images ?? []),
                'attributes' => $selection->attr->map(function ($attr) use ($locale) {
                    return [
                        'attribute' => $attr->attr_key_id,
                        'name' => $attr->attrKey->name[$locale],
                        'value' => $attr->attrKey->translatable ? $attr->value[$locale] : $attr->value['default']
                    ];
                }),
            ];
        }

        return $array;
    }
}
