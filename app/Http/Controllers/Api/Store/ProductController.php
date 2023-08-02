<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string'],
            'category_id' => ['nullable', 'numeric'],
            'limit' => ['nullable', 'numeric'],
        ]);

        $store = Store::current();
        $locale = config('app.locale');

        ProductResource::$locale = $locale;

        $query = Product::query()
            ->with([
                'category:id,name,category_id',
                'selections',
                'store:id',
                'images',
                'content_' . $locale
            ])->whereHas('selections', function (Builder $query) {
                $query->where('is_available', true);
            })->whereStoreId($store->id);

        $categories = $store->categories;

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

        if ($request->has('category_id')) {
            $category = Category::find($request->get('category_id'));

            if ($category) {
                $categories = $category->children;
            } else {
                throw new NotFoundHttpException('Category was not found!');
            }
        }

        $query->whereIn('category_id', $categories);

        $products = $query
            ->orderBy('sorted', 'desc')
            ->orderBy('category_id', 'desc')
            ->paginate($request->get('limit', 15))
            ->withQueryString();

        return ProductResource::collection($products);
    }

    public function show(Product $product)
    {
        $store = Store::current();

        ProductResource::$locale = config('app.locale');

        if ($product->store_id == $store->id && $product->selections->count()) {
            return new ProductResource($product);
        }

        throw new NotFoundHttpException('Product was not found!');
    }
}
