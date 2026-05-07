<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'Local\Seat\IndustryPlanner\Http\Controllers',
    'prefix' => 'industry-planner',
    'middleware' => ['web', 'auth'],
], function (): void {
    Route::get('/', [
        'as' => 'industryplanner::index',
        'uses' => 'IndustryPlannerController@index',
        'middleware' => 'can:industryplanner.view',
    ]);
});
