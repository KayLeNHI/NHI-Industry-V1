<?php

declare(strict_types=1);

namespace Kayle\Seat\IndustryPlanner\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Kayle\Seat\IndustryPlanner\Services\ManufacturingPlanner;

class IndustryPlannerController extends Controller
{
    public function __construct(
        private readonly ManufacturingPlanner $planner
    ) {
    }

    public function index(Request $request)
    {
        $source = $request->string('source', config('industryplanner.default_source', 'character'))->toString();
        $owner_id = $request->integer('owner_id');

        $characters = $this->charactersForUser($request->user());
        $corporations = $this->corporationsForUser($request->user());

        $available_owner_ids = array_map('intval', array_keys($source === 'corporation' ? $corporations : $characters));
        $owner_ids = $owner_id > 0 ? [$owner_id] : $available_owner_ids;

        $plan = ! empty($owner_ids)
            ? $this->planner->forOwners($source, $owner_ids)
            : [
                'source' => $source,
                'owner_id' => $owner_id,
                'owner_ids' => [],
                'materials' => [],
                'builds' => collect(),
                'summary' => ['blueprints' => 0, 'materials' => 0, 'buildable' => 0],
            ];

        return view('industryplanner::index', [
            'source' => $source,
            'owner_id' => $owner_id,
            'owner_ids' => $owner_ids,
            'characters' => $characters,
            'corporations' => $corporations,
            'plan' => $plan,
        ]);
    }

    private function charactersForUser($user): array
    {
        if (! $user || ! method_exists($user, 'characters')) {
            return [];
        }

        return $user->characters()
            ->select(['character_id', 'name'])
            ->orderBy('name')
            ->pluck('name', 'character_id')
            ->all();
    }

    private function corporationsForUser($user): array
    {
        if (! $user || ! method_exists($user, 'characters')) {
            return [];
        }

        return $user->characters()
            ->whereNotNull('corporation_id')
            ->select(['corporation_id'])
            ->orderBy('corporation_id')
            ->pluck('corporation_id')
            ->unique()
            ->mapWithKeys(fn ($corporation_id) => [
                (int) $corporation_id => sprintf('Corporation %d', $corporation_id),
            ])
            ->all();
    }
}
