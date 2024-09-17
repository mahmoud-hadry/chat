<?php

namespace App\Providers;

use Carbon\Carbon;
use Laravel\Passport\Bridge\PersonalAccessGrant;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use League\OAuth2\Server\AuthorizationServer;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
//        $lifetime = new \DateInterval('PT3H'); // For 3hours

//        $this->app->get(AuthorizationServer::class)->enableGrantType(new PersonalAccessGrant(), $lifetime);
    }
}
