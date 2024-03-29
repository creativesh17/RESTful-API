<?php

namespace App\Http\Controllers\Buyer;

use App\Buyer;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class BuyerController extends ApiController
{
    public function __construct() {
        parent::__construct();
        $this->middleware('scope:read-general')->only(['show']);
        $this->middleware('can:view,buyer')->only(['show']);
    }

    public function index() {
        $this->allowedAdminAction();
        $buyers = Buyer::has('transactions')->get();
        return $this->showAll($buyers);
    }


    public function show(Buyer $buyer) {
        return $this->showOne($buyer);
    }

}
