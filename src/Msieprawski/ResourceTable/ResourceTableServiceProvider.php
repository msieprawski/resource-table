<?php namespace Msieprawski\ResourceTable;

use Illuminate\Support\ServiceProvider;

class ResourceTableServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred
	 *
	 * @var bool
	 */
	protected $defer = false;

    /**
     * Bootstrap the application events
     *
     * @return void
     */
    public function boot()
    {
        $views = __DIR__.'/views';
        $this->loadViewsFrom($views, 'resource-table');

        $this->publishes([
            $views => base_path('resources/views/vendor/msieprawski/resource-table'),
        ]);

        $this->loadTranslationsFrom(__DIR__.'/lang', 'resource-table');
    }

	/**
	 * Register the service provider
	 *
	 * @return void
	 */
	public function register() {}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
    {
        return [];
    }
}
