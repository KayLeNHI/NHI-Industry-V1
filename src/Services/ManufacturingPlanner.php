<?php

declare(strict_types=1);

namespace Local\Seat\IndustryPlanner\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ManufacturingPlanner
{
    public function forOwner(string $source, int $owner_id): array
    {
        return $this->forOwners($source, [$owner_id]);
    }

    public function forOwners(string $source, array $owner_ids): array
    {
        $source = $source === 'corporation' ? 'corporation' : 'character';
        $owner_ids = collect($owner_ids)
            ->map(fn ($owner_id) => (int) $owner_id)
            ->filter(fn ($owner_id) => $owner_id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($owner_ids)) {
            return [
                'source' => $source,
                'owner_id' => 0,
                'owner_ids' => [],
                'materials' => collect(),
                'builds' => collect(),
                'summary' => ['blueprints' => 0, 'materials' => 0, 'buildable' => 0],
            ];
        }

        $materials = $this->loadMaterialPool($source, $owner_ids);
        $blueprints = $this->loadBlueprints($source, $owner_ids);

        $builds = $blueprints
            ->map(fn ($blueprint) => $this->buildPlanForBlueprint($blueprint, $materials))
            ->filter()
            ->sortBy([
                ['buildable_runs', 'desc'],
                ['product_name', 'asc'],
            ])
            ->values();

        return [
            'source' => $source,
            'owner_id' => count($owner_ids) === 1 ? $owner_ids[0] : 0,
            'owner_ids' => $owner_ids,
            'materials' => $materials,
            'builds' => $builds,
            'summary' => [
                'blueprints' => $blueprints->count(),
                'materials' => $materials->count(),
                'buildable' => $builds->where('buildable_runs', '>', 0)->count(),
            ],
        ];
    }

    private function loadMaterialPool(string $source, array $owner_ids): Collection
    {
        $table = $this->table($source === 'corporation' ? 'corporation_assets' : 'character_assets');
        $owner_column = $this->column($source === 'corporation' ? 'corporation_asset_owner_id' : 'asset_owner_id');
        $type_column = $this->column('type_id');
        $quantity_column = $this->column('quantity');

        return DB::table($table)
            ->select($type_column, DB::raw(sprintf('SUM(%s) as total_quantity', $quantity_column)))
            ->whereIn($owner_column, $owner_ids)
            ->where($quantity_column, '>', 0)
            ->groupBy($type_column)
            ->pluck('total_quantity', $type_column)
            ->map(fn ($quantity) => (int) $quantity);
    }

    private function loadBlueprints(string $source, array $owner_ids): Collection
    {
        $table = $this->table($source === 'corporation' ? 'corporation_blueprints' : 'character_blueprints');
        $owner_column = $this->column($source === 'corporation' ? 'corporation_blueprint_owner_id' : 'blueprint_owner_id');

        return DB::table($table)
            ->whereIn($owner_column, $owner_ids)
            ->get();
    }

    private function buildPlanForBlueprint(object $blueprint, Collection $materials): ?array
    {
        $type_id = (int) $blueprint->{$this->column('type_id')};
        $me = max(0, min(10, (int) ($blueprint->{$this->column('material_efficiency')} ?? 0)));
        $blueprint_quantity = (int) ($blueprint->{$this->column('quantity')} ?? 1);
        $blueprint_runs = (int) ($blueprint->{$this->column('runs')} ?? -1);

        $product = $this->manufacturingProduct($type_id);
        $required = $this->manufacturingMaterials($type_id);

        if (! $product || $required->isEmpty()) {
            return null;
        }

        $requirements = $required->map(function ($row) use ($materials, $me): array {
            $material_type_id = (int) $row->material_type_id;
            $base_quantity = (int) $row->quantity;
            $required_quantity = $this->materialQuantityAfterMe($base_quantity, $me);
            $available_quantity = (int) ($materials->get($material_type_id, 0));

            return [
                'type_id' => $material_type_id,
                'name' => $this->typeName($material_type_id),
                'required_per_run' => $required_quantity,
                'available' => $available_quantity,
                'possible_runs' => $required_quantity > 0 ? intdiv($available_quantity, $required_quantity) : 0,
                'missing_for_one_run' => max(0, $required_quantity - $available_quantity),
            ];
        });

        $material_runs = (int) $requirements->min('possible_runs');
        $copy_runs = $this->remainingBlueprintRuns($blueprint_quantity, $blueprint_runs);
        $buildable_runs = min($material_runs, $copy_runs);

        return [
            'blueprint_type_id' => $type_id,
            'blueprint_name' => $this->typeName($type_id),
            'product_type_id' => (int) $product->product_type_id,
            'product_name' => $this->typeName((int) $product->product_type_id),
            'product_quantity_per_run' => (int) $product->quantity,
            'material_efficiency' => $me,
            'blueprint_quantity' => $blueprint_quantity,
            'blueprint_runs' => $blueprint_runs,
            'max_blueprint_runs' => $copy_runs,
            'buildable_runs' => $buildable_runs,
            'limiting_material' => $requirements->sortBy('possible_runs')->first(),
            'requirements' => $requirements->values(),
        ];
    }

    private function manufacturingProduct(int $blueprint_type_id): ?object
    {
        $sde = config('industryplanner.sde_columns');

        return DB::table($this->table('industry_activity_products'))
            ->select([
                $sde['product_type_id'] . ' as product_type_id',
                $sde['quantity'] . ' as quantity',
            ])
            ->where($sde['blueprint_type_id'], $blueprint_type_id)
            ->where($sde['activity_id'], $this->activity('manufacturing'))
            ->first();
    }

    private function manufacturingMaterials(int $blueprint_type_id): Collection
    {
        $sde = config('industryplanner.sde_columns');

        return DB::table($this->table('industry_activity_materials'))
            ->select([
                $sde['material_type_id'] . ' as material_type_id',
                $sde['quantity'] . ' as quantity',
            ])
            ->where($sde['blueprint_type_id'], $blueprint_type_id)
            ->where($sde['activity_id'], $this->activity('manufacturing'))
            ->get();
    }

    private function typeName(int $type_id): string
    {
        static $cache = [];

        if (array_key_exists($type_id, $cache)) {
            return $cache[$type_id];
        }

        $sde = config('industryplanner.sde_columns');

        $cache[$type_id] = DB::table($this->table('types'))
            ->where($sde['blueprint_type_id'], $type_id)
            ->value($sde['type_name']) ?? sprintf('Type %d', $type_id);

        return $cache[$type_id];
    }

    private function materialQuantityAfterMe(int $base_quantity, int $material_efficiency): int
    {
        return max(1, (int) ceil($base_quantity * (1 - ($material_efficiency / 100))));
    }

    private function remainingBlueprintRuns(int $quantity, int $runs): int
    {
        if ($quantity === -2) {
            return max(0, $runs);
        }

        if ($quantity > 0 && $runs > 0) {
            return $quantity * $runs;
        }

        return PHP_INT_MAX;
    }

    private function table(string $key): string
    {
        return config(sprintf('industryplanner.tables.%s', $key));
    }

    private function column(string $key): string
    {
        return config(sprintf('industryplanner.columns.%s', $key));
    }

    private function activity(string $key): int
    {
        return (int) config(sprintf('industryplanner.activity_ids.%s', $key));
    }
}
