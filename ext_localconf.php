<?php
defined('TYPO3_MODE') || die('Access denied.');

$_EXTKEY = 'md_fullcalendar';

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    $_EXTKEY,
    'Cal',
    [
        \Mediadreams\MdFullcalendar\Controller\CalController::class => 'show, list, detail',
    ],
    // non-cacheable actions
    [
        \Mediadreams\MdFullcalendar\Controller\CalController::class => '',
    ],
);
