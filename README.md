# SeAT Industry Planner

SeAT Industry Planner is a SeAT 5 plugin that reads blueprints and assets already synced by SeAT from ESI, then calculates what can be manufactured from those materials.

It does not manage ESI tokens itself. SeAT must already be syncing:

- Character or corporation assets
- Character or corporation blueprints
- EVE SDE industry tables

The plugin is aligned with SeAT 5, PHP 8.2, Laravel 10, and the current ESI compatibility-date model.

## Features

- Aggregates available assets by type ID.
- Reads owned character and corporation blueprints.
- Uses `industryActivityMaterials` and `industryActivityProducts` for manufacturing.
- Applies blueprint material efficiency using EVE's total-job material rounding.
- Limits blueprint copies by remaining runs.
- Shows manufacturable runs, limiting material, missing materials, and product names.

## Installation

For a local SeAT Docker development install, place this package under your mounted `packages` directory and add it to SeAT's Composer override.

Example `packages/override.json` entry:

```json
{
  "autoload": {
    "psr-4": {
      "Kayle\\Seat\\IndustryPlanner\\": "NHI-Industry-V1/src/"
    }
  },
  "extra": {
    "laravel": {
      "providers": [
        "Kayle\\Seat\\IndustryPlanner\\IndustryPlannerServiceProvider"
      ]
    }
  }
}
```

For a normal Composer install after publishing this package:

```bash
composer require kayle/seat-industry-planner
php artisan vendor:publish --provider="Kayle\\Seat\\IndustryPlanner\\IndustryPlannerServiceProvider"
php artisan cache:clear
```

Grant the `industryplanner.view` permission in SeAT, then open `Industry Planner` from the sidebar.

## Configuration

The plugin defaults to SeAT/EVE SDE table names:

- `character_assets`
- `corporation_assets`
- `character_blueprints`
- `corporation_blueprints`
- `industryActivityMaterials`
- `industryActivityProducts`
- `invTypes`

If your SeAT installation uses different table names, publish and edit the config.

## ESI and SeAT sync requirements

This plugin reads SeAT's synced database data. SeAT must have tokens with these ESI scopes:

- Character assets: `esi-assets.read_assets.v1`
- Corporation assets: `esi-assets.read_corporation_assets.v1`
- Character blueprints: `esi-characters.read_blueprints.v1`
- Corporation blueprints: `esi-corporations.read_blueprints.v1`

The matching ESI routes are:

- `/characters/{character_id}/assets/`
- `/characters/{character_id}/blueprints/`
- `/corporations/{corporation_id}/assets/`
- `/corporations/{corporation_id}/blueprints/`

The config stores the ESI base URL as `https://esi.evetech.net`, datasource as `tranquility`, and compatibility date as `2026-09-19`.

## Notes

This version applies blueprint ME only. It does not yet apply structure rig bonuses, system cost index, job taxes, facility material modifiers, or reaction formulas.
