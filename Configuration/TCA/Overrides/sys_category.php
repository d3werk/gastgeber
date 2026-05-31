<?php

declare(strict_types=1);

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$ll = 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:';

$additionalColumns = [
    'tx_gastgeber_icon' => [
        'exclude' => true,
        'label' => $ll . 'sys_category.tx_gastgeber_icon',
        'description' => $ll . 'sys_category.tx_gastgeber_icon.description',
        'config' => [
            'type' => 'file',
            'allowed' => 'svg,png,jpg,jpeg,gif,webp',
            'maxitems' => 1,
            'minitems' => 0,
            'appearance' => [
                'createNewRelationLinkTitle' => $ll . 'sys_category.tx_gastgeber_icon.add',
            ],
        ],
    ],
    'tx_gastgeber_icon_css_class' => [
        'exclude' => true,
        'label' => $ll . 'sys_category.tx_gastgeber_icon_css_class',
        'description' => $ll . 'sys_category.tx_gastgeber_icon_css_class.description',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'max' => 120,
            'eval' => 'trim',
            'placeholder' => 'bi bi-wifi oder fa-solid fa-dog',
        ],
    ],
    'tx_gastgeber_filter_hidden' => [
        'exclude' => true,
        'label' => $ll . 'sys_category.tx_gastgeber_filter_hidden',
        'description' => $ll . 'sys_category.tx_gastgeber_filter_hidden.description',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'default' => 0,
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('sys_category', $additionalColumns);

$GLOBALS['TCA']['sys_category']['palettes']['gastgeberFeatureIcon'] = [
    'label' => $ll . 'palettes.gastgeberFeatureIcon',
    'showitem' => 'tx_gastgeber_icon, --linebreak--, tx_gastgeber_icon_css_class, --linebreak--, tx_gastgeber_filter_hidden',
];

ExtensionManagementUtility::addToAllTCAtypes(
    'sys_category',
    '--div--;' . $ll . 'tabs.gastgeberFeature, --palette--;;gastgeberFeatureIcon'
);
