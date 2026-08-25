<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Tests\Unit\Service;

use HDNET\Calendarize\Domain\Model\Event;
use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Repository\IndexRepository;
use Mediadreams\MdFullcalendar\Service\CalendarItemFactory;
use Mediadreams\MdFullcalendar\Service\CategoryClassResolver;
use Mediadreams\MdFullcalendar\Service\EventCollectionBuilder;
use Mediadreams\MdFullcalendar\Service\ObjectPropertyReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(EventCollectionBuilder::class)]
#[CoversClass(CalendarItemFactory::class)]
#[CoversClass(CategoryClassResolver::class)]
#[CoversClass(ObjectPropertyReader::class)]
final class EventCollectionBuilderTest extends UnitTestCase
{
    #[Test]
    public function buildPassesQueryToRepositoryAndReturnsSupportedItemsInOriginalOrder(): void
    {
        $start = new \DateTimeImmutable('2026-08-24 00:00:00+02:00');
        $end = new \DateTimeImmutable('2026-08-31 23:59:59+02:00');
        $storagePageIds = [12, 23];
        $eventIndex = $this->createIndexStub('Event', 1, 'First event');
        $unsupportedIndex = $this->createIndexStub('Page', 2, 'Unsupported');
        $newsIndex = $this->createIndexStub('News', 3, 'Second event');

        $indexRepository = $this->createMock(IndexRepository::class);
        $indexRepository->expects($this->once())->method('setOverridePageIds')->with($storagePageIds);
        $indexRepository->expects($this->once())
            ->method('findByTimeSlot')
            ->with($start, $end)
            ->willReturn([$eventIndex, new \stdClass(), $unsupportedIndex, $newsIndex]);

        $propertyReader = new ObjectPropertyReader();
        $subject = new EventCollectionBuilder(
            $indexRepository,
            new CalendarItemFactory($propertyReader, new CategoryClassResolver($propertyReader)),
        );
        $uriBuilder = self::createStub(UriBuilder::class);
        $uriBuilder->method('reset')->willReturnSelf();
        $uriBuilder->method('setTargetPageUid')->willReturnSelf();
        $uriBuilder->method('setTargetPageType')->willReturnSelf();
        $uriBuilder->method('uriFor')->willReturn('/detail');

        $result = $subject->build($start, $end, $uriBuilder, 123, $storagePageIds);

        self::assertCount(2, $result);
        self::assertSame([1, 3], array_column($result, 'id'));
        self::assertSame(['First event', 'Second event'], array_column($result, 'title'));
    }

    #[Test]
    public function buildReturnsEmptyArrayForEmptyRepositoryResult(): void
    {
        $start = new \DateTimeImmutable('2026-08-24 00:00:00+02:00');
        $end = new \DateTimeImmutable('2026-08-31 23:59:59+02:00');
        $indexRepository = $this->createMock(IndexRepository::class);
        $indexRepository->expects($this->once())->method('setOverridePageIds')->with(null);
        $indexRepository->expects($this->once())
            ->method('findByTimeSlot')
            ->with($start, $end)
            ->willReturn([]);

        $subject = $this->createSubject($indexRepository);

        self::assertSame([], $subject->build($start, $end, self::createStub(UriBuilder::class), 123, null));
    }

    #[Test]
    public function buildSkipsSupportedIndexIfCalendarItemCannotBeCreated(): void
    {
        $start = new \DateTimeImmutable('2026-08-24 00:00:00+02:00');
        $end = new \DateTimeImmutable('2026-08-31 23:59:59+02:00');
        $index = self::createStub(Index::class);
        $index->method('getUniqueRegisterKey')->willReturn('Event');
        $index->method('getOriginalObject')->willReturn(null);
        $indexRepository = $this->createMock(IndexRepository::class);
        $indexRepository->method('findByTimeSlot')->willReturn([$index]);

        $subject = $this->createSubject($indexRepository);

        self::assertSame([], $subject->build($start, $end, self::createStub(UriBuilder::class), 123, null));
    }

    private function createSubject(IndexRepository $indexRepository): EventCollectionBuilder
    {
        $propertyReader = new ObjectPropertyReader();

        return new EventCollectionBuilder(
            $indexRepository,
            new CalendarItemFactory($propertyReader, new CategoryClassResolver($propertyReader)),
        );
    }

    private function createIndexStub(string $registerKey, int $uid, string $title): Index
    {
        $event = new Event();
        $event->setTitle($title);

        $index = self::createStub(Index::class);
        $index->method('getUid')->willReturn($uid);
        $index->method('getUniqueRegisterKey')->willReturn($registerKey);
        $index->method('getOriginalObject')->willReturn($event);
        $index->method('getStartDateComplete')->willReturn(new \DateTime('2026-08-24 10:00:00+02:00'));
        $index->method('getEndDateComplete')->willReturn(new \DateTime('2026-08-24 11:00:00+02:00'));
        $index->method('isAllDay')->willReturn(false);

        return $index;
    }
}
