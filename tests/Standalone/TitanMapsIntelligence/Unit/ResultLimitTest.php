<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\Services\ResultLimitService;

return static function (): void {
    $limits = new ResultLimitService();
    assert_same(5, $limits->remaining(25, 20));
    assert_same(0, $limits->remaining(20, 20));
    assert_same([1, 2, 3], $limits->take([1, 2, 3, 4, 5], 3));
    assert_same([], $limits->take([1, 2], 0));
};
