<?php

namespace App\TitanOS\Foundation\Contracts;

use Illuminate\Support\Collection;

interface ManifestLoaderContract
{
    /**
     * Load and validate agent manifest from YAML file.
     *
     * @param string $path Path to manifest file
     * @return array Parsed manifest with validation
     *
     * @throws \App\TitanOS\Foundation\Exceptions\InvalidManifestException
     */
    public function loadAgentManifest(string $path): array;

    /**
     * Load capability registry.
     *
     * @param string $path Path to registry file
     * @return Collection Capabilities indexed by id
     *
     * @throws \App\TitanOS\Foundation\Exceptions\InvalidRegistryException
     */
    public function loadCapabilityRegistry(string $path): Collection;

    /**
     * Validate manifest against schema.
     *
     * @param array $manifest
     * @param string $schema Path to JSON schema
     * @return bool
     *
     * @throws \App\TitanOS\Foundation\Exceptions\ManifestValidationException
     */
    public function validateManifest(array $manifest, string $schema): bool;
}
