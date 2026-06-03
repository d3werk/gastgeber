<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
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

$registerPluginReflection = new \ReflectionMethod(ExtensionUtility::class, 'registerPlugin');
$supportsFlexFormArgument = $registerPluginReflection->getNumberOfParameters() >= 7;

foreach ($plugins as $pluginName => $configuration) {
    $ctypeKey = 'gastgeber_' . strtolower($pluginName);
    if ($supportsFlexFormArgument) {
        ExtensionUtility::registerPlugin(
            'Gastgeber',
            $pluginName,
            $configuration['title'],
            'ext-gastgeber',
            'gastgeber',
            $configuration['description'],
            $configuration['flexForm']
        );
    } else {
        ExtensionUtility::registerPlugin(
            'Gastgeber',
            $pluginName,
            $configuration['title'],
            'ext-gastgeber',
            'gastgeber',
            $configuration['description']
        );
        ExtensionManagementUtility::addPiFlexFormValue('*', $configuration['flexForm'], $ctypeKey);
    }

    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_content',
        '--div--;LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:tabs.plugin,pi_flexform,',
        $ctypeKey,
        'after:header'
    );

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
