<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Service;

/**
 * Safely reads optional properties from the heterogeneous original objects returned by Calendarize.
 */
final class ObjectPropertyReader
{
    public function readString(object $object, string $getter): string
    {
        $callable = [$object, $getter];
        if (!is_callable($callable)) {
            return '';
        }

        $value = \Closure::fromCallable($callable)();

        return is_string($value) ? $value : '';
    }

    /** @return iterable<mixed> */
    public function readIterable(object $object, string $getter): iterable
    {
        $callable = [$object, $getter];
        if (!is_callable($callable)) {
            return [];
        }

        $value = \Closure::fromCallable($callable)();

        return is_iterable($value) ? $value : [];
    }

    public function readPositiveInt(object $object, string $getter): int
    {
        $callable = [$object, $getter];
        if (!is_callable($callable)) {
            return 0;
        }

        $value = \Closure::fromCallable($callable)();

        return is_int($value) && $value > 0 ? $value : 0;
    }
}
