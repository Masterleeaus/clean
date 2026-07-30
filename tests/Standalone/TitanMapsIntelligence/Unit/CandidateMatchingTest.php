<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\Services\CandidateMatchingService;

return static function (): void {
    $service = new CandidateMatchingService();
    $candidate = [
        'provider' => 'google-places',
        'provider_place_id' => 'abc',
        'name' => 'Northside Plumbing',
        'phone' => '03 9000 0000',
        'website' => 'https://northside.example',
        'email' => 'info@northside.example',
        'address' => '1 High Street Melbourne VIC 3000',
        'latitude' => -37.8100,
        'longitude' => 144.9600,
        'categories' => ['plumber'],
    ];
    $existing = [
        'external_provider' => 'google-places',
        'external_place_id' => 'abc',
        'name' => 'Northside Plumbing Pty Ltd',
        'phone' => '(03) 9000 0000',
        'website' => 'https://www.northside.example/contact',
        'email' => 'info@northside.example',
        'address' => '1 High St, Melbourne VIC 3000',
        'latitude' => -37.8102,
        'longitude' => 144.9601,
        'categories' => ['plumber', 'home_service'],
    ];

    $result = $service->score($candidate, $existing);
    assert_same('confirmed', $result->status);
    assert_true($result->score >= 0.86, 'Exact external identity should confirm match');
    assert_true(in_array('provider_place_id', $result->matchingFields, true));

    $ambiguous = $service->score($candidate, [...$existing, 'external_place_id' => 'different', 'phone' => '03 8111 1111', 'website' => 'https://different.example', 'email' => 'other@example.com']);
    assert_true(in_array($ambiguous->status, ['ambiguous', 'no_match'], true));
    assert_true($ambiguous->score < $result->score);
};
