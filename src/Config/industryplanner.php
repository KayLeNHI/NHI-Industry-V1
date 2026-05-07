<?php

declare(strict_types=1);

return [
    'default_source' => 'character',

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
