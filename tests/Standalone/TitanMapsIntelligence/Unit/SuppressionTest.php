<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\Services\SuppressionService;

return static function (): void {
    $service = new SuppressionService();
    assert_same('example.com', $service->normalise('domain', 'https://www.Example.com/path'));
    assert_same('0390000000', $service->normalise('phone', '+61 3 9000 0000'));
    assert_same('hello@example.com', $service->normalise('email', ' Hello@Example.COM '));
    assert_true($service->matches([
        ['suppression_type' => 'domain', 'normalised_value' => 'example.com'],
    ], ['website' => 'https://www.example.com/contact']));
};
