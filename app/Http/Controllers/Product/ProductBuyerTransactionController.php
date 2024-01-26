<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\ApiController;
use App\Product;
use App\Transaction;
use App\Transformers\TransactionTransformer;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ProductBuyerTransactionController extends ApiController
{
    public function __construct() {
        parent::__construct();
        $this->middleware('transform.input:' . TransactionTransformer::class)->only(['store']);
        $this->middleware('scope:purchase-product')->only(['store']);
        $this->middleware('can:purchase,buyer')->only(['store']);
    }

    public function store(Request $request, Product $product, User $buyer) {
        $rules = [
            'quantity' => 'required|integer|min:1'
        ];

        $this->validate($request, $rules);

        if($buyer->id == $product->seller_id) {
            return $this->errorResponse('The buyer must be different from the seller', Response::HTTP_CONFLICT);
        }

        if($buyer->verified == User::UNVERIFIED_USER) {
            return $this->errorResponse('The buyer must be a verified user', Response::HTTP_CONFLICT);
        }

        if($product->seller->verified == User::UNVERIFIED_USER) {
            return $this->errorResponse('The seller must be a verified user', Response::HTTP_CONFLICT);
        }

        if(!$product->isAvailable()) {
            return $this->errorResponse('The product is not available', Response::HTTP_CONFLICT);
        }

        if($product->quantity <  $request['quantity']) {
            return $this->errorResponse('The product does not have enough units for this transactions', Response::HTTP_CONFLICT);
        }

        return DB::transaction(function() use ($request, $product, $buyer) {
            $product->quantity -= $request->quantity;
            $product->save();

            $transaction = Transaction::create([
                'quantity' => $request->quantity,
                'buyer_id' => $buyer->id,
                'product_id' => $product->id,
            ]);

            return $this->showOne($transaction, 'Success!', Response::HTTP_CREATED);
        });
    }
}
