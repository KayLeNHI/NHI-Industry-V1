# SeAT Industry Planner

SeAT Industry Planner is a SeAT 5 plugin that reads blueprints and assets already synced by SeAT from ESI, then calculates what can be manufactured from those materials.

It does not manage ESI tokens itself. SeAT must already be syncing:

- Character or corporation assets
- Character or corporation blueprints
- EVE SDE industry tables

## Features

- Aggregates available assets by type ID.
- Reads owned character and corporation blueprints.
- Uses `industryActivityMaterials` and `industryActivityProducts` for manufacturing.
- Applies blueprint material efficiency to material requirements.
- Limits blueprint copies by remaining runs.
- Shows manufacturable runs, limiting material, missing materials, and product names.

## Installation

For a local SeAT Docker development install, place this package under your mounted `packages` directory and add it to SeAT's Composer override.

Example `packages/override.json` entry:

```json
{
  "autoload": {
    "psr-4": {
      "Local\\Seat\\IndustryPlanner\\": "local/seat-industry-planner/src/"
    }
  },
  "extra": {
    "laravel": {
      "providers": [
        "Local\\Seat\\IndustryPlanner\\IndustryPlannerServiceProvider"
      ]
    }
  }
}
```

For a normal Composer install after publishing this package:

```bash
composer require local/seat-industry-planner
php artisan vendor:publish --provider="Local\\Seat\\IndustryPlanner\\IndustryPlannerServiceProvider"
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

## Notes

The first version applies blueprint ME only. It does not yet apply structure rig bonuses, system cost index, job taxes, facility material modifiers, or reaction formulas.
