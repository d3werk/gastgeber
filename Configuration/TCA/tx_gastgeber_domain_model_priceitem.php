<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'Preiszeile / Preisbeschreibung',
        'label' => 'title',
        'label_alt' => 'row_type,description',
        'label_alt_force' => false,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'sortby' => 'sorting',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'title,description',
        'iconfile' => 'EXT:gastgeber/Resources/Public/Icons/Extension.svg',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --palette--;;visibility,
                row_type, title, description,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language
            ',
        ],
    ],
    'palettes' => [
        'visibility' => [
            'label' => 'Sichtbarkeit',
            'showitem' => 'hidden',
        ],
        'language' => [
            'label' => 'Sprache',
            'showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource',
        ],
    ],
    'columns' => [
        'sys_language_uid' => [
            'label' => 'Sprache',
            'config' => [
                'type' => 'language',
            ],
        ],
        'l10n_parent' => [
            'label' => 'Übersetzung von',
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_gastgeber_domain_model_priceitem',
                'foreign_table_where' => 'AND {#tx_gastgeber_domain_model_priceitem}.{#pid}=###CURRENT_PID### AND {#tx_gastgeber_domain_model_priceitem}.{#sys_language_uid} IN (-1,0)',
                'default' => 0,
            ],
        ],
        'l10n_diffsource' => [
            'label' => 'Übersetzungsänderungen',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'hidden' => [
            'label' => 'Deaktivieren',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
        'host' => [
            'label' => 'Zugehöriger Gastgeber',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'row_type' => [
            'label' => 'Zeilentyp',
            'description' => 'Normale Preiszeile, Zwischenüberschrift oder Hinweiszeile für besondere Preisblöcke.',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => 'Preiszeile: Leistung links, Preis/Beschreibung rechts', 'value' => 'price'],
                    ['label' => 'Zwischenüberschrift / Abschnitt', 'value' => 'heading'],
                    ['label' => 'Hinweis über beide Tabellenspalten', 'value' => 'note'],
                ],
                'default' => 'price',
            ],
        ],
        'title' => [
            'label' => 'Leistung / Bezeichnung',
            'description' => 'Linke Tabellenspalte, z. B. „Doppelzimmer“, „Babybett“, „Ferienwohnung OG (2–4 Personen)“. Mehrzeilige Eingaben sind möglich.',
            'config' => [
                'type' => 'text',
                'rows' => 2,
                'cols' => 40,
                'eval' => 'trim',
            ],
        ],
        'description' => [
            'label' => 'Preis / Beschreibung',
            'description' => 'Rechte Tabellenspalte. Hier können Preise, mehrzeilige Bedingungen, Zusatzkosten oder Erläuterungen gepflegt werden.',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'richtextConfiguration' => 'default',
                'rows' => 5,
                'cols' => 80,
                'softref' => 'typolink_tag,email[subst],url',
            ],
        ],
    ],
];
