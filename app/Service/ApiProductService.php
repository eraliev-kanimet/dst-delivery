<?php

namespace App\Service;

use App\Http\Requests\Api\Store\ProductIndexRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiProductService
{
    protected int $store_id = 0;
    protected int $category_id = 0;
    protected array $categories = [];

    public function __construct(
        protected ProductService $service
    )
    {}

    public function all(ProductIndexRequest $request, string $locale): LengthAwarePaginator
    {
        $query = Product::query()
            ->with([
                'category:id,name,category_id',
                'selections',
                'store:id',
                'images',
                'content_' . $locale,
                'productAttributes',
            ])->whereHas('selections', function (Builder $query) {
                $query->where('is_available', true);
            })->whereStoreId($this->store_id);

        if ($request->has('attributes')) {
            $attributes = $this->getFormattedAttributes($request->get('attributes'));

            foreach ($attributes as $attribute) {
                if ($attribute['type'] == 1) {
                    $value = "value1->$locale";
                } else {
                    $value = 'value2';
                }

                if (is_array($attribute['value'])) {
                    $query->whereHas('productAttributes', function (Builder $query) use ($attribute, $value) {
                        $query->where('attribute', $attribute['attribute'])->whereIn($value, $attribute['value']);
                    });

                } else {
                    $query->whereHas('productAttributes', function (Builder $query) use ($attribute, $value) {
                        $query->where('attribute', $attribute['attribute'])->where($value, $attribute['value']);
                    });
                }
            }
        }

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

        return $query
            ->whereIn('category_id', $this->getCategories())
            ->orderBy('sorted', 'desc')
            ->orderBy('category_id', 'desc')
            ->paginate($request->get('limit', 15))
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

    protected function getFormattedAttributes(array $attributes): array
    {
        $formattedAttributes = [];

        foreach ($attributes as $attribute => $value) {
            $type = $this->service->getType($attribute);

            if ($type) {
                $formattedAttributes[] = [
                    'type' => $type,
                    'attribute' => $attribute,
                    'value' => $value
                ];
            }
        }

        return $formattedAttributes;
    }
}
