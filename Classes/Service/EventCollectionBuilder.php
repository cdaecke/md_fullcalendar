<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Service;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Repository\IndexRepository;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

/**
 * Loads Calendarize indices for a time range and converts supported records into calendar items.
 */
final readonly class EventCollectionBuilder
{
    public function __construct(
        private IndexRepository $indexRepository,
        private CalendarItemFactory $calendarItemFactory,
    ) {}

    /**
     * @return list<array<string, bool|int|string|list<string>>>
     * @param list<int>|null $storagePageIds
     */
    public function build(
        \DateTimeInterface $start,
        \DateTimeInterface $end,
        UriBuilder $uriBuilder,
        int $detailPid,
        ?array $storagePageIds,
    ): array {
        $this->indexRepository->setOverridePageIds($storagePageIds);

        $items = [];
        foreach ($this->indexRepository->findByTimeSlot($start, $end) as $index) {
            if (!$index instanceof Index) {
                continue;
            }

            $item = $this->createSupportedItem($index, $uriBuilder, $detailPid);
            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return array<string, bool|int|string|list<string>>|null
     */
    private function createSupportedItem(Index $index, UriBuilder $uriBuilder, int $detailPid): ?array
    {
        if (!in_array($index->getUniqueRegisterKey(), ['Event', 'News'], true)) {
            return null;
        }

        return $this->calendarItemFactory->create($index, $uriBuilder, $detailPid);
    }
}
