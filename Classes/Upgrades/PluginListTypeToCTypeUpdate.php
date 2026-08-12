<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Upgrades;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

#[UpgradeWizard('mdFullcalendar_pluginListTypeToCTypeUpdate')]
final class PluginListTypeToCTypeUpdate extends AbstractListTypeToCTypeUpdate
{
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'mdfullcalendar_cal' => 'mdfullcalendar_cal',
        ];
    }

    public function getTitle(): string
    {
        return 'Migrate the FullCalendar plugin to a dedicated content type';
    }

    public function getDescription(): string
    {
        return 'Migrates existing FullCalendar content elements from list_type to CType.';
    }
}
