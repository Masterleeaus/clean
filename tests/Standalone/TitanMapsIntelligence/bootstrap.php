<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\Extensions\\TitanMapsIntelligence\\';
    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = dirname(__DIR__, 3).'/app/Extensions/TitanMapsIntelligence/'.str_replace('\\', '/', $relative).'.php';
    if (is_file($path)) {
        require_once $path;
    }
});

final class TestFailure extends RuntimeException {}

function assert_true(bool $condition, string $message = 'Assertion failed'): void
{
    if (! $condition) {
        throw new TestFailure($message);
    }
}

function assert_same(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new TestFailure(($message !== '' ? $message.': ' : '').'expected '.var_export($expected, true).' got '.var_export($actual, true));
    }
}

function assert_throws(callable $callback, string $exceptionClass, ?string $code = null): void
{
    try {
        $callback();
    } catch (Throwable $throwable) {
        assert_true($throwable instanceof $exceptionClass, 'Expected '.$exceptionClass.', got '.$throwable::class);
        if ($code !== null && method_exists($throwable, 'errorCode')) {
            assert_same($code, $throwable->errorCode(), 'Unexpected stable error code');
        }
        return;
    }

    throw new TestFailure('Expected exception '.$exceptionClass.' was not thrown');
}
