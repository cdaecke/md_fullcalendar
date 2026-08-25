<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Service;

use HDNET\Calendarize\Domain\Model\Event;
use HDNET\Calendarize\Domain\Model\Index;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

/**
 * Maps a Calendarize index and its original record to the JSON structure expected by FullCalendar.
 */
final readonly class CalendarItemFactory
{
    public function __construct(
        private ObjectPropertyReader $propertyReader,
        private CategoryClassResolver $categoryClassResolver,
    ) {}

    /**
     * @return array<string, bool|int|string|list<string>>|null
     */
    public function create(Index $index, UriBuilder $uriBuilder, int $detailPid): ?array
    {
        $originalObject = $index->getOriginalObject();
        if ($originalObject === null) {
            return null;
        }

        $dates = $this->formatDates($index);
        if ($dates === null) {
            return null;
        }

        $item = [
            'id' => $index->getUid() ?? 0,
            'title' => $this->propertyReader->readString($originalObject, 'getTitle'),
            'description' => $this->propertyReader->readString($originalObject, 'getDescription'),
            'start' => $dates['start'],
            'end' => $dates['end'],
            'allDay' => $index->isAllDay(),
            'className' => implode(' ', ['cal-item', ...$this->categoryClassResolver->resolve($originalObject)]),
            'url' => $this->createDetailUri($uriBuilder, $detailPid, $index),
            'uriAjax' => $this->createAjaxUri($uriBuilder, $detailPid, $index),
        ];

        if ($index->getUniqueRegisterKey() === 'News') {
            $item['abstract'] = $this->propertyReader->readString($originalObject, 'getTeaser');
        }
        if ($originalObject instanceof Event) {
            $item += $this->createEventProperties($originalObject);
        }

        return $item;
    }

    /**
     * @return array{start: non-empty-string, end: non-empty-string}|null
     */
    private function formatDates(Index $index): ?array
    {
        $startDate = $index->getStartDateComplete();
        if ($startDate === null) {
            return null;
        }
        $endDate = $index->getEndDateComplete();
        if ($endDate === null) {
            return null;
        }

        if ($index->isAllDay()) {
            return [
                'start' => $startDate->format('Y-m-d'),
                'end' => (clone $endDate)->add(new \DateInterval('P1D'))->format('Y-m-d'),
            ];
        }

        return [
            'start' => $startDate->format(\DateTimeInterface::ATOM),
            'end' => $endDate->format(\DateTimeInterface::ATOM),
        ];
    }

    private function createDetailUri(UriBuilder $uriBuilder, int $detailPid, Index $index): string
    {
        return $uriBuilder
            ->reset()
            ->setTargetPageUid($detailPid)
            ->uriFor('detail', ['index' => $index->getUid()], 'Calendar', 'calendarize', 'calendar');
    }

    private function createAjaxUri(UriBuilder $uriBuilder, int $detailPid, Index $index): string
    {
        return $uriBuilder
            ->reset()
            ->setTargetPageUid($detailPid)
            ->setTargetPageType(1573760945)
            ->uriFor('detail', ['index' => $index->getUid()], 'Cal', 'mdfullcalendar', 'caldetail');
    }

    /**
     * @return array{abstract: string, location: string, locationLink: string, organizer: string, organizerLink: string}
     */
    private function createEventProperties(Event $event): array
    {
        return [
            'abstract' => $event->getAbstract(),
            'location' => $event->getLocation(),
            'locationLink' => $event->getLocationLink(),
            'organizer' => $event->getOrganizer(),
            'organizerLink' => $event->getOrganizerLink(),
        ];
    }
}
