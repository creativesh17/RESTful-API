<?php

namespace App\Http\Controllers\Seller;

use App\Seller;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class SellerController extends ApiController
{
    public function __construct() {
        parent::__construct();
        $this->middleware('scope:read-general')->only(['show']);
        $this->middleware('can:view,seller')->only(['show']);
    }

    public function index() {
        $this->allowedAdminAction();
        
        $sellers = Seller::has('products')->get();
        return $this->showAll($sellers);
    }

    public function show(Seller $seller) {
        return $this->showOne($seller);
    }
    
}
