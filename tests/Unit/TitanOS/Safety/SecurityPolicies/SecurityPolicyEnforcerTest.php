<?php

namespace Tests\Unit\TitanOS\Safety\SecurityPolicies;

use App\TitanOS\Safety\SecurityPolicies\SecurityPolicyEnforcer;
use PHPUnit\Framework\TestCase;

class SecurityPolicyEnforcerTest extends TestCase
{
    private SecurityPolicyEnforcer $enforcer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enforcer = new SecurityPolicyEnforcer();
    }

    public function test_create_policy_returns_unique_id(): void
    {
        $policyId1 = $this->enforcer->createPolicy('Policy 1', ['rule1']);
        $policyId2 = $this->enforcer->createPolicy('Policy 2', ['rule2']);

        $this->assertNotEquals($policyId1, $policyId2);
    }

    public function test_assign_policy_to_agent(): void
    {
        $policyId = $this->enforcer->createPolicy('Default Policy', []);
        $this->enforcer->assignPolicy('agent-1', $policyId);

        // Verify by checking validation
        $allowed = $this->enforcer->validateAction('agent-1', 'test', []);
        $this->assertTrue($allowed);
    }

    public function test_validate_action_allows_unrestricted_actions(): void
    {
        $policyId = $this->enforcer->createPolicy('Policy', [
            [
                'name' => 'allow_all',
                'allowed_actions' => ['action1', 'action2'],
            ]
        ]);

        $this->enforcer->assignPolicy('agent-1', $policyId);

        $allowed = $this->enforcer->validateAction('agent-1', 'action1', []);
        $this->assertTrue($allowed);
    }

    public function test_validate_action_denies_restricted_actions(): void
    {
        $policyId = $this->enforcer->createPolicy('Restrictive Policy', [
            [
                'name' => 'deny_delete',
                'denied_actions' => ['delete', 'drop'],
            ]
        ]);

        $this->enforcer->assignPolicy('agent-1', $policyId);

        $allowed = $this->enforcer->validateAction('agent-1', 'delete', []);
        $this->assertFalse($allowed);
    }

    public function test_validate_action_enforces_required_fields(): void
    {
        $policyId = $this->enforcer->createPolicy('Strict Policy', [
            [
                'name' => 'require_id',
                'required_fields' => ['resource_id'],
            ]
        ]);

        $this->enforcer->assignPolicy('agent-1', $policyId);

        $withId = $this->enforcer->validateAction('agent-1', 'read', ['resource_id' => '123']);
        $withoutId = $this->enforcer->validateAction('agent-1', 'read', []);

        $this->assertTrue($withId);
        $this->assertFalse($withoutId);
    }

    public function test_get_violations_returns_failed_validations(): void
    {
        $policyId = $this->enforcer->createPolicy('Policy', [
            [
                'name' => 'deny_delete',
                'denied_actions' => ['delete'],
            ]
        ]);

        $this->enforcer->assignPolicy('agent-1', $policyId);

        $this->enforcer->validateAction('agent-1', 'delete', []);
        $violations = $this->enforcer->getViolations('agent-1');

        $this->assertCount(1, $violations);
        $this->assertEquals('delete', $violations[0]['action']);
    }

    public function test_set_resource_access_defines_permissions(): void
    {
        $policyId = $this->enforcer->createPolicy('Resource Policy', []);

        $this->enforcer->setResourceAccess($policyId, 'files', ['read', 'write']);
        $this->enforcer->setResourceAccess($policyId, 'database', ['read']);

        $this->enforcer->assignPolicy('agent-1', $policyId);

        $canReadFiles = $this->enforcer->checkResourceAccess('agent-1', 'files', 'read');
        $canDeleteFiles = $this->enforcer->checkResourceAccess('agent-1', 'files', 'delete');

        $this->assertTrue($canReadFiles);
        $this->assertFalse($canDeleteFiles);
    }

    public function test_check_resource_access_validates_permission(): void
    {
        $policyId = $this->enforcer->createPolicy('Policy', []);
        $this->enforcer->setResourceAccess($policyId, 'api', ['read', 'write']);
        $this->enforcer->assignPolicy('agent-1', $policyId);

        $canRead = $this->enforcer->checkResourceAccess('agent-1', 'api', 'read');
        $canDelete = $this->enforcer->checkResourceAccess('agent-1', 'api', 'delete');

        $this->assertTrue($canRead);
        $this->assertFalse($canDelete);
    }

    public function test_agent_without_policy_has_unrestricted_access(): void
    {
        $allowed = $this->enforcer->validateAction('unassigned-agent', 'any_action', []);

        $this->assertTrue($allowed);
    }
}
