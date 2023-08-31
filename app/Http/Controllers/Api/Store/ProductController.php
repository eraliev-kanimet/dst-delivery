<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Store\ProductIndexRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
use App\Service\ApiProductService;

class ProductController extends Controller
{
    public function __construct(
        protected ApiProductService $apiProductService
    )
    {}

    public function index(ProductIndexRequest $request)
    {
        $store = Store::current();

        $this->apiProductService->setStoreId($store->id);
        $this->apiProductService->setLocale(config('app.locale'));
        $this->apiProductService->setLimit($request->get('limit', 15));
        $this->apiProductService->setCategoryId($request->get('category_id'));
        $this->apiProductService->setCategories($store->categories()->pluck('id')->toArray());

        return ProductResource::collection($this->apiProductService->all($request));
    }

    public function show(string $id)
    {
        $product = Product::whereId($id)->whereHas('selections')->first();

        if ($product) {
            return new ProductResource($product);
        }

        return response()->json([], 404);
    }
}
