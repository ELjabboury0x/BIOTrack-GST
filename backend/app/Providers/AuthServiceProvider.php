<?php

namespace App\Providers;

use App\Models\Complaint;
use App\Models\Equipment;
use App\Models\Intervention;
use App\Policies\ServiceVisibilityPolicy;
use App\Policies\EquipmentPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPolicies();

        // Surface potential N+1 issues during development without impacting production.
        Model::preventLazyLoading(! app()->isProduction());
        Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation): void {
            Log::warning('Lazy loading detected', [
                'model' => $model::class,
                'relation' => $relation,
            ]);
        });

        // Map the Equipment model to its policy
        $this->policies[Equipment::class] = EquipmentPolicy::class;

        Gate::define('access-service', [ServiceVisibilityPolicy::class, 'accessService']);
        Gate::define('view-equipment', [ServiceVisibilityPolicy::class, 'viewEquipment']);
        Gate::define('view-intervention', [ServiceVisibilityPolicy::class, 'viewIntervention']);
        Gate::define('view-complaint', [ServiceVisibilityPolicy::class, 'viewComplaint']);
    }
}
