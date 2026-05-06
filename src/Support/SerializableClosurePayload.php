<?php

namespace InEngine\TableUI\Support;

use Closure;
use InEngine\TableUI\Livewire\TableView;
use InvalidArgumentException;
use Laravel\SerializableClosure\SerializableClosure;

/**
 * Encodes {@see Closure} targets into Livewire-safe strings for {@see TableView::$actionSnapshots}.
 */
final class SerializableClosurePayload
{
    /**
     * Base64-encoded PHP serialization of {@see SerializableClosure}.
     */
    public static function encode(Closure $closure): string
    {
        return base64_encode(serialize(new SerializableClosure($closure)));
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function decode(string $payload): SerializableClosure
    {
        $binary = base64_decode($payload, true);

        if ($binary === false) {
            throw new InvalidArgumentException('Invalid base64 payload for serialized closure.');
        }

        $unserialized = unserialize($binary);

        if (! $unserialized instanceof SerializableClosure) {
            throw new InvalidArgumentException('Expected a Laravel SerializableClosure instance.');
        }

        return $unserialized;
    }
}
