<?php

namespace App\TitanOS\Knowledge\Contracts;

use Illuminate\Support\Collection;

interface RepositoryConstitutionContract
{
    /**
     * Load and validate repository constitution.
     *
     * @param string $configPath Path to constitution config
     * @return array Validated constitution rules
     */
    public function load(string $configPath): array;

    /**
     * Define a bounded context.
     *
     * @param string $name Context name
     * @param array $config Context configuration
     * @return void
     */
    public function defineContext(string $name, array $config): void;

    /**
     * Get all defined bounded contexts.
     *
     * @return Collection Contexts with boundaries
     */
    public function getContexts(): Collection;

    /**
     * Set file ownership rules.
     *
     * @param string $pattern File glob pattern
     * @param string $owner Owner/team ID
     * @param array $metadata Ownership metadata
     * @return void
     */
    public function setOwnership(string $pattern, string $owner, array $metadata = []): void;

    /**
     * Get file owner.
     *
     * @param string $filePath
     * @return string|null Owner ID or null if unowned
     */
    public function getFileOwner(string $filePath): ?string;

    /**
     * Define architectural boundary.
     *
     * @param string $name Boundary name
     * @param array $config Boundary rules
     * @return void
     */
    public function defineBoundary(string $name, array $config): void;

    /**
     * Validate code against constitution.
     *
     * @param string $filePath File to validate
     * @return array Violations found
     */
    public function validate(string $filePath): array;

    /**
     * Get boundary violations.
     *
     * @return Collection All boundary violations in repo
     */
    public function getBoundaryViolations(): Collection;

    /**
     * Get ownership violations.
     *
     * @return Collection Files modified by non-owner
     */
    public function getOwnershipViolations(): Collection;
}
