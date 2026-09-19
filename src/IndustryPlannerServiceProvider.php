<?php

declare(strict_types=1);

namespace Kayle\Seat\IndustryPlanner;

use Seat\Services\AbstractSeatPlugin;

class IndustryPlannerServiceProvider extends AbstractSeatPlugin
{
    public function boot(): void
    {
        $this->add_routes();

        $this->loadViewsFrom(__DIR__ . '/resources/views', 'industryplanner');
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'industryplanner');

        $this->publishes([
            __DIR__ . '/Config/industryplanner.php' => config_path('industryplanner.php'),
        ], 'config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/industryplanner.php', 'industryplanner');
        $this->mergeConfigFrom(__DIR__ . '/Config/package.sidebar.php', 'package.sidebar');

        $this->registerPermissions(
            __DIR__ . '/Config/Permissions/industryplanner.permissions.php',
            'industryplanner'
        );
    }

    public function add_routes(): void
    {
        if (! $this->app->routesAreCached()) {
            include __DIR__ . '/Http/routes.php';
        }
    }

    public function getName(): string
    {
        return 'Industry Planner';
    }

    public function getPackageRepositoryUrl(): string
    {
        return 'https://github.com/KayLeNHI/NHI-Industry-V1';
    }

    public function getPackagistPackageName(): string
    {
        return 'kayle/seat-industry-planner';
    }

    public function getPackagistVendorName(): string
    {
        return 'kayle';
    }
}
