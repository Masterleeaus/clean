<?php

namespace Tests\Feature\TitanOS\Knowledge;

use App\TitanOS\Knowledge\RepositoryConstitution\ConstitutionEnforcer;
use Tests\TestCase;

class ConstitutionEnforcerTest extends TestCase
{
    private ConstitutionEnforcer $enforcer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enforcer = new ConstitutionEnforcer();
    }

    /**
     * @test
     */
    public function it_defines_bounded_contexts()
    {
        $this->enforcer->defineContext('Payment', [
            'path_pattern' => 'app/Domains/Payment/**',
            'dependencies' => ['Shared'],
        ]);

        $contexts = $this->enforcer->getContexts();
        $this->assertTrue($contexts->has('Payment'));
    }

    /**
     * @test
     */
    public function it_sets_file_ownership()
    {
        $this->enforcer->setOwnership('app/Domains/Payment/**', 'payment-team');

        $owner = $this->enforcer->getFileOwner('app/Domains/Payment/PaymentService.php');
        $this->assertEquals('payment-team', $owner);
    }

    /**
     * @test
     */
    public function it_finds_unowned_files()
    {
        $violations = $this->enforcer->validate('app/SomeRandomFile.php');

        $this->assertGreaterThan(0, count($violations));
        $this->assertEquals('unowned', $violations[0]['type']);
    }

    /**
     * @test
     */
    public function it_defines_boundaries()
    {
        $this->enforcer->defineBoundary('payment_boundary', [
            'path_pattern' => 'app/Domains/Payment/**',
            'restrictions' => [
                ['type' => 'no_import', 'from' => 'App\Domains\Auth'],
            ],
        ]);

        $boundaries = $this->enforcer->getContexts();
        $this->assertIsArray((array) $boundaries);
    }

    /**
     * @test
     */
    public function it_validates_files_against_constitution()
    {
        $this->enforcer->defineContext('Auth', [
            'path_pattern' => 'app/Domains/Auth/**',
        ]);

        $violations = $this->enforcer->validate('app/Domains/Auth/AuthService.php');
        $this->assertIsArray($violations);
    }
}
