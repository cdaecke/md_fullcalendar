<?php

declare(strict_types=1);

namespace Mediadreams\MdFullcalendar\Upgrades;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Upgrade flexform structure in database to be compatible with TYPO3 v13
 * TODO: This Wizard can be removed as soon as support for TYPO3 v12 is dropped
 */
#[UpgradeWizard('mdFullcalendar_flexformUpgradeWizard')]
final class FlexformUpgradeWizard implements UpgradeWizardInterface
{
    /**
     * Return the speaking name of this wizard
     */
    public function getTitle(): string
    {
        return 'ext:md_fullcalendar: Update flexform settings';
    }

    /**
     * Return the description for this wizard
     */
    public function getDescription(): string
    {
        return 'Migrate flexform data in database for the plugin of ext:md_fullcalendar.';
    }

    /**
     * Is an update necessary?
     *
     * Is used to determine whether a wizard needs to be run.
     * Check if data for migration exists.
     *
     * This update is necessary, if the `list_type` equals 'mdfullcalendar_cal' AND `pi_flexform` contains the string 'ewsfeusers_users'
     *
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function updateNecessary(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');

        $queryBuilder->getRestrictions()->removeAll();
        $res = (int)$queryBuilder->count('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->and(
                    $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter('mdfullcalendar_cal', Connection::PARAM_STR)),
                    $queryBuilder->expr()->like('pi_flexform', $queryBuilder->createNamedParameter('%ewsfeusers_users%', Connection::PARAM_STR))
                )
            )
            ->executeQuery()->fetchOne();

        return $res > 0;
    }

    /**
     * Execute the update
     *
     * Called when a wizard reports that an update is necessary
     *
     * The boolean indicates whether the update was successful
     */
    public function executeUpdate(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');

        $queryBuilder->getRestrictions()->removeAll();
        $res = $queryBuilder
            ->update('tt_content')
            ->where(
                $queryBuilder->expr()->and(
                    $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter('mdfullcalendar_cal', Connection::PARAM_STR)),
                    $queryBuilder->expr()->like('pi_flexform', $queryBuilder->createNamedParameter('%ewsfeusers_users%', Connection::PARAM_STR))
                )
            )
            ->set(
                'tt_content.pi_flexform',
                sprintf(
                    'REPLACE(tt_content.pi_flexform, %s, %s)',
                    $queryBuilder->createNamedParameter('ewsfeusers_users', Connection::PARAM_STR),
                    $queryBuilder->createNamedParameter('sDEF', Connection::PARAM_STR)
                ),
                false
            )
            ->executeStatement();

        return $res > 0;
    }

    /**
     * Returns an array of class names of prerequisite classes
     *
     * This way a wizard can define dependencies like "database up-to-date" or
     * "reference index updated"
     *
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        // Add your logic here
    }
}
