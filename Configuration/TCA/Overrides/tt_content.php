<?php

defined('TYPO3_MODE') || die();

$_EXTKEY = 'md_fullcalendar';

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $_EXTKEY,
    'Cal',
    'LLL:EXT:md_fullcalendar/Resources/Private/Language/locallang_db.xlf:tx_md_fullcalendar_cal.name'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('md_fullcalendar', 'Configuration/TypoScript', 'Md FullCalendar');

# Prepare plugin's signature
$extensionName = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY));
$pluginName = strtolower('Cal');
$pluginSignature = $extensionName.'_'.$pluginName;

# Add list_type to tt_content
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';

# Add Flexform
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:'.$_EXTKEY . '/Configuration/FlexForms/CalPlugin.xml'
);