<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

(static function (): void {
    ExtensionManagementUtility::addTcaSelectItemGroup(
        'tt_content',
        'CType',
        'gastgeber',
        'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:contentElement.group.gastgeber',
        'after:plugins'
    );

    $typo3MajorVersion = GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion();

    $plugins = [
        'List' => [
            'title' => 'plugin.list.title',
            'description' => 'plugin.list.description',
            'icon' => 'EXT:gastgeber/Resources/Public/Icons/Extension.svg',
            'flexForm' => 'FILE:EXT:gastgeber/Configuration/FlexForms/List.xml',
        ],
        'Detail' => [
            'title' => 'plugin.detail.title',
            'description' => 'plugin.detail.description',
            'icon' => 'EXT:gastgeber/Resources/Public/Icons/Extension.svg',
            'flexForm' => 'FILE:EXT:gastgeber/Configuration/FlexForms/Detail.xml',
        ],
        'Filter' => [
            'title' => 'plugin.filter.title',
            'description' => 'plugin.filter.description',
            'icon' => 'EXT:gastgeber/Resources/Public/Icons/Extension.svg',
            'flexForm' => 'FILE:EXT:gastgeber/Configuration/FlexForms/Filter.xml',
        ],
    ];

    foreach ($plugins as $pluginName => $configuration) {
        if ($typo3MajorVersion >= 14) {
            $pluginKey = ExtensionUtility::registerPlugin(
                'Gastgeber',
                $pluginName,
                'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:' . $configuration['title'],
                $configuration['icon'],
                'gastgeber',
                'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:' . $configuration['description'],
                $configuration['flexForm']
            );
        } else {
            $pluginKey = ExtensionUtility::registerPlugin(
                'Gastgeber',
                $pluginName,
                'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:' . $configuration['title'],
                $configuration['icon'],
                'gastgeber',
                'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:' . $configuration['description']
            );
        }

        $GLOBALS['TCA']['tt_content']['types'][$pluginKey]['showitem'] = implode(',', [
            '--palette--;;headers',
            '--div--;LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:flexform.tab.configuration',
            'pi_flexform',
            '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance',
            '--palette--;;frames',
            '--palette--;;appearanceLinks',
            '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language',
            '--palette--;;language',
            '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access',
            '--palette--;;hidden',
            '--palette--;;access',
            '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories',
            'categories',
            '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes',
            'rowDescription',
        ]);

        if ($typo3MajorVersion >= 14) {
            // TYPO3 v14 uses the new single-entry FlexForm data structure format.
            // The 7th registerPlugin() argument already registers the FlexForm, this
            // explicit assignment keeps the configuration stable if the showitem list
            // is customized by project code.
            $GLOBALS['TCA']['tt_content']['types'][$pluginKey]['columnsOverrides']['pi_flexform']['config']['ds'] = $configuration['flexForm'];
        } else {
            // TYPO3 v13.4 still expects the legacy FlexForm registration path for CType plugins.
            // This creates the required ['ds']['default'] structure internally and prevents
            // FlexFormTools from seeing a string in the v13 TCA schema.
            ExtensionManagementUtility::addPiFlexFormValue(
                '*',
                $configuration['flexForm'],
                $pluginKey
            );
        }

        $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$pluginKey] = 'content-plugin';
    }
})();
