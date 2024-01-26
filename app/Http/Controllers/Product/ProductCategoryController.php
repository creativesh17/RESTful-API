<?php

namespace App\Http\Controllers\Product;

use App\Category;
use App\Http\Controllers\ApiController;
use App\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductCategoryController extends ApiController
{
    public function __construct() {
        $this->middleware('client.credentials')->only(['index']);
        $this->middleware('auth:api')->except(['index']);
        $this->middleware('scope:manage-products')->except(['index']);
        $this->middleware('can:add-category,product')->except(['update']);
        $this->middleware('can:delete-category,product')->except(['destroy']);
    }

    public function index(Product $product) {
        $categories = $product->categories;
        return $this->showAll($categories);
    }

    public function update(Request $request, Product $product, Category $category) {
        $product->categories()->syncWithoutDetaching([$category->id]);
        return $this->showAll($product->categories);
    }

    public function destroy(Product $product, Category $category) {
        if(!$product->categories()->find($category->id)) {
            return $this->errorResponse('The specified category is not a category of this product.', Response::HTTP_NOT_FOUND);
        }

        $product->categories()->detach($category->id);

        return $this->showAll($product->categories);

    }
}
