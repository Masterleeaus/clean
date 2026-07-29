<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

final class CustomerChannelPreferenceResolver
{
    private const PRIORITY = ['customer_app', 'sms', 'email', 'voice'];

    /** @param array<string,mixed>|object $customer @param array<int,string> $requested @return array<int,string> */
    public function resolve(array|object $customer, array $requested): array
    {
        $data = is_array($customer) ? $customer : $customer->toArray();
        $metadata = is_array($data['metadata_json'] ?? null) ? $data['metadata_json'] : [];
        $preferences = is_array($metadata['communication_preferences'] ?? null)
            ? $metadata['communication_preferences']
            : [];

        $requested = array_values(array_unique(array_filter($requested, 'is_string')));
        $resolved = [];
        foreach (self::PRIORITY as $channel) {
            if (! in_array($channel, $requested, true) || ($preferences[$channel] ?? true) !== true) {
                continue;
            }

            $available = match ($channel) {
                'customer_app' => ! empty($metadata['customer_app_user_id']),
                'sms', 'voice' => ! empty($data['phone']),
                'email' => ! empty($data['email']),
                default => false,
            };

            if ($available) {
                $resolved[] = $channel;
            }
        }

        return $resolved;
    }
}
