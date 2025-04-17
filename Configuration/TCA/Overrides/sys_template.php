<?php

defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'md_fullcalendar',
    'Configuration/TypoScript',
    'FullCalendar.io for ext:Calendarize'
);
