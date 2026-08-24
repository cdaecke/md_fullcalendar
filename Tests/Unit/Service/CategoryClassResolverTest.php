<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Tests\Unit\Service;

use Mediadreams\MdFullcalendar\Service\CategoryClassResolver;
use Mediadreams\MdFullcalendar\Service\ObjectPropertyReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(CategoryClassResolver::class)]
#[CoversClass(ObjectPropertyReader::class)]
final class CategoryClassResolverTest extends UnitTestCase
{
    private CategoryClassResolver $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new CategoryClassResolver(new ObjectPropertyReader());
    }

    #[Test]
    public function resolveReturnsCssClassesForValidCategoryUids(): void
    {
        $event = new class {
            /** @return list<object> */
            public function getCategories(): array
            {
                return [
                    new class {
                        public function getUid(): int
                        {
                            return 12;
                        }
                    },
                    new class {
                        public function getUid(): int
                        {
                            return 23;
                        }
                    },
                ];
            }
        };

        self::assertSame(['category12', 'category23'], $this->subject->resolve($event));
    }

    #[Test]
    public function resolveIgnoresCategoriesWithoutPositiveIntegerUid(): void
    {
        $event = new class {
            /** @return list<mixed> */
            public function getCategories(): array
            {
                return [
                    new \stdClass(),
                    new class {
                        public function getUid(): int
                        {
                            return 0;
                        }
                    },
                    new class {
                        public function getUid(): string
                        {
                            return '42';
                        }
                    },
                    'not an object',
                ];
            }
        };

        self::assertSame([], $this->subject->resolve($event));
    }

    #[Test]
    public function resolveReturnsEmptyArrayForMissingCategoriesGetter(): void
    {
        self::assertSame([], $this->subject->resolve(new \stdClass()));
    }
}
