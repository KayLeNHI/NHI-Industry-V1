<?php

declare(strict_types=1);

return [
    'industryplanner' => [
        'permission' => 'industryplanner.view',
        'name' => 'Industry Planner',
        'icon' => 'fas fa-industry',
        'route_segment' => 'industry-planner',
        'entries' => [
            [
                'name' => 'Manufacturing',
                'icon' => 'fas fa-cogs',
                'route' => 'industryplanner::index',
            ],
        ],
    ],
];
