<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Store\ProductIndexRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
use App\Service\ApiProductService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    public function __construct(
        protected ApiProductService $apiProductService
    )
    {}

    public function index(ProductIndexRequest $request)
    {
        $store = Store::current();
        $locale = config('app.locale');

        ProductResource::$locale = $locale;

        $this->apiProductService->setStoreId($store->id);
        $this->apiProductService->setCategoryId($request->get('category_id'));
        $this->apiProductService->setCategories($store->categories);

        return ProductResource::collection($this->apiProductService->all($request, $locale));
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
