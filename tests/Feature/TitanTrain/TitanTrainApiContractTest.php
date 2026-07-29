<?php

namespace Tests\Feature\TitanTrain;

use Tests\TestCase;

final class TitanTrainApiContractTest extends TestCase
{
    public function test_pwa_bootstrap_requires_authentication(): void
    {
        $this->getJson('/api/v1/titan-train/pwa/bootstrap')->assertUnauthorized();
    }

    public function test_train_launch_requires_authentication(): void
    {
        $this->get('/train')->assertRedirect();
    }
}
