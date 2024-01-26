<?php

namespace App\Http\Controllers\Category;

use App\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Transformers\CategoryTransformer;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends ApiController
{
    public function __construct() {
        $this->middleware('client.credentials')->only(['index', 'show']);
        $this->middleware('auth:api')->except(['index', 'show']);
        $this->middleware('transform.input:' . CategoryTransformer::class)->only(['store', 'update']);
    }

    public function index() {
        $categories = Category::all();
        return $this->showAll($categories);
    }


    public function store(Request $request) {
        
        $this->allowedAdminAction();

        $rules = [
            'name' => 'required',
            'description' => 'required',
        ];

        $this->validate($request, $rules);

        $newCategory = Category::create($request->all());

        return $this->showOne($newCategory, 'Success!', Response::HTTP_CREATED);
    }

 
    public function show(Category $category) {
        return $this->showOne($category);
    }


 
    public function update(Request $request, Category $category) {

        $this->allowedAdminAction();

        $category->fill($request->only([
            'name',
            'description',
        ]));

        if($category->isClean()) {
            return $this->errorResponse('You need to specify a different value to update!', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $category->save();

        return $this->showOne($category);
    }


    public function destroy(Category $category) {

        $this->allowedAdminAction();

        $category->delete();

        return $this->deleteResponse($category);
    }
}
