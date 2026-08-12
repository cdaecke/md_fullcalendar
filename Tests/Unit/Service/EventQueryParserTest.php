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

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function invalidParametersProvider(): iterable
    {
        yield 'missing start' => [[
            'end' => '2026-08-17T00:00:00+02:00',
        ]];
        yield 'invalid start' => [[
            'start' => 'next Thursday',
            'end' => '2026-08-17T00:00:00+02:00',
        ]];
        yield 'end before start' => [[
            'start' => '2026-08-17T00:00:00+02:00',
            'end' => '2026-08-10T00:00:00+02:00',
        ]];
        yield 'non-numeric storage page' => [[
            'start' => '2026-08-10T00:00:00+02:00',
            'end' => '2026-08-17T00:00:00+02:00',
            'storage' => '12,invalid',
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
