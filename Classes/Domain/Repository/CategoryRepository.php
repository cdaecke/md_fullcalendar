<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Domain\Repository;

/***
 *
 * This file is part of the "FullCalendar.io for ext:Calendarize" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2019 Christoph Daecke
 *
 ***/

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository for Category models.
 */
class CategoryRepository extends Repository
{
    protected $defaultOrderings = [
        'sorting' => QueryInterface::ORDER_ASCENDING,
        'uid' => QueryInterface::ORDER_ASCENDING,
    ];
}
