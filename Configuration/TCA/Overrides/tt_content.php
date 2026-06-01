<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
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
        $pluginKey = ExtensionUtility::registerPlugin(
            'Gastgeber',
            $pluginName,
            'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:' . $configuration['title'],
            $configuration['icon'],
            'gastgeber',
            'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:' . $configuration['description']
        );

        $GLOBALS['TCA']['tt_content']['types'][$pluginKey]['showitem'] = implode(',', [
            '--palette--;;headers',
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

        $GLOBALS['TCA']['tt_content']['types'][$pluginKey]['columnsOverrides']['pi_flexform']['config']['ds'] = [
            'default' => $configuration['flexForm'],
        ];
        $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$pluginKey] = 'content-plugin';
    }
})();
