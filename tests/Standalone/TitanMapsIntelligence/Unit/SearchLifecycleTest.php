<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\Enums\SearchStatus;
use App\Extensions\TitanMapsIntelligence\Exceptions\MapsIntelligenceException;
use App\Extensions\TitanMapsIntelligence\Services\DiscoverySearchStateMachine;

return static function (): void {
    assert_true(DiscoverySearchStateMachine::canTransition(SearchStatus::Draft, SearchStatus::Queued));
    assert_true(DiscoverySearchStateMachine::canTransition(SearchStatus::Running, SearchStatus::Paused));
    assert_true(DiscoverySearchStateMachine::canTransition(SearchStatus::Running, SearchStatus::Completed));
    assert_true(! DiscoverySearchStateMachine::canTransition(SearchStatus::Completed, SearchStatus::Running));
    assert_throws(
        fn () => DiscoverySearchStateMachine::assertTransition(SearchStatus::Completed, SearchStatus::Running),
        MapsIntelligenceException::class,
        'MAPS_INVALID_SEARCH_TRANSITION'
    );
};
