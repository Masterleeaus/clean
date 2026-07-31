<?php

declare(strict_types=1);

namespace App\Titan\Intelligence\Contracts;

interface IntelligenceRepository
{
    public function createFolder(array $data, int $companyId, int $actorId): array;
    public function createCanvas(array $data, int $companyId, int $actorId): array;
    public function createShare(array $data, int $companyId, int $actorId): array;
    public function storeMemory(array $data, int $companyId, int $actorId): array;
    public function createSkill(array $data, int $companyId, int $actorId): array;
    public function assignSkill(array $data, int $companyId, int $actorId): array;
    public function createAgent(array $data, int $companyId, int $actorId): array;
    public function assignAgentTool(array $data, int $companyId, int $actorId): array;
    public function createConnection(array $data, int $companyId, int $actorId): array;
    public function createProviderConnection(array $data, int $companyId, int $actorId): array;
    public function upsertModel(array $data, int $companyId, int $actorId): array;
    public function createRoutingPolicy(array $data, int $companyId, int $actorId): array;
    public function startCouncilRun(array $data, int $companyId, int $actorId): array;
    public function createVoiceProfile(array $data, int $companyId, int $actorId): array;
    public function startVoiceSession(array $data, int $companyId, int $actorId): array;
    public function publishAnnouncement(array $data, int $companyId, int $actorId): array;
    public function createOnboardingFlow(array $data, int $companyId, int $actorId): array;
    public function recordOnboardingProgress(array $data, int $companyId, int $actorId): array;
    public function summary(int $companyId): array;
}
