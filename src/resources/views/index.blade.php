@extends('web::layouts.grids.12')

@section('title', 'Industry Planner')
@section('page_header', 'Industry Planner')

@section('full')
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Manufacturing From Assets</h3>
    </div>
    <div class="card-body">
      <form method="GET" action="{{ route('industryplanner::index') }}" class="form-inline mb-3">
        <div class="form-group mr-2">
          <label for="source" class="mr-2">Source</label>
          <select name="source" id="source" class="form-control" onchange="this.form.submit()">
            <option value="character" @selected($source === 'character')>Character</option>
            <option value="corporation" @selected($source === 'corporation')>Corporation</option>
          </select>
        </div>

        <div class="form-group mr-2">
          <label for="owner_id" class="mr-2">Owner</label>
          <select name="owner_id" id="owner_id" class="form-control">
            <option value="0" @selected((int) $owner_id === 0)>
              {{ $source === 'corporation' ? 'All Corporations' : 'All Characters' }}
            </option>
            @foreach(($source === 'corporation' ? $corporations : $characters) as $id => $name)
              <option value="{{ $id }}" @selected((int) $owner_id === (int) $id)>{{ $name }}</option>
            @endforeach
          </select>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fas fa-sync-alt"></i> Calculate
        </button>
      </form>

      <div class="row">
        <div class="col-md-4">
          <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-scroll"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Blueprints</span>
              <span class="info-box-number">{{ number_format($plan['summary']['blueprints']) }}</span>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-cubes"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Material Types</span>
              <span class="info-box-number">{{ number_format($plan['summary']['materials']) }}</span>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-industry"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Buildable Blueprints</span>
              <span class="info-box-number">{{ number_format($plan['summary']['buildable']) }}</span>
            </div>
          </div>
        </div>
      </div>

      <table class="table table-hover table-sm">
        <thead>
          <tr>
            <th>Product</th>
            <th>Blueprint</th>
            <th class="text-right">ME</th>
            <th class="text-right">Runs</th>
            <th>Limiter</th>
            <th>Missing For One Run</th>
          </tr>
        </thead>
        <tbody>
          @forelse($plan['builds'] as $build)
            <tr>
              <td>
                <strong>{{ $build['product_name'] }}</strong>
                <div class="text-muted">{{ number_format($build['product_quantity_per_run']) }} per run</div>
              </td>
              <td>
                {{ $build['blueprint_name'] }}
                <div class="text-muted">Type {{ $build['blueprint_type_id'] }}</div>
              </td>
              <td class="text-right">{{ $build['material_efficiency'] }}%</td>
              <td class="text-right">
                @if($build['buildable_runs'] === PHP_INT_MAX)
                  Unlimited
                @else
                  {{ number_format($build['buildable_runs']) }}
                @endif
              </td>
              <td>
                @if($build['limiting_material'])
                  {{ $build['limiting_material']['name'] }}
                  <div class="text-muted">
                    {{ number_format($build['limiting_material']['available']) }}
                    /
                    {{ number_format($build['limiting_material']['required_per_run']) }} per run
                  </div>
                @endif
              </td>
              <td>
                @php
                  $missing = $build['requirements']->where('missing_for_one_run', '>', 0);
                @endphp

                @if($missing->isEmpty())
                  <span class="badge badge-success">Ready</span>
                @else
                  @foreach($missing->take(4) as $material)
                    <div>{{ $material['name'] }}: {{ number_format($material['missing_for_one_run']) }}</div>
                  @endforeach
                  @if($missing->count() > 4)
                    <div class="text-muted">+ {{ $missing->count() - 4 }} more</div>
                  @endif
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted">
                No manufacturable blueprints found for this owner. Check SeAT has synced blueprints, assets, and SDE industry tables.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection
