<?php

namespace Tests\Feature\TitanOS\Knowledge;

use App\TitanOS\Knowledge\DriftDetection\ArchitecturalDriftDetector;
use Tests\TestCase;

class ArchitecturalDriftDetectorTest extends TestCase
{
    private ArchitecturalDriftDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new ArchitecturalDriftDetector();
    }

    /**
     * @test
     */
    public function it_scans_for_architectural_drift()
    {
        $basePath = app_path();
        $scanId = $this->detector->scan($basePath);

        $this->assertNotEmpty($scanId);
    }

    /**
     * @test
     */
    public function it_detects_boundary_crossings()
    {
        $basePath = app_path();
        $this->detector->scan($basePath);

        $violations = $this->detector->detectBoundaryCrossings();
        $this->assertIsObject($violations);
    }

    /**
     * @test
     */
    public function it_detects_layering_violations()
    {
        $basePath = app_path();
        $this->detector->scan($basePath);

        $violations = $this->detector->detectLayeringViolations();
        $this->assertIsObject($violations);
    }

    /**
     * @test
     */
    public function it_detects_organization_issues()
    {
        $basePath = app_path();
        $this->detector->scan($basePath);

        $violations = $this->detector->detectOrganizationIssues();
        $this->assertIsObject($violations);
    }

    /**
     * @test
     */
    public function it_calculates_health_score()
    {
        $basePath = app_path();
        $this->detector->scan($basePath);

        $score = $this->detector->calculateHealthScore();

        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    /**
     * @test
     */
    public function it_generates_drift_report()
    {
        $basePath = app_path();
        $scanId = $this->detector->scan($basePath);

        $report = $this->detector->getReport($scanId);

        $this->assertArrayHasKey('scan_id', $report);
        $this->assertArrayHasKey('total_violations', $report);
        $this->assertArrayHasKey('health_score', $report);
        $this->assertArrayHasKey('recommendations', $report);
    }

    /**
     * @test
     */
    public function it_provides_recommendations()
    {
        $basePath = app_path();
        $scanId = $this->detector->scan($basePath);

        $report = $this->detector->getReport($scanId);

        $this->assertIsArray($report['recommendations']);
    }
}
