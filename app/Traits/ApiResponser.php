<?php

namespace App\Traits;

use fractal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponser {
    private function successResponse($data, $message, $status) {
        return response()->json(['data' => $data, 'message' => $message, 'status' => $status]);
    }

    protected function errorResponse($message, $status) {
        return response()->json(['errors' => $message, 'status' => $status]);
    }

    protected function deleteResponse(Model $model, $message = 'Deleted Successfully!', $status = Response::HTTP_NO_CONTENT) {
        // return response()->json(['message' => $message, 'status' => $status]);
        return $this->successResponse($model, $message, $status);
    }

    protected function showAll(Collection $collection, $message = 'Success!', $status = Response::HTTP_OK) {

        if($collection->isEmpty()) {
            return $this->successResponse($collection, $message, $status);    
        }

        $transformer = $collection->first()->transformer;

        $collection = $this->filterData($collection, $transformer);
        $collection = $this->sortData($collection, $transformer);
        $collection = $this->paginate($collection);
        $collection = $this->transformData($collection, $transformer);
        $collection = $this->cacheResponse($collection);
        $collection['message'] = $message;         
        $collection['status'] = $status;   
        

        return response()->json($collection);
    }

    protected function showOne(Model $model, $message = 'Success!', $status = Response::HTTP_OK) {
        $transformer = $model->transformer;
        $model = $this->transformData($model, $transformer);
        $model = $model['data'];
        return $this->successResponse($model, $message, $status);
    }

    protected function showMessage($message = 'Success!', $status = Response::HTTP_OK) {
        return response()->json(['message' => $message, 'status' => $status]);
    }

    protected function filterData(Collection $collection, $transformer) {
        foreach(request()->query() as $query => $value) {
            $attribute = $transformer::originalAttribute($query);

            if(isset($attribute, $value)) {
                $collection = $collection->where($attribute, $value);
            }
        }
        return $collection;
    }

    protected function sortData(Collection $collection, $transformer) {
        if(request()->has('sort_by')) {
            $attribute = $transformer::originalAttribute(request()->sort_by);
            $collection = $collection->sortBy->{$attribute};
        }
        return $collection;
    }

    protected function paginate(Collection $collection) {
        $rules = [
            'per_page' => 'integer|min:2|max:50'
        ];

        Validator::validate(request()->all(), $rules);

        $page = LengthAwarePaginator::resolveCurrentPage();

        $perPage = 15;

        if(request()->has('per_page')) {
            $perPage = (int) request()->per_page;
        }

        $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $paginated->appends(request()->all());

        return $paginated;
    }

    protected function transformData($data, $transformer) {
        $transformation = fractal($data, new $transformer);

        return $transformation->toArray();
    }

    protected function cacheResponse($data) {
        $url = request()->url();
        $queryParams = request()->query();

        ksort($queryParams);

        $queryString = http_build_query($queryParams);

        $fullUrl = "{$url}?{$queryString}";

        return Cache::remember($fullUrl, 30, function() use($data) {
            return $data;
        });

    }
}





