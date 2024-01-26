<?php

namespace App\Providers;

use App\User;
use Carbon\Carbon;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Buyer' => 'App\Policies\BuyerPolicy',
        'App\Seller' => 'App\Policies\SellerPolicy',
        'App\User' => 'App\Policies\UserPolicy',
        'App\Transaction' => 'App\Policies\TransactionPolicy',
        'App\Product' => 'App\Policies\ProductPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('admin-action', function($user) {
            return $user->admin == User::ADMIN_USER;
        });

        // Passport::routes();
        Passport::routes(function($router) {
            $router->forAuthorization();
            $router->forAccessTokens();
            $router->forTransientTokens();
            $router->forClients();
            $router->forPersonalAccessTokens();

            Route::post('/token', [
                'uses' => 'AccessTokenController@issueToken',
                'as' => 'passport.token',
                'middleware' => 'api',
            ]);

        });

        // Passport::tokensExpireIn(Carbon::now()->addSeconds(40));
        // Passport::tokensExpireIn(Carbon::now()->addMinutes(10080));
        // Passport::tokensExpireIn(Carbon::now()->addDays(60));
        // Passport::refreshTokensExpireIn(Carbon::now()->addDays(60));
        Passport::enableImplicitGrant();

        // scopes
        Passport::tokensCan([
            'purchase-product' => 'Create a new transaction for a specific product',
            'manage-products' => 'Create, reade, update and delete products (CRUD)',
            'manage-account' => 'Read your account data, id, name, email, if verified, and if admin,  (cannot read password). Modify your account data (email and password). Cannot delete your account',
            'read-general' => 'Read general information like purchasing categories, purchased products, selling products, selling categories, your transactions (purchases and sales)',
        ]);
    }
}
