<?php

namespace App\TitanOS\Safety\SecurityPolicies;

use App\TitanOS\Safety\Contracts\SecurityPolicyContract;
use App\TitanOS\Safety\Exceptions\PolicyViolationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class SecurityPolicyEnforcer implements SecurityPolicyContract
{
    private const POLICY_PREFIX = 'titan:policy:';
    private const ASSIGNMENT_PREFIX = 'titan:policy:assignment:';
    private array $policies = [];
    private array $assignments = [];
    private array $violations = [];

    public function createPolicy(string $policyName, array $rules): string
    {
        $policyId = Str::uuid()->toString();

        $this->policies[$policyId] = [
            'id' => $policyId,
            'name' => $policyName,
            'rules' => $rules,
            'created_at' => now()->toIso8601String(),
            'resource_access' => [],
        ];

        Cache::put(self::POLICY_PREFIX . $policyId, $this->policies[$policyId], 604800);

        return $policyId;
    }

    public function assignPolicy(string $agentId, string $policyId): void
    {
        $this->assignments[$agentId] = $policyId;
        Cache::put(self::ASSIGNMENT_PREFIX . $agentId, $policyId, 604800);
    }

    public function validateAction(string $agentId, string $action, array $context): bool
    {
        $policyId = $this->assignments[$agentId] ?? Cache::get(self::ASSIGNMENT_PREFIX . $agentId);

        if (!$policyId) {
            return true;
        }

        $policy = $this->policies[$policyId] ?? Cache::get(self::POLICY_PREFIX . $policyId, []);

        if (empty($policy)) {
            return true;
        }

        foreach ($policy['rules'] as $rule) {
            if (!$this->evaluateRule($rule, $action, $context)) {
                if (!isset($this->violations[$agentId])) {
                    $this->violations[$agentId] = [];
                }

                $this->violations[$agentId][] = [
                    'action' => $action,
                    'rule' => $rule['name'] ?? 'unnamed',
                    'context' => $context,
                    'timestamp' => now()->toIso8601String(),
                ];

                return false;
            }
        }

        return true;
    }

    public function getViolations(string $agentId): array
    {
        return $this->violations[$agentId] ?? [];
    }

    public function setResourceAccess(string $policyId, string $resource, array $permissions): void
    {
        if (!isset($this->policies[$policyId])) {
            $this->policies[$policyId] = Cache::get(self::POLICY_PREFIX . $policyId, []);
        }

        if (!empty($this->policies[$policyId])) {
            $this->policies[$policyId]['resource_access'][$resource] = $permissions;
            Cache::put(self::POLICY_PREFIX . $policyId, $this->policies[$policyId], 604800);
        }
    }

    public function checkResourceAccess(string $agentId, string $resource, string $permission): bool
    {
        $policyId = $this->assignments[$agentId] ?? Cache::get(self::ASSIGNMENT_PREFIX . $agentId);

        if (!$policyId) {
            return true;
        }

        $policy = $this->policies[$policyId] ?? Cache::get(self::POLICY_PREFIX . $policyId, []);

        if (empty($policy) || empty($policy['resource_access'][$resource])) {
            return true;
        }

        $permissions = $policy['resource_access'][$resource];

        return in_array($permission, $permissions);
    }

    private function evaluateRule(array $rule, string $action, array $context): bool
    {
        if (isset($rule['allowed_actions'])) {
            if (!in_array($action, $rule['allowed_actions'])) {
                return false;
            }
        }

        if (isset($rule['denied_actions'])) {
            if (in_array($action, $rule['denied_actions'])) {
                return false;
            }
        }

        if (isset($rule['required_fields'])) {
            foreach ($rule['required_fields'] as $field) {
                if (!isset($context[$field])) {
                    return false;
                }
            }
        }

        return true;
    }
}
