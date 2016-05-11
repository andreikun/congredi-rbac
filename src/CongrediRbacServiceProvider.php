<?php namespace Congredi\Rbac;

use Congredi\Rbac\Adapters\DatabaseAdapterInterface;
use Congredi\Rbac\Adapters\Fluent\FluentDatabaseAdapter;
use \Illuminate\Support\ServiceProvider;

class CongrediRbacServiceProvider extends ServiceProvider
{
	/**
	 * Boot the service provider.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->setupConfig();
		$this->setupMigrations();
	}

	/**
	 * Setup the config.
	 *
	 * @return void
	 */
	protected function setupConfig()
	{
		$source = realpath(__DIR__ . '/../config/rbac.php');

		if (class_exists('Illuminate\Foundation\Application', false)) {
			$this->publishes([$source => config_path('rbac.php')]);
		}

		$this->mergeConfigFrom($source, 'rbac');
	}

	/**
	 * Setup the migrations.
	 *
	 * @return void
	 */
	protected function setupMigrations()
	{
		$source = realpath(__DIR__ . '/../database/migrations/');

		$this->publishes([$source => database_path('migrations')], 'migrations');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerDatabaseAdapter();
		$this->registerManager();
	}

	/**
	 * Register Database Adapter.
	 */
	public function registerDatabaseAdapter()
	{
		$provider = $this;

		/**
		 * Bind FluentDatabaseAdapter to the IoC Container
		 */
		$this->app->bindShared('rbac.database.adapter', function ($app) use ($provider) {
			$databaseAdapter = new FluentDatabaseAdapter($app['db'], $app['config']->get('rbac'));
			$databaseAdapter->setConnectionName($provider->getConnectionName());

			return $databaseAdapter;
		});

		/**
		 * Bind FluentDatabaseAdapter to the IoC Container
		 */
		$this->app->bindShared(FluentDatabaseAdapter::class, function ($app) use ($provider) {
			return $app['rbac.database.adapter'];
		});

		/**
		 * Bind the interfaces to their implementations
		 */
		$this->app->bind(DatabaseAdapterInterface::class, FluentDatabaseAdapter::class);
	}

	/**
	 * @return string
	 */
	public function getConnectionName()
	{
		return ($this->app['config']->get('rbac.database') !== 'default') ? $this->app['config']->get('rbac.database') : null;
	}

	/**
	 * Register Rbac Manager
	 */
	public function registerManager()
	{
		$this->app->singleton('rbac.manager', function ($app) {
			$manager = new RbacManager($app->make(DatabaseAdapterInterface::class), $this->app['config']->get('rbac.default_roles'));

			return $manager;
		});

		$this->app->singleton(RbacManager::class, function ($app) {
			return $app['rbac.manager'];
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return string[]
	 * @codeCoverageIgnore
	 */
	public function provides()
	{
		return ['rbac.manager'];
	}
}