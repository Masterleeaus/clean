<?php

namespace App\TitanOS\Safety\Contracts;

interface SecurityPolicyContract
{
    /**
     * Create security policy.
     *
     * @param string $policyName Unique policy name
     * @param array $rules Security rules and constraints
     * @return string Policy ID
     */
    public function createPolicy(string $policyName, array $rules): string;

    /**
     * Assign policy to agent.
     *
     * @param string $agentId
     * @param string $policyId
     * @return void
     */
    public function assignPolicy(string $agentId, string $policyId): void;

    /**
     * Validate action against security policies.
     *
     * @param string $agentId
     * @param string $action Action type to validate
     * @param array $context Action context and parameters
     * @return bool Whether action is allowed
     */
    public function validateAction(string $agentId, string $action, array $context): bool;

    /**
     * Get violations for policy.
     *
     * @param string $agentId
     * @return array List of policy violations
     */
    public function getViolations(string $agentId): array;

    /**
     * Define data access restrictions.
     *
     * @param string $policyId
     * @param string $resource Resource type
     * @param array $permissions Read, write, delete permissions
     * @return void
     */
    public function setResourceAccess(string $policyId, string $resource, array $permissions): void;

    /**
     * Check resource access permission.
     *
     * @param string $agentId
     * @param string $resource Resource to access
     * @param string $permission read|write|delete
     * @return bool Whether agent can access resource
     */
    public function checkResourceAccess(string $agentId, string $resource, string $permission): bool;
}
