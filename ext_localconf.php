<?php

defined('TYPO3') or die();

call_user_func(
    function()
    {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'MdFullcalendar',
            'Cal',
            [
                \Mediadreams\MdFullcalendar\Controller\CalController::class => 'show, list, detail'
            ],
            // non-cacheable actions
            [
                \Mediadreams\MdFullcalendar\Controller\CalController::class => ''
            ]
        );

        // Will be used for the ajax calls
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'MdFullcalendar',
            'CalList',
            [
                \Mediadreams\MdFullcalendar\Controller\CalController::class => 'list'
            ],
            // non-cacheable actions
            [
                \Mediadreams\MdFullcalendar\Controller\CalController::class => ''
            ]
        );

        // Will be used for the ajax calls
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'MdFullcalendar',
            'CalDetail',
            [
                \Mediadreams\MdFullcalendar\Controller\CalController::class => 'detail'
            ],
            // non-cacheable actions
            [
                \Mediadreams\MdFullcalendar\Controller\CalController::class => ''
            ]
        );

    }
);
