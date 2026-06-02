<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$additionalColumns = [
    'tx_gastgeber_icon' => [
        'exclude' => true,
        'label' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:category.icon',
        'config' => [
            'type' => 'file',
            'allowed' => 'common-image-types',
            'maxitems' => 1,
        ],
    ],
    'tx_gastgeber_icon_class' => [
        'exclude' => true,
        'label' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:category.icon_class',
        'description' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:category.icon_class.description',
        'config' => ['type' => 'input', 'eval' => 'trim', 'placeholder' => 'bi bi-wifi oder fa-solid fa-dog'],
    ],
    'tx_gastgeber_top_feature' => [
        'exclude' => true,
        'label' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:category.top_feature',
        'description' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:category.top_feature.description',
        'config' => ['type' => 'check', 'renderType' => 'checkboxToggle'],
    ],
    'tx_gastgeber_hide_filter' => [
        'exclude' => true,
        'label' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:category.hide_filter',
        'description' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:category.hide_filter.description',
        'config' => ['type' => 'check', 'renderType' => 'checkboxToggle'],
    ],
    'tx_gastgeber_is_filter_group' => [
        'exclude' => true,
        'label' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:category.is_filter_group',
        'description' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:category.is_filter_group.description',
        'config' => ['type' => 'check', 'renderType' => 'checkboxToggle'],
    ],
];

ExtensionManagementUtility::addTCAcolumns('sys_category', $additionalColumns);
ExtensionManagementUtility::addToAllTCAtypes(
    'sys_category',
    '--div--;LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:tab.gastgeber_filter, tx_gastgeber_icon, tx_gastgeber_icon_class, tx_gastgeber_top_feature, tx_gastgeber_hide_filter, tx_gastgeber_is_filter_group'
);
