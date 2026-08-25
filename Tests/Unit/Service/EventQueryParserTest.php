<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Tests\Unit\Service;

use Mediadreams\MdFullcalendar\Service\EventQueryParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(EventQueryParser::class)]
final class EventQueryParserTest extends UnitTestCase
{
    private EventQueryParser $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new EventQueryParser();
    }

    #[Test]
    public function parsesAndExpandsAValidTimeRange(): void
    {
        $result = $this->subject->parse([
            'start' => '2026-08-10T00:00:00+02:00',
            'end' => '2026-08-17T00:00:00+02:00',
            'storage' => '12, 23,12',
        ]);

        self::assertSame('2026-08-09T00:00:00+02:00', $result->start->format(\DateTimeInterface::ATOM));
        self::assertSame('2026-08-18T23:59:59+02:00', $result->end->format(\DateTimeInterface::ATOM));
        self::assertSame([12, 23], $result->storagePageIds);
    }

    #[Test]
    public function limitsTheExpandedRangeToFiftyDays(): void
    {
        $result = $this->subject->parse([
            'start' => '2026-01-01T00:00:00+01:00',
            'end' => '2026-12-31T00:00:00+01:00',
        ]);

        self::assertSame(50, $result->start->diff($result->end)->days);
    }

    #[Test]
    public function keepsAnExpandedRangeOfExactlyFiftyDays(): void
    {
        $result = $this->subject->parse([
            'start' => '2026-01-02T00:00:00+01:00',
            'end' => '2026-02-19T00:00:00+01:00',
        ]);

        self::assertSame('2026-01-01T00:00:00+01:00', $result->start->format(\DateTimeInterface::ATOM));
        self::assertSame('2026-02-20T23:59:59+01:00', $result->end->format(\DateTimeInterface::ATOM));
        self::assertSame(50, $result->start->diff($result->end)->days);
    }

    #[Test]
    public function emptyStoragePageParameterResultsInNull(): void
    {
        $result = $this->subject->parse([
            'start' => '2026-08-10T00:00:00+02:00',
            'end' => '2026-08-17T00:00:00+02:00',
            'storage' => '',
        ]);

        self::assertNull($result->storagePageIds);
    }

    #[Test]
    public function missingStoragePageParameterResultsInNull(): void
    {
        $result = $this->subject->parse([
            'start' => '2026-08-10T00:00:00+02:00',
            'end' => '2026-08-17T00:00:00+02:00',
        ]);

        self::assertNull($result->storagePageIds);
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function invalidParametersProvider(): iterable
    {
        yield 'missing start' => [[
            'end' => '2026-08-17T00:00:00+02:00',
        ]];
        yield 'missing end' => [[
            'start' => '2026-08-10T00:00:00+02:00',
        ]];
        yield 'invalid start' => [[
            'start' => 'next Thursday',
            'end' => '2026-08-17T00:00:00+02:00',
        ]];
        yield 'invalid end' => [[
            'start' => '2026-08-10T00:00:00+02:00',
            'end' => 'next Thursday',
        ]];
        yield 'end before start' => [[
            'start' => '2026-08-17T00:00:00+02:00',
            'end' => '2026-08-10T00:00:00+02:00',
        ]];
        yield 'end equals start' => [[
            'start' => '2026-08-17T00:00:00+02:00',
            'end' => '2026-08-17T00:00:00+02:00',
        ]];
        yield 'start without timezone' => [[
            'start' => '2026-08-10T00:00:00',
            'end' => '2026-08-17T00:00:00+02:00',
        ]];
        yield 'non-numeric storage page' => [[
            'start' => '2026-08-10T00:00:00+02:00',
            'end' => '2026-08-17T00:00:00+02:00',
            'storage' => '12,invalid',
        ]];
        yield 'zero storage page' => [[
            'start' => '2026-08-10T00:00:00+02:00',
            'end' => '2026-08-17T00:00:00+02:00',
            'storage' => '0',
        ]];
        yield 'negative storage page' => [[
            'start' => '2026-08-10T00:00:00+02:00',
            'end' => '2026-08-17T00:00:00+02:00',
            'storage' => '-1',
        ]];
        yield 'empty storage page in list' => [[
            'start' => '2026-08-10T00:00:00+02:00',
            'end' => '2026-08-17T00:00:00+02:00',
            'storage' => '12,,23',
        ]];
        yield 'non-string storage pages' => [[
            'start' => '2026-08-10T00:00:00+02:00',
            'end' => '2026-08-17T00:00:00+02:00',
            'storage' => [12, 23],
        ]];
    }

    /** @param array<string, mixed> $parameters */
    #[Test]
    #[DataProvider('invalidParametersProvider')]
    public function rejectsInvalidParameters(array $parameters): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->parse($parameters);
    }
}
