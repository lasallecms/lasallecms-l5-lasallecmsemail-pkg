<?php 

namespace Lasallecrm\Lasallecrmemail;

/**
 *
 * Email handling package for the LaSalle Customer Relationship Management package.
 *
 * Based on the Laravel 5 Framework.
 *
 * Copyright (C) 2015 - 2016  The South LaSalle Trading Corporation
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @package    Eail handling package for the LaSalle Customer Relationship Management package
 * @link       http://LaSalleCRM.com
 * @copyright  (c) 2015 - 2016, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *
 */

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;


/**
 * This is the Specialized inbound email handling service provider class.
 *
 * @author Bob Bloom <info@southlasalle.com>
 */
class LasallecrmemailServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot() {
        //$this->setupConfiguration();

        $this->setupRoutes($this->app->router);

        //$this->setupTranslations();

        $this->setupViews();

        //$this->setupAssets();

        //$this->setupMigrations();
        //$this->setupSeeds();

        //$this->bindInterfacesToImplementations();
    }


    /**
     * Setup the Configuration.
     *
     * @return void
     */
    protected function setupConfiguration() {
        $configuration = realpath(__DIR__.'/../config/lasallecrmemail.php');

        $this->publishes([
            $configuration => config_path('lasallecrmemail.php'),
        ]);
    }


    /**
     * Setup the Migrations.
     *
     * @return void
     */
    protected function setupMigrations() {
        $migrations = realpath(__DIR__.'/../database/migrations');

        $this->publishes([
            $migrations    => $this->app->databasePath() . '/migrations',
        ]);
    }


    /**
     * Setup the Seeds.
     *
     * @return void
     */
    protected function setupSeeds() {
        $seeds = realpath(__DIR__.'/../database/seeds');

        $this->publishes([
            $seeds    => $this->app->databasePath() . '/seeds',
        ]);
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->registerLasallecrmemail();
    }


    /**
     * Register the application bindings.
     *
     * @return void
     */
    private function registerLasallecrmemail() {
        $this->app->bind('todo', function($app) {
            return new Lasallecrmemail($app);
        });
    }


    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function setupRoutes(Router $router) {
        $router->group(['namespace' => 'Lasallecrm\Lasallecrmemail\Http\Controllers'], function($router)
        {
            require __DIR__.'/Http/routes.php';
        });
    }


    /**
     * Define the views for the application.
     *
     * @return void
     */
    public function setupViews() {
        $this->loadViewsFrom(__DIR__.'/../views', 'lasallecrmemail');

        $this->publishes([
            __DIR__.'/../views' => base_path('resources/views/vendor/lasallecrmemail'),
        ]);
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return array('lasallecrmemail');
    }


    /**
     * Bind interfaces to implementations
     *
     * https://laravel.com/docs/5.1/container#binding-interfaces-to-implementations
     *
     * @return void
     */
    public function bindInterfacesToImplementations() {

        $this->app->bind(
            'Lasallecrm\Lasallecrmemail\Contracts\InboundWebhookProcessing',
            'Lasallecrm\Lasallecrmemail\Processing\MailgunInboundWebhookProcessing'
        );
    }
}