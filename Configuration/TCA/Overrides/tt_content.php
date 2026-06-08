<?php

declare(strict_types=1);

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

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


$removeFieldsFromTcaList = static function (string $fieldList, array $fieldNamesToRemove): string {
    if (trim($fieldList) === '') {
        return '';
    }

    $removeMap = array_fill_keys(array_map('strtolower', $fieldNamesToRemove), true);
    $cleanedFields = [];

    foreach (array_filter(array_map('trim', explode(',', $fieldList))) as $fieldDefinition) {
        // TCA field definitions may contain additional semicolon parts, for example:
        // bodytext;Einleitungstext;;;richtext:rte_transform
        // For removal we only compare the actual database field name before the first semicolon.
        $fieldName = strtolower(trim(explode(';', $fieldDefinition, 2)[0] ?? $fieldDefinition));
        if ($fieldName === '' || isset($removeMap[$fieldName])) {
            continue;
        }
        $cleanedFields[] = $fieldDefinition;
    }

    return implode(',', array_values(array_unique($cleanedFields)));
};

// Compatibility for older content elements using "Allgemeines Plugin [list]".
// New TYPO3 13 content elements use their own CType (for example "gastgeber_list"),
// but existing migrated elements may still use CType=list with list_type=gastgeber_list.
// Register the FlexForms explicitly for these legacy list plugins and clean up the subtype
// field lists afterwards, so bodytext can never appear inside the "Plugin" tab.
foreach ($plugins as $pluginName => $configuration) {
    ExtensionManagementUtility::addPiFlexFormValue(
        'gastgeber_' . strtolower($pluginName),
        $configuration['flexForm'],
        'list'
    );
}

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
    $GLOBALS['TCA']['tt_content']['types'][$ctypeKey]['columnsOverrides']['pi_flexform'] = [
        'label' => 'Plugin-Einstellungen',
        'config' => [
            'type' => 'flex',
            'ds' => [
                'default' => $configuration['flexForm'],
            ],
        ],
    ];

    $generalFields = $pluginName === 'List'
        ? 'header, subheader, bodytext, pi_flexform,'
        : 'header, pi_flexform,';

    if ($pluginName === 'List') {
        $GLOBALS['TCA']['tt_content']['types'][$ctypeKey]['columnsOverrides']['bodytext'] = [
            'label' => 'Einleitungstext',
            'description' => 'Optionaler RTE-Text für die Gastgeber-Liste. Der Text wird oberhalb von Filter und Ergebnissen ausgegeben.',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'richtextConfiguration' => 'default',
                'cols' => 40,
                'rows' => 10,
            ],
        ];
    }

    $GLOBALS['TCA']['tt_content']['types'][$ctypeKey]['showitem'] = '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            ' . $generalFields . '
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

// Kompatibilität für Installationen, in denen das Plugin noch als altes
// "Allgemeines Plugin [list]" mit list_type=gastgeber_list eingebunden ist.
// Das RTE-Feld erscheint dort im Reiter "Allgemein" direkt unter der Unterüberschrift.
if (isset($GLOBALS['TCA']['tt_content']['types']['list'])) {
    $listShowitem = (string)($GLOBALS['TCA']['tt_content']['types']['list']['showitem'] ?? '');
    $bodytextAlreadyInListType = preg_match('/(?:^|[,;\\s])bodytext(?:[,;\\s]|$)/', $listShowitem) === 1;

    // bodytext nur in den normalen CType=list-Showitems ergänzen, nicht in subtypes_addlist.
    // addToAllTCAtypes platziert das Feld im Reiter "Allgemein" an der Stelle nach subheader.
    if (!$bodytextAlreadyInListType) {
        ExtensionManagementUtility::addToAllTCAtypes(
            'tt_content',
            'bodytext',
            'list',
            'after:subheader'
        );
    }

    // Wichtig: bodytext darf NICHT in subtypes_addlist stehen.
    // subtypes_addlist wird beim klassischen "Allgemeines Plugin [list]" im Reiter
    // "Plugin" ausgegeben. Frühere Zwischenstände konnten bodytext dort mit Label- oder
    // RTE-Zusätzen eintragen, z. B. "bodytext;Einleitungstext". Deshalb entfernen wir
    // nicht nur den exakten String "bodytext", sondern alle Einträge, deren Feldname vor
    // dem ersten Semikolon "bodytext" ist.
    foreach (['subtypes_addlist', 'subtypes_excludelist'] as $subtypeListName) {
        if (!isset($GLOBALS['TCA']['tt_content']['types']['list'][$subtypeListName])
            || !is_array($GLOBALS['TCA']['tt_content']['types']['list'][$subtypeListName])
        ) {
            continue;
        }

        foreach ($GLOBALS['TCA']['tt_content']['types']['list'][$subtypeListName] as $subtype => $fieldList) {
            $subtype = (string)$subtype;
            if ($subtype !== 'gastgeber_list' && !str_starts_with($subtype, 'gastgeber_')) {
                continue;
            }

            $GLOBALS['TCA']['tt_content']['types']['list'][$subtypeListName][$subtype]
                = $removeFieldsFromTcaList((string)$fieldList, ['bodytext']);
        }
    }

    // Nochmals explizit für die Listenansicht setzen: Im Plugin-Reiter bleibt nur die
    // FlexForm. Der Einleitungstext bleibt ausschließlich bodytext im Reiter Allgemein.
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['gastgeber_list']
        = $removeFieldsFromTcaList(
            (string)($GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['gastgeber_list'] ?? 'pi_flexform'),
            ['bodytext']
        );

    if ($GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['gastgeber_list'] === '') {
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['gastgeber_list'] = 'pi_flexform';
    }

    $GLOBALS['TCA']['tt_content']['types']['list']['columnsOverrides']['bodytext'] = [
        'label' => 'Einleitungstext',
        'description' => 'Optionaler RTE-Text für die Gastgeber-Liste. Der Text wird oberhalb von Filter und Ergebnissen ausgegeben.',
        'displayCond' => 'FIELD:list_type:=:gastgeber_list',
        'config' => [
            'type' => 'text',
            'enableRichtext' => true,
            'richtextConfiguration' => 'default',
            'cols' => 40,
            'rows' => 10,
        ],
    ];
}
