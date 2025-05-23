<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Container\ServiceProvider;
use App\Core\Routing\Router;

class RouteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(Router::class, function ($container) {
            return new Router($container);
        });
    }

    public function boot(): void
    {
        $router = $this->container->get(Router::class);

        $this->loadRoutes($router);
    }

    private function loadRoutes(Router $router): void
    {
        // Public routes
        $router->group(['middleware' => ['guest']], function (Router $router) {
            $router->get('/', 'HomeController@index');
            $router->get('/login', 'Auth\LoginController@showLoginForm');
            $router->post('/login', 'Auth\LoginController@login');
            $router->get('/register', 'Auth\RegisterController@showRegistrationForm');
            $router->post('/register', 'Auth\RegisterController@register');
            $router->get('/click/{id}', 'ClickController@index')->whereNumber('id');
            $router->post('/click/{id}', 'ClickController@process')->whereNumber('id');
        });

        // Authenticated routes
        $router->group(['middleware' => ['auth'], 'prefix' => 'game'], function (Router $router) {
            $router->get('/', 'Game\DashboardController@index');
            $router->post('/remove-protection', 'Game\DashboardController@removeProtection');
            $router->post('/toggle-online', 'Game\DashboardController@toggleOnlineStatus');

            // Profile
            $router->get('/profile/{id?}', 'Game\ProfileController@show')->whereNumber('id');
            $router->get('/profile/edit', 'Game\ProfileController@edit');
            $router->post('/profile/update', 'Game\ProfileController@update');

            // Bank
            $router->get('/bank', 'Game\BankController@index');
            $router->post('/bank/deposit', 'Game\BankController@deposit');
            $router->post('/bank/withdraw', 'Game\BankController@withdraw');

            // Shop
            $router->get('/shop', 'Game\ShopController@index');
            $router->post('/shop/buy', 'Game\ShopController@buy');
            $router->get('/shop/sell', 'Game\ShopController@sell');
            $router->post('/shop/sell', 'Game\ShopController@processSell');

            // Combat
            $router->get('/attack/{id}', 'Game\AttackController@attack')->whereNumber('id');
            $router->get('/list', 'Game\UserListController@index');

            // Messages
            $router->get('/messages', 'Game\MessageController@inbox');
            $router->get('/messages/outbox', 'Game\MessageController@outbox');
            $router->get('/messages/read/{id}', 'Game\MessageController@read')->whereNumber('id');
            $router->get('/messages/compose/{to?}', 'Game\MessageController@compose')->whereNumber('to');
            $router->post('/messages/send', 'Game\MessageController@send');
            $router->post('/messages/delete', 'Game\MessageController@delete');
        });

        // Logout (available for authenticated users)
        $router->post('/logout', 'Auth\LoginController@logout')->middleware('auth');

        // Admin routes
        $router->group(['middleware' => ['auth', 'admin'], 'prefix' => 'admin'], function (Router $router) {
            $router->get('/', 'Admin\AdminDashboardController@index');

            // User management
            $router->get('/users', 'Admin\AdminUserController@index');
            $router->get('/users/{id}', 'Admin\AdminUserController@show')->whereNumber('id');
            $router->match(['GET', 'POST'], '/users/{id}/reset', 'Admin\AdminUserController@reset')->whereNumber('id');
            $router->match(['GET', 'POST'], '/users/{id}/delete', 'Admin\AdminUserController@delete')->whereNumber('id');
            $router->match(['GET', 'POST'], '/users/{id}/donate', 'Admin\AdminUserController@donate')->whereNumber('id');
            $router->match(['GET', 'POST'], '/users/{id}/level', 'Admin\AdminUserController@changeLevel')->whereNumber('id');

            // Message management
            $router->get('/messages', 'Admin\AdminMessageController@index');
            $router->match(['GET', 'POST'], '/messages/mass', 'Admin\AdminMessageController@sendMass');
            $router->match(['GET', 'POST'], '/messages/type', 'Admin\AdminMessageController@sendToType');
            $router->match(['GET', 'POST'], '/messages/country', 'Admin\AdminMessageController@sendToCountry');

            // Settings
            $router->get('/settings', 'Admin\AdminSettingsController@index');
            $router->match(['GET', 'POST'], '/settings/theme', 'Admin\AdminSettingsController@updateTheme');
            $router->match(['GET', 'POST'], '/settings/rules', 'Admin\AdminSettingsController@updateRules');
            $router->match(['GET', 'POST'], '/settings/prices', 'Admin\AdminSettingsController@updatePrices');
            $router->match(['GET', 'POST'], '/settings/game', 'Admin\AdminSettingsController@gameSettings');
            $router->match(['GET', 'POST'], '/settings/maintenance', 'Admin\AdminSettingsController@maintenance');
        });
    }
}