<?php

declare(strict_types=1);

use Mediadreams\MdFullcalendar\Controller\CalController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::configurePlugin(
    'MdFullcalendar',
    'Cal',
    [CalController::class => 'show, list, detail'],
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

// Used by the event-list AJAX page type.
ExtensionUtility::configurePlugin(
    'MdFullcalendar',
    'CalList',
    [CalController::class => 'list'],
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

// Used by the event-detail AJAX page type.
ExtensionUtility::configurePlugin(
    'MdFullcalendar',
    'CalDetail',
    [CalController::class => 'detail'],
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);
