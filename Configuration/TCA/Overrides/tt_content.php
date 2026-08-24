<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

$extensionName = 'MdFullcalendar';
$pluginName = 'Cal';
$pluginTitle = 'LLL:EXT:md_fullcalendar/Resources/Private/Language/locallang_db.xlf:tx_md_fullcalendar_cal.name';
$pluginDescription = 'LLL:EXT:md_fullcalendar/Resources/Private/Language/locallang_db.xlf:tx_md_fullcalendar_cal.description';
$pluginIcon = 'md_fullcalendar-plugin-cal';
$flexForm = 'FILE:EXT:md_fullcalendar/Configuration/FlexForms/CalPlugin.xml';

if ((new Typo3Version())->getMajorVersion() >= 14) {
    $pluginSignature = ExtensionUtility::registerPlugin(
        $extensionName,
        $pluginName,
        $pluginTitle,
        $pluginIcon,
        'plugins',
        $pluginDescription,
        $flexForm,
    );

    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_content',
        'pages,recursive',
        $pluginSignature,
        'after:pi_flexform',
    );
} else {
    $pluginSignature = ExtensionUtility::registerPlugin(
        $extensionName,
        $pluginName,
        $pluginTitle,
        $pluginIcon,
        'plugins',
        $pluginDescription,
    );

    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_content',
        '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:plugin,pi_flexform,pages,recursive',
        $pluginSignature,
        'after:subheader',
    );
    ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        $flexForm,
        $pluginSignature,
    );
}
