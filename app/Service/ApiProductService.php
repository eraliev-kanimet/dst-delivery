<?php

namespace App\Service;

use App\Http\Requests\Api\Store\ProductIndexRequest;
use App\Models\AttrKey;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiProductService
{
    protected int $store_id = 0;
    protected int $limit = 15;
    protected int $category_id = 0;

    protected string $locale = '';

    protected array $categories = [];
    protected array $attributes = [];

    public function all(ProductIndexRequest $request): LengthAwarePaginator
    {
        $locale = $this->locale;

        $this->setFormattedAttributes(
            $request->has('attributes'), $request->get('attributes', [])
        );

        $query = Product::query()
            ->with([
                'category.category',
                'selections.attr.attrKey',
                'images',
                'content_' . $locale,
                'attr.attrKey',
            ])->whereHas('selections', function (Builder $query) {
                $query->where('is_available', true);
            })->whereStoreId($this->store_id);

        if ($request->has('q')) {
            $words = explode(' ', $request->get('q'));

            $query->whereHas('content_' . $locale, function (Builder $query) use ($words) {
                foreach ($words as $word) {
                    $query->where('name', 'like', "%$word%")
                        ->orWhere('description', 'like', "%$word%");
                }
            });
        } else {
            $query->whereHas('content_' . $locale);
        }

        if (count($this->attributes)) {
            foreach ($this->attributes as $attribute => $value) {
                $query->whereHas('attr', function (Builder $query) use ($attribute, $value) {
                    $query->where('attr_key_id', $attribute)->whereIn($value['key'], $value['value']);
                });
            }
        }

        return $query
            ->whereIn('category_id', $this->getCategories())
            ->orderBy('sorted', 'desc')
            ->orderBy('category_id', 'desc')
            ->paginate($this->limit)
            ->withQueryString();
    }

    public function setCategoryId(?int $category_id): void
    {
        $this->category_id = $category_id ?? 0;
    }

    protected function getCategories()
    {
        if ($this->category_id) {
            $category = Category::find($this->category_id);

            if ($category) {
                return $category->children;
            }

            throw new NotFoundHttpException('Category was not found!');
        }

        return $this->categories;
    }

    public function setCategories(?array $categories): void
    {
        $this->categories = $categories ?? [];
    }

    public function setStoreId(int $store_id): void
    {
        $this->store_id = $store_id;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    protected function setFormattedAttributes(bool $exists, array $attributes): void
    {
        if ($exists) {
            $locale = $this->locale;

            $arr = [];

            foreach ($attributes as $attribute => $value) {
                $attribute = filter_var($attribute, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                $attribute = AttrKey::whereStoreId($this->store_id)->find($attribute);

                if ($attribute) {
                    $arr[$attribute->id] = [
                        'key' => $attribute->translatable ? "value->$locale" : 'value->default',
                        'value' => array_map('trim', explode('@', $value))
                    ];
                }
            }

            $this->attributes = $arr;
        }
    }
}
