<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Tests\Unit\Service;

use Mediadreams\MdFullcalendar\Service\ObjectPropertyReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ObjectPropertyReader::class)]
final class ObjectPropertyReaderTest extends UnitTestCase
{
    private ObjectPropertyReader $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new ObjectPropertyReader();
    }

    #[Test]
    public function readStringReturnsValueFromCallableGetter(): void
    {
        $object = new class {
            public function getTitle(): string
            {
                return 'Tea time';
            }
        };

        self::assertSame('Tea time', $this->subject->readString($object, 'getTitle'));
    }

    #[Test]
    public function readStringReturnsEmptyStringForMissingGetter(): void
    {
        self::assertSame('', $this->subject->readString(new \stdClass(), 'getTitle'));
    }

    #[Test]
    public function readStringReturnsEmptyStringForNonStringValue(): void
    {
        $object = new class {
            public function getTitle(): int
            {
                return 123;
            }
        };

        self::assertSame('', $this->subject->readString($object, 'getTitle'));
    }

    #[Test]
    public function readIterableReturnsValueFromCallableGetter(): void
    {
        $items = [new \stdClass(), new \stdClass()];
        $object = new class ($items) {
            /** @param list<object> $items */
            public function __construct(private readonly array $items) {}

            /** @return list<object> */
            public function getItems(): array
            {
                return $this->items;
            }
        };

        self::assertSame($items, $this->subject->readIterable($object, 'getItems'));
    }

    #[Test]
    public function readIterableReturnsEmptyArrayForMissingGetter(): void
    {
        self::assertSame([], $this->subject->readIterable(new \stdClass(), 'getItems'));
    }

    #[Test]
    public function readIterableReturnsEmptyArrayForNonIterableValue(): void
    {
        $object = new class {
            public function getItems(): string
            {
                return 'not iterable';
            }
        };

        self::assertSame([], $this->subject->readIterable($object, 'getItems'));
    }

    #[Test]
    public function readPositiveIntReturnsValueFromCallableGetter(): void
    {
        $object = new class {
            public function getUid(): int
            {
                return 42;
            }
        };

        self::assertSame(42, $this->subject->readPositiveInt($object, 'getUid'));
    }

    #[Test]
    public function readPositiveIntReturnsZeroForMissingGetter(): void
    {
        self::assertSame(0, $this->subject->readPositiveInt(new \stdClass(), 'getUid'));
    }

    #[Test]
    public function readPositiveIntReturnsZeroForNonPositiveInteger(): void
    {
        $object = new class {
            public function getUid(): int
            {
                return -1;
            }
        };

        self::assertSame(0, $this->subject->readPositiveInt($object, 'getUid'));
    }

    #[Test]
    public function readPositiveIntReturnsZeroForNumericString(): void
    {
        $object = new class {
            public function getUid(): string
            {
                return '42';
            }
        };

        self::assertSame(0, $this->subject->readPositiveInt($object, 'getUid'));
    }
}
