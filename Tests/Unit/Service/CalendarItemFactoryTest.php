<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Tests\Unit\Service;

use HDNET\Calendarize\Domain\Model\Event;
use HDNET\Calendarize\Domain\Model\Index;
use Mediadreams\MdFullcalendar\Service\CalendarItemFactory;
use Mediadreams\MdFullcalendar\Service\CategoryClassResolver;
use Mediadreams\MdFullcalendar\Service\ObjectPropertyReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(CalendarItemFactory::class)]
#[CoversClass(CategoryClassResolver::class)]
#[CoversClass(ObjectPropertyReader::class)]
final class CalendarItemFactoryTest extends UnitTestCase
{
    private CalendarItemFactory $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $propertyReader = new ObjectPropertyReader();
        $this->subject = new CalendarItemFactory(
            $propertyReader,
            new CategoryClassResolver($propertyReader),
        );
    }

    #[Test]
    public function createMapsTimedCalendarizeEventToFullCalendarItem(): void
    {
        $event = new Event();
        $event->setTitle('Tea time');
        $event->setDescription('A calendar event');
        $event->setAbstract('Short description');
        $event->setLocation('Tea room');
        $event->setLocationLink('https://example.test/location');
        $event->setOrganizer('The organizer');
        $event->setOrganizerLink('https://example.test/organizer');
        $category = new Category();
        $category->_setProperty('uid', 42);
        $event->addCategory($category);

        $index = $this->createIndexStub(
            $event,
            new \DateTime('2026-08-24 10:15:00+02:00'),
            new \DateTime('2026-08-24 11:30:00+02:00'),
            false,
            'Event',
        );

        $result = $this->subject->create($index, $this->createUriBuilderMock(), 123);

        self::assertSame([
            'id' => 17,
            'title' => 'Tea time',
            'description' => 'A calendar event',
            'start' => '2026-08-24T10:15:00+02:00',
            'end' => '2026-08-24T11:30:00+02:00',
            'allDay' => false,
            'className' => 'cal-item category42',
            'url' => '/calendarize/detail/17',
            'uriAjax' => '/md-fullcalendar/detail/17',
            'abstract' => 'Short description',
            'location' => 'Tea room',
            'locationLink' => 'https://example.test/location',
            'organizer' => 'The organizer',
            'organizerLink' => 'https://example.test/organizer',
        ], $result);
    }

    #[Test]
    public function createFormatsAllDayEventWithExclusiveEndDate(): void
    {
        $event = new Event();
        $end = new \DateTime('2026-08-26 23:59:59+02:00');
        $index = $this->createIndexStub(
            $event,
            new \DateTime('2026-08-24 00:00:00+02:00'),
            $end,
            true,
            'Event',
        );

        $result = $this->subject->create($index, $this->createUriBuilderMock(), 123);

        self::assertIsArray($result);
        self::assertSame('2026-08-24', $result['start']);
        self::assertSame('2026-08-27', $result['end']);
        self::assertTrue($result['allDay']);
        self::assertSame('2026-08-26T23:59:59+02:00', $end->format(\DateTimeInterface::ATOM));
    }

    #[Test]
    public function createUsesZeroForMissingIndexUid(): void
    {
        $event = new Event();
        $index = $this->createIndexStub(
            $event,
            new \DateTime('2026-08-24 10:00:00+02:00'),
            new \DateTime('2026-08-24 11:00:00+02:00'),
            false,
            'Event',
            null,
        );
        $uriBuilder = self::createStub(UriBuilder::class);
        $uriBuilder->method('reset')->willReturnSelf();
        $uriBuilder->method('setTargetPageUid')->willReturnSelf();
        $uriBuilder->method('setTargetPageType')->willReturnSelf();
        $uriBuilder->method('uriFor')->willReturn('/detail');

        $result = $this->subject->create($index, $uriBuilder, 123);

        self::assertIsArray($result);
        self::assertSame(0, $result['id']);
    }

    #[Test]
    public function createMapsNewsTeaserToAbstract(): void
    {
        $news = new class extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {
            public function getTitle(): string
            {
                return 'News title';
            }

            public function getDescription(): string
            {
                return 'News description';
            }

            public function getTeaser(): string
            {
                return 'News teaser';
            }
        };
        $index = $this->createIndexStub(
            $news,
            new \DateTime('2026-08-24 10:00:00+02:00'),
            new \DateTime('2026-08-24 11:00:00+02:00'),
            false,
            'News',
        );

        $result = $this->subject->create($index, $this->createUriBuilderMock(), 123);

        self::assertIsArray($result);
        self::assertSame('News teaser', $result['abstract']);
        self::assertArrayNotHasKey('location', $result);
    }

    #[Test]
    public function createReturnsNullForMissingOriginalObject(): void
    {
        $index = self::createStub(Index::class);
        $index->method('getOriginalObject')->willReturn(null);

        self::assertNull($this->subject->create($index, self::createStub(UriBuilder::class), 123));
    }

    #[Test]
    public function createReturnsNullForMissingStartDate(): void
    {
        $event = new Event();
        $index = self::createStub(Index::class);
        $index->method('getOriginalObject')->willReturn($event);
        $index->method('getStartDateComplete')->willReturn(null);

        self::assertNull($this->subject->create($index, self::createStub(UriBuilder::class), 123));
    }

    #[Test]
    public function createReturnsNullForMissingEndDate(): void
    {
        $event = new Event();
        $index = self::createStub(Index::class);
        $index->method('getOriginalObject')->willReturn($event);
        $index->method('getStartDateComplete')->willReturn(new \DateTime('2026-08-24 10:00:00+02:00'));
        $index->method('getEndDateComplete')->willReturn(null);

        self::assertNull($this->subject->create($index, self::createStub(UriBuilder::class), 123));
    }

    private function createIndexStub(
        \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $originalObject,
        \DateTime $start,
        \DateTime $end,
        bool $allDay,
        string $registerKey,
        ?int $uid = 17,
    ): Index {
        $index = self::createStub(Index::class);
        $index->method('getUid')->willReturn($uid);
        $index->method('getOriginalObject')->willReturn($originalObject);
        $index->method('getStartDateComplete')->willReturn($start);
        $index->method('getEndDateComplete')->willReturn($end);
        $index->method('isAllDay')->willReturn($allDay);
        $index->method('getUniqueRegisterKey')->willReturn($registerKey);

        return $index;
    }

    private function createUriBuilderMock(): UriBuilder
    {
        $uriBuilder = $this->createMock(UriBuilder::class);
        $uriBuilder->expects($this->exactly(2))->method('reset')->willReturnSelf();
        $uriBuilder->expects($this->exactly(2))->method('setTargetPageUid')->with(123)->willReturnSelf();
        $uriBuilder->expects($this->once())->method('setTargetPageType')->with(1573760945)->willReturnSelf();
        $uriBuilder->expects($this->exactly(2))->method('uriFor')->willReturnCallback(
            static function (
                ?string $actionName,
                ?array $controllerArguments,
                ?string $controllerName,
                ?string $extensionName,
                ?string $pluginName,
            ): string {
                self::assertSame('detail', $actionName);
                self::assertSame(['index' => 17], $controllerArguments);

                if ($extensionName === 'calendarize') {
                    self::assertSame('Calendar', $controllerName);
                    self::assertSame('calendar', $pluginName);

                    return '/calendarize/detail/17';
                }

                self::assertSame('Cal', $controllerName);
                self::assertSame('mdfullcalendar', $extensionName);
                self::assertSame('caldetail', $pluginName);

                return '/md-fullcalendar/detail/17';
            },
        );

        return $uriBuilder;
    }
}
