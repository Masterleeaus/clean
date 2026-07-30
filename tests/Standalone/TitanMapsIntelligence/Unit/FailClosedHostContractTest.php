<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\Contracts\SecretResolver;
use App\Extensions\TitanMapsIntelligence\Exceptions\MissingHostContractException;
use App\Extensions\TitanMapsIntelligence\Support\RequiredHostContract;

return static function (): void {
    $resolver = new RequiredHostContract(static fn (string $contract): mixed => null);
    assert_throws(
        fn () => $resolver->resolve(SecretResolver::class),
        MissingHostContractException::class,
        'MAPS_HOST_CONTRACT_MISSING'
    );

    $secret = new class implements SecretResolver {
        public function resolve(string $credentialReference): string
        {
            return 'resolved-secret';
        }
    };
    $resolver = new RequiredHostContract(static fn (string $contract): mixed => $contract === SecretResolver::class ? $secret : null);
    assert_same($secret, $resolver->resolve(SecretResolver::class));
};
