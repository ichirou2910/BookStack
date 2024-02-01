<?php

namespace BookStack\App\Providers;

use BookStack\Access\ExternalBaseUserProvider;
use BookStack\Access\Guards\AsyncExternalBaseSessionGuard;
use BookStack\Access\Guards\LdapSessionGuard;
use BookStack\Access\Guards\TruesightDevopsSessionGuard;
use BookStack\Access\LdapService;
use BookStack\Access\LoginService;
use BookStack\Access\RegistrationService;
use BookStack\Access\TruesightDevopsService;
use BookStack\Api\ApiTokenGuard;
use BookStack\Permissions\PermissionsRepo;
use BookStack\Users\Models\User;
use BookStack\Users\UserRepo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Password Configuration
        // Changes here must be reflected in ApiDocsGenerate@getValidationAsString.
        Password::defaults(fn () => Password::min(8));

        // Custom guards
        Auth::extend('ts-devops', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);
            $guard = new TruesightDevopsSessionGuard(
                $name,
                $provider,
                $app['session.store'],
                $app[TruesightDevopsService::class],
                $app[PermissionsRepo::class],
                $app[UserRepo::class]
            );
            // When using the remember me functionality of the authentication services we
            // will need to be set the encryption instance of the guard, which allows
            // secure, encrypted cookie values to get generated for those cookies.
            if (method_exists($guard, 'setCookieJar')) {
                $guard->setCookieJar($this->app['cookie']);
            }

            if (method_exists($guard, 'setDispatcher')) {
                $guard->setDispatcher($this->app['events']);
            }

            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
            }

            if (isset($config['remember'])) {
                $guard->setRememberDuration($config['remember']);
            }
            return $guard;
        });

        Auth::extend('api-token', function ($app, $name, array $config) {
            return new ApiTokenGuard($app['request'], $app->make(LoginService::class));
        });

        Auth::extend('ldap-session', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);

            return new LdapSessionGuard(
                $name,
                $provider,
                $app['session.store'],
                $app[LdapService::class],
                $app[RegistrationService::class]
            );
        });

        Auth::extend('async-external-session', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);

            return new AsyncExternalBaseSessionGuard(
                $name,
                $provider,
                $app['session.store'],
                $app[RegistrationService::class]
            );
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        Auth::provider('external-users', function ($app, array $config) {
            return new ExternalBaseUserProvider($config['model']);
        });

        // Bind and provide the default system user as a singleton to the app instance when needed.
        // This effectively "caches" fetching the user at an app-instance level.
        $this->app->singleton('users.default', function () {
            return User::query()->where('system_name', '=', 'public')->first();
        });
    }
}
