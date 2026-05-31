<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

(static function (): void {
    $pluginKey = ExtensionUtility::registerPlugin(
        'Gastgeber',
        'Filter',
        'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:plugin.filter.title',
        'EXT:gastgeber/Resources/Public/Icons/Extension.svg',
        'plugins',
        'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:plugin.filter.description'
    );

    ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:gastgeber/Configuration/FlexForms/Filter.xml',
        $pluginKey
    );

    $GLOBALS['TCA']['tt_content']['types'][$pluginKey]['showitem'] = '
        --palette--;;headers,
        pi_flexform,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
        --palette--;;hidden,
        --palette--;;access
    ';
})();
