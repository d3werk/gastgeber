<?php

declare(strict_types=1);

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

$plugins = [
    'List' => [
        'title' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:plugin.list.title',
        'description' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:plugin.list.description',
        'flexForm' => 'FILE:EXT:gastgeber/Configuration/FlexForms/List.xml',
    ],
    'Detail' => [
        'title' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:plugin.detail.title',
        'description' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:plugin.detail.description',
        'flexForm' => 'FILE:EXT:gastgeber/Configuration/FlexForms/Detail.xml',
    ],
    'Map' => [
        'title' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:plugin.map.title',
        'description' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:plugin.map.description',
        'flexForm' => 'FILE:EXT:gastgeber/Configuration/FlexForms/Map.xml',
    ],
    'Teaser' => [
        'title' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:plugin.teaser.title',
        'description' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:plugin.teaser.description',
        'flexForm' => 'FILE:EXT:gastgeber/Configuration/FlexForms/Teaser.xml',
    ],
    'Categories' => [
        'title' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:plugin.categories.title',
        'description' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:plugin.categories.description',
        'flexForm' => 'FILE:EXT:gastgeber/Configuration/FlexForms/Categories.xml',
    ],
    'Filter' => [
        'title' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:plugin.filter.title',
        'description' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:plugin.filter.description',
        'flexForm' => 'FILE:EXT:gastgeber/Configuration/FlexForms/Filter.xml',
    ],
];

foreach ($plugins as $pluginName => $configuration) {
    $ctypeKey = 'gastgeber_' . strtolower($pluginName);

    ExtensionUtility::registerPlugin(
        'Gastgeber',
        $pluginName,
        $configuration['title'],
        'ext-gastgeber',
        'gastgeber',
        $configuration['description']
    );

    // TYPO3 13/14: Die FlexForm wird bewusst direkt am CType gesetzt.
    // Dadurch kann keine fremde Default-FlexForm (z. B. aus EXT:news) auf diesen CType fallen.
    $GLOBALS['TCA']['tt_content']['types'][$ctypeKey]['columnsOverrides']['pi_flexform']['config'] = [
        'type' => 'flex',
        'ds' => [
            'default' => $configuration['flexForm'],
        ],
    ];

    $GLOBALS['TCA']['tt_content']['types'][$ctypeKey]['showitem'] = '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header,
            pi_flexform,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
            --palette--;;frames,
            --palette--;;appearanceLinks,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
            --palette--;;language,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
            categories,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
            rowDescription,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended
    ';
}
