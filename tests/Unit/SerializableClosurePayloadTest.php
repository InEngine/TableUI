<?php

declare(strict_types=1);

use InEngine\TableUI\Support\SerializableClosurePayload;

it('roundtrips a closure payload', function (): void {
    $payload = SerializableClosurePayload::encode(static fn (int $n): int => $n * 2);

    expect(SerializableClosurePayload::decode($payload)(21))->toBe(42);
});
