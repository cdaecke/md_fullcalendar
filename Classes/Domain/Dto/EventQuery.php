<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Domain\Dto;

/**
 * Carries a validated event time range and optional Calendarize storage page IDs.
 */
final readonly class EventQuery
{
    /**
     * @param list<int>|null $storagePageIds
     */
    public function __construct(
        public \DateTimeImmutable $start,
        public \DateTimeImmutable $end,
        public ?array $storagePageIds,
    ) {}
}
