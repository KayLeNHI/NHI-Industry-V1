<?php

declare(strict_types=1);

return [
    'default_source' => 'character',

    'esi' => [
        'base_url' => 'https://esi.evetech.net',
        'datasource' => 'tranquility',
        'compatibility_date' => '2026-09-19',
        'required_scopes' => [
            'character_assets' => 'esi-assets.read_assets.v1',
            'corporation_assets' => 'esi-assets.read_corporation_assets.v1',
            'character_blueprints' => 'esi-characters.read_blueprints.v1',
            'corporation_blueprints' => 'esi-corporations.read_blueprints.v1',
        ],
        'endpoints' => [
            'character_assets' => '/characters/{character_id}/assets/',
            'character_blueprints' => '/characters/{character_id}/blueprints/',
            'corporation_assets' => '/corporations/{corporation_id}/assets/',
            'corporation_blueprints' => '/corporations/{corporation_id}/blueprints/',
        ],
    ],

    'tables' => [
        'character_assets' => 'character_assets',
        'corporation_assets' => 'corporation_assets',
        'character_blueprints' => 'character_blueprints',
        'corporation_blueprints' => 'corporation_blueprints',
        'industry_activity_materials' => 'industryActivityMaterials',
        'industry_activity_products' => 'industryActivityProducts',
        'types' => 'invTypes',
    ],

    'columns' => [
        'asset_owner_id' => 'character_id',
        'corporation_asset_owner_id' => 'corporation_id',
        'blueprint_owner_id' => 'character_id',
        'corporation_blueprint_owner_id' => 'corporation_id',
        'type_id' => 'type_id',
        'quantity' => 'quantity',
        'material_efficiency' => 'material_efficiency',
        'runs' => 'runs',
    ],

    'sde_columns' => [
        'blueprint_type_id' => 'typeID',
        'activity_id' => 'activityID',
        'material_type_id' => 'materialTypeID',
        'product_type_id' => 'productTypeID',
        'quantity' => 'quantity',
        'type_name' => 'typeName',
    ],

    'activity_ids' => [
        'manufacturing' => 1,
    ],
];
