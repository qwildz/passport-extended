<?php
namespace Qwildz\PassportExtended;

use Illuminate\Auth\RequestGuard;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\Guards\TokenGuard;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use Laravel\Passport\Bridge\ScopeRepository;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\ResourceServer;

class PassportExtendedServiceProvider extends PassportServiceProvider
{

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->setupConfig();

        parent::boot();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'passport');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/passport'),
            ], 'passport-views');

            $this->publishes([
                __DIR__.'/../resources/assets/js/components' => base_path('resources/assets/js/components/passport'),
            ], 'passport-components');
        }
    }

    /**
     * Make the authorization service instance.
     *
     * @return AuthorizationServer
     */
    public function makeAuthorizationServer()
    {
        return new AuthorizationServer(
            $this->app->make(Bridge\ClientRepository::class),
            $this->app->make(AccessTokenRepository::class),
            $this->app->make(ScopeRepository::class),
            $this->makeCryptKey('oauth-private.key'),
            app('encrypter')->getKey()
        );
    }

    /**
     * @inheritdoc
     */
    protected function makeGuard(array $config)
    {
        return new RequestGuard(function ($request) use ($config) {
            return (new TokenGuard(
                $this->app->make(ResourceServer::class),
                Auth::createUserProvider($config['provider']),
                $this->app->make(TokenRepository::class),
                $this->app->make(ClientRepository::class),
                $this->app->make('encrypter')
            ))->user($request);
        }, $this->app['request']);
    }

    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../resources/config/passport-extended.php');
        $this->publishes([$source => config_path('passport-extended.php')]);
        $this->mergeConfigFrom($source, 'passport-extended');
    }

    /**
     * @inheritdoc
     */
    protected function registerMigrations()
    {
        parent::registerMigrations();

        if (Passport::$runsMigrations) {
            return $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'passport-migrations');
    }
}