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
    'Filter' => [
        'title' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:plugin.filter.title',
        'description' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:plugin.filter.description',
        'flexForm' => 'FILE:EXT:gastgeber/Configuration/FlexForms/Filter.xml',
    ],
];

$registerPluginReflection = new \ReflectionMethod(ExtensionUtility::class, 'registerPlugin');
$supportsFlexFormArgument = $registerPluginReflection->getNumberOfParameters() >= 7;

foreach ($plugins as $pluginName => $configuration) {
    $pluginKey = 'gastgeber_' . strtolower($pluginName);

    if ($supportsFlexFormArgument) {
        ExtensionUtility::registerPlugin(
            'Gastgeber',
            $pluginName,
            $configuration['title'],
            'content-widget',
            'gastgeber',
            $configuration['description'],
            $configuration['flexForm']
        );
    } else {
        ExtensionUtility::registerPlugin(
            'Gastgeber',
            $pluginName,
            $configuration['title'],
            'content-widget',
            'gastgeber',
            $configuration['description']
        );

        // TYPO3 13.4 still needs this registration for CType based Extbase plugins.
        // The first argument must be "*" because pi_flexform is already configured as type=flex.
        ExtensionManagementUtility::addPiFlexFormValue('*', $configuration['flexForm'], $pluginKey);
    }

    $GLOBALS['TCA']['tt_content']['types'][$pluginKey]['showitem'] = '
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
