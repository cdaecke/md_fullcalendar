<?php

defined('TYPO3') or die();

call_user_func(
    function()
    {
        /**
         * Register plugin
         */
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'MdFullcalendar',
            'Cal',
            'LLL:EXT:md_fullcalendar/Resources/Private/Language/locallang_db.xlf:tx_md_fullcalendar_cal.name',
            'md_fullcalendar-plugin-cal',
            null,
            'LLL:EXT:md_fullcalendar/Resources/Private/Language/locallang_db.xlf:tx_md_fullcalendar_cal.description',
        );

        /**
         * Load flexform for cal plugin
         */
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['mdfullcalendar_cal'] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
            'mdfullcalendar_cal',
            'FILE:EXT:md_fullcalendar/Configuration/FlexForms/CalPlugin.xml'
        );

    }
);
