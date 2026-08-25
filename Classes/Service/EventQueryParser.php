<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Service;

use Mediadreams\MdFullcalendar\Domain\Dto\EventQuery;

/**
 * Validates request parameters and creates the bounded query used to load Calendarize events.
 */
final class EventQueryParser
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function parse(array $parameters): EventQuery
    {
        $requestedStart = $this->parseDateTime($parameters['start'] ?? null, 'start');
        $requestedEnd = $this->parseDateTime($parameters['end'] ?? null, 'end');

        if ($requestedEnd <= $requestedStart) {
            throw new \InvalidArgumentException('The end date must be later than the start date.', 1755000101);
        }

        $start = $requestedStart->sub(new \DateInterval('P1D'))->setTime(0, 0);
        $end = $requestedEnd->add(new \DateInterval('P1D'))->setTime(23, 59, 59);
        $rangeInDays = $start->diff($end)->days;
        if ($rangeInDays > 50) {
            $end = $start->add(new \DateInterval('P50D'))->setTime(23, 59, 59);
        }

        return new EventQuery(
            $start,
            $end,
            $this->parseStoragePageIds($parameters['storage'] ?? null),
        );
    }

    private function parseDateTime(mixed $value, string $parameterName): \DateTimeImmutable
    {
        if (!is_string($value) || $value === '') {
            throw new \InvalidArgumentException('Missing or invalid parameter: ' . $parameterName, 1755000102);
        }

        $dateTime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $value);
        $errors = \DateTimeImmutable::getLastErrors();
        if ($dateTime === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            throw new \InvalidArgumentException('Invalid ISO 8601 date in parameter: ' . $parameterName, 1755000103);
        }

        return $dateTime;
    }

    /**
     * @return list<int>|null
     */
    private function parseStoragePageIds(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Invalid storage page parameter.', 1755000104);
        }

        $pageIds = [];
        foreach (explode(',', $value) as $pageId) {
            $pageId = trim($pageId);
            if ($pageId === '' || !ctype_digit($pageId) || (int)$pageId <= 0) {
                throw new \InvalidArgumentException('Invalid storage page parameter.', 1755000105);
            }
            $pageIds[] = (int)$pageId;
        }

        return array_values(array_unique($pageIds));
    }
}
