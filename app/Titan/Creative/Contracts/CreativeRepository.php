<?php

declare(strict_types=1);

namespace App\Titan\Creative\Contracts;

interface CreativeRepository
{
    public function createBrandKit(array $data, int $companyId, int $actorId): array;
    public function createAudience(array $data, int $companyId, int $actorId): array;
    public function createTemplate(array $data, int $companyId, int $actorId): array;
    public function createProject(array $data, int $companyId, int $actorId): array;
    public function registerAsset(array $data, int $companyId, int $actorId): array;
    public function queueGeneration(array $data, int $companyId, int $actorId): array;
    public function transitionGeneration(array $data, int $companyId, int $actorId): array;
    public function createCampaign(array $data, int $companyId, int $actorId): array;
    public function createContent(array $data, int $companyId, int $actorId): array;
    public function scheduleContent(array $data, int $companyId, int $actorId): array;
    public function createPublicationChannel(array $data, int $companyId, int $actorId): array;
    public function requestPublication(array $data, int $companyId, int $actorId): array;
    public function transitionPublication(array $data, int $companyId, int $actorId): array;
    public function createAutomation(array $data, int $companyId, int $actorId): array;
    public function createSeoBrief(array $data, int $companyId, int $actorId): array;
    public function createNewsletter(array $data, int $companyId, int $actorId): array;
    public function decideApproval(array $data, int $companyId, int $actorId): array;
    public function recordAnalytics(array $data, int $companyId, int $actorId): array;
    public function summary(int $companyId): array;
}
