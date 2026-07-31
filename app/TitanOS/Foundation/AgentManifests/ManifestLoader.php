<?php

namespace App\TitanOS\Foundation\AgentManifests;

use App\TitanOS\Foundation\Contracts\ManifestLoaderContract;
use App\TitanOS\Foundation\Exceptions\{InvalidManifestException, InvalidRegistryException, ManifestValidationException};
use Illuminate\Support\Collection;
use JsonSchema\Validator;
use JsonSchema\SchemaStorage;
use Symfony\Component\Yaml\Yaml;

class ManifestLoader implements ManifestLoaderContract
{
    private SchemaStorage $schemaStorage;
    private Validator $validator;

    public function __construct()
    {
        $this->schemaStorage = new SchemaStorage();
        $this->validator = new Validator();
    }

    public function loadAgentManifest(string $path): array
    {
        if (!file_exists($path)) {
            throw new InvalidManifestException("Manifest file not found: {$path}", ['path' => $path]);
        }

        try {
            $content = file_get_contents($path);
            $manifest = Yaml::parse($content);

            if (!is_array($manifest)) {
                throw new InvalidManifestException("Manifest must be valid YAML object", ['path' => $path]);
            }

            return $manifest;
        } catch (\Exception $e) {
            throw new InvalidManifestException("Failed to parse manifest: {$e->getMessage()}", ['path' => $path], previous: $e);
        }
    }

    public function loadCapabilityRegistry(string $path): Collection
    {
        if (!file_exists($path)) {
            throw new InvalidRegistryException("Registry file not found: {$path}", ['path' => $path]);
        }

        try {
            $content = file_get_contents($path);
            $registry = Yaml::parse($content);

            if (!isset($registry['capabilities']) || !is_array($registry['capabilities'])) {
                throw new InvalidRegistryException("Registry missing 'capabilities' section", ['path' => $path]);
            }

            return collect($registry['capabilities'])->keyBy('id');
        } catch (\Exception $e) {
            throw new InvalidRegistryException("Failed to parse registry: {$e->getMessage()}", ['path' => $path], previous: $e);
        }
    }

    public function validateManifest(array $manifest, string $schemaPath): bool
    {
        if (!file_exists($schemaPath)) {
            throw new ManifestValidationException("Schema file not found: {$schemaPath}", ['schema' => $schemaPath]);
        }

        try {
            $schemaData = json_decode(file_get_contents($schemaPath));
            $this->validator->validate((object) $manifest, $schemaData);

            if (!$this->validator->isValid()) {
                $errors = collect($this->validator->getErrors())
                    ->map(fn($error) => "{$error['property']}: {$error['message']}")
                    ->toArray();

                throw new ManifestValidationException(
                    "Manifest validation failed",
                    ['errors' => $errors]
                );
            }

            return true;
        } catch (\Exception $e) {
            if ($e instanceof ManifestValidationException) {
                throw $e;
            }
            throw new ManifestValidationException("Validation error: {$e->getMessage()}", previous: $e);
        }
    }
}
