<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Service;

/**
 * Converts categories of a Calendarize original object into FullCalendar CSS class names.
 */
final readonly class CategoryClassResolver
{
    public function __construct(private ObjectPropertyReader $propertyReader) {}

    /**
     * @return list<string>
     */
    public function resolve(object $event): array
    {
        $cssClasses = [];
        foreach ($this->propertyReader->readIterable($event, 'getCategories') as $category) {
            $uid = is_object($category) ? $this->propertyReader->readPositiveInt($category, 'getUid') : 0;
            if ($uid > 0) {
                $cssClasses[] = 'category' . $uid;
            }
        }

        return $cssClasses;
    }
}
