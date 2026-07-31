<?php

namespace App\TitanOS\Knowledge\Services;

use App\TitanOS\Knowledge\Contracts\{
    DriftDetectionContract,
    KnowledgeGraphContract,
    RepositoryConstitutionContract,
};
use App\TitanOS\Knowledge\DriftDetection\ArchitecturalDriftDetector;
use App\TitanOS\Knowledge\KnowledgeGraph\KnowledgeGraphBuilder;
use App\TitanOS\Knowledge\RepositoryConstitution\ConstitutionEnforcer;
use Illuminate\Support\ServiceProvider;

class TitanKnowledgeServiceProvider extends ServiceProvider
{
    /**
     * Register Titan Knowledge Layer services.
     */
    public function register(): void
    {
        // Phase 2.1: Knowledge Graph Construction & Querying
        $this->app->singleton(KnowledgeGraphContract::class, KnowledgeGraphBuilder::class);

        // Phase 2.2: Repository Constitution
        $this->app->singleton(RepositoryConstitutionContract::class, ConstitutionEnforcer::class);

        // Phase 2.3: Architectural Drift Detection
        $this->app->singleton(DriftDetectionContract::class, ArchitecturalDriftDetector::class);
    }

    /**
     * Bootstrap Titan Knowledge Layer services.
     */
    public function boot(): void
    {
        //
    }
}
