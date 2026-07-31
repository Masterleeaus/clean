<?php

namespace App\TitanOS\Knowledge\Contracts;

use Illuminate\Support\Collection;

interface DriftDetectionContract
{
    /**
     * Scan repository for architectural drift.
     *
     * @param string $basePath Repository path
     * @param array $expectedArchitecture Reference architecture
     * @return string Scan ID
     */
    public function scan(string $basePath, array $expectedArchitecture = []): string;

    /**
     * Get drift violations from latest scan.
     *
     * @param string $scanId Scan ID
     * @return Collection Violations grouped by type
     */
    public function getViolations(string $scanId): Collection;

    /**
     * Detect boundary crossings.
     *
     * @return Collection Illegal dependencies between bounded contexts
     */
    public function detectBoundaryCrossings(): Collection;

    /**
     * Detect layering violations.
     *
     * @return Collection Files that violate layering rules
     */
    public function detectLayeringViolations(): Collection;

    /**
     * Detect code organization issues.
     *
     * @return Collection Organization problems (deep nesting, wrong placement, etc)
     */
    public function detectOrganizationIssues(): Collection;

    /**
     * Calculate health score for architecture.
     *
     * @return float Score 0-100 representing architectural health
     */
    public function calculateHealthScore(): float;

    /**
     * Get drift report.
     *
     * @param string $scanId Scan ID
     * @return array Detailed drift report with recommendations
     */
    public function getReport(string $scanId): array;

    /**
     * Compare two architecture states.
     *
     * @param array $before Architecture before
     * @param array $after Architecture after
     * @return array Diff with changes
     */
    public function compareScan(array $before, array $after): array;
}
