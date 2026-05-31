<?php

declare(strict_types=1);

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$ll = 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:';

$additionalColumns = [
    'tx_gastgeber_street' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_street',
        'config' => [
            'type' => 'input',
            'size' => 40,
            'max' => 255,
            'eval' => 'trim',
            'placeholder' => 'Wilseder Str. 13',
        ],
    ],
    'tx_gastgeber_address_addition' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_address_addition',
        'config' => [
            'type' => 'input',
            'size' => 40,
            'max' => 255,
            'eval' => 'trim',
            'placeholder' => 'z. B. Hausname, Ortsteil, Lagehinweis',
        ],
    ],
    'tx_gastgeber_zip' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_zip',
        'config' => [
            'type' => 'input',
            'size' => 10,
            'max' => 20,
            'eval' => 'trim',
            'default' => '21274',
            'placeholder' => '21274',
        ],
    ],
    'tx_gastgeber_city' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_city',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'max' => 255,
            'eval' => 'trim',
            'default' => 'Undeloh',
            'placeholder' => 'Undeloh',
        ],
    ],
    'tx_gastgeber_country' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_country',
        'config' => [
            'type' => 'input',
            'size' => 20,
            'max' => 80,
            'eval' => 'trim',
            'default' => 'Deutschland',
            'placeholder' => 'Deutschland',
        ],
    ],
    'tx_gastgeber_phone' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_phone',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'max' => 80,
            'eval' => 'trim',
            'placeholder' => '04189 333',
        ],
    ],
    'tx_gastgeber_email' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_email',
        'config' => [
            'type' => 'email',
            'size' => 40,
            'placeholder' => 'info@undeloh.de',
        ],
    ],
    'tx_gastgeber_website' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_website',
        'config' => [
            'type' => 'link',
            'size' => 40,
            'allowedTypes' => ['url', 'page'],
            'placeholder' => 'https://www.example.de',
        ],
    ],
    'tx_gastgeber_booking_url' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_booking_url',
        'config' => [
            'type' => 'link',
            'size' => 40,
            'allowedTypes' => ['url', 'page', 'email'],
            'placeholder' => 'https://www.example.de/anfrage',
        ],
    ],
    'tx_gastgeber_price_from' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_price_from',
        'config' => [
            'type' => 'number',
            'format' => 'decimal',
            'range' => [
                'lower' => 0,
            ],
            'placeholder' => '85,00',
        ],
    ],
    'tx_gastgeber_price_note' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_price_note',
        'config' => [
            'type' => 'text',
            'cols' => 40,
            'rows' => 3,
            'enableRichtext' => true,
            'placeholder' => 'Preis je Zimmer/FW pro Tag, inkl. Endreinigung, Mindestaufenthalt usw.',
        ],
    ],
    'tx_gastgeber_capacity_people' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_capacity_people',
        'config' => [
            'type' => 'number',
            'range' => [
                'lower' => 0,
            ],
            'placeholder' => '2',
        ],
    ],
    'tx_gastgeber_rooms' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_rooms',
        'config' => [
            'type' => 'number',
            'range' => [
                'lower' => 0,
            ],
            'placeholder' => '1',
        ],
    ],
    'tx_gastgeber_beds' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_beds',
        'config' => [
            'type' => 'number',
            'range' => [
                'lower' => 0,
            ],
            'placeholder' => '4',
        ],
    ],
    'tx_gastgeber_latitude' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_latitude',
        'description' => $ll . 'tx_news_domain_model_news.tx_gastgeber_latitude.description',
        'config' => [
            'type' => 'number',
            'format' => 'decimal',
            'placeholder' => '53.1966000',
        ],
    ],
    'tx_gastgeber_longitude' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_longitude',
        'description' => $ll . 'tx_news_domain_model_news.tx_gastgeber_longitude.description',
        'config' => [
            'type' => 'number',
            'format' => 'decimal',
            'placeholder' => '9.9762000',
        ],
    ],
    'tx_gastgeber_show_on_map' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_show_on_map',
        'description' => $ll . 'tx_news_domain_model_news.tx_gastgeber_show_on_map.description',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'default' => 1,
        ],
    ],
    'tx_gastgeber_opening_times' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_opening_times',
        'config' => [
            'type' => 'text',
            'cols' => 40,
            'rows' => 3,
            'placeholder' => 'ganzjährig geöffnet, Saisonzeiten, Ruhetage, Anreisezeiten …',
        ],
    ],
    'tx_gastgeber_equipment' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_equipment',
        'description' => $ll . 'tx_news_domain_model_news.tx_gastgeber_equipment.description',
        'config' => [
            'type' => 'text',
            'cols' => 40,
            'rows' => 5,
            'enableRichtext' => true,
            'placeholder' => 'Freitext für Ausstattung. Filterbare Merkmale bitte zusätzlich über Kategorien auswählen.',
        ],
    ],
    'tx_gastgeber_seo_title' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_seo_title',
        'description' => $ll . 'tx_news_domain_model_news.tx_gastgeber_seo_title.description',
        'config' => [
            'type' => 'input',
            'size' => 50,
            'max' => 255,
            'eval' => 'trim',
            'placeholder' => 'Hotel Heiderose in Undeloh | Unterkunft Lüneburger Heide',
        ],
    ],
    'tx_gastgeber_seo_description' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_seo_description',
        'description' => $ll . 'tx_news_domain_model_news.tx_gastgeber_seo_description.description',
        'config' => [
            'type' => 'text',
            'cols' => 50,
            'rows' => 3,
            'placeholder' => 'Kurzbeschreibung für Google & Co. Empfehlung: ca. 140–160 Zeichen.',
        ],
    ],
    'tx_gastgeber_focus_keyword' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_focus_keyword',
        'description' => $ll . 'tx_news_domain_model_news.tx_gastgeber_focus_keyword.description',
        'config' => [
            'type' => 'input',
            'size' => 40,
            'max' => 255,
            'eval' => 'trim',
            'placeholder' => 'z. B. Ferienwohnung Undeloh mit Hund',
        ],
    ],
    'tx_gastgeber_og_title' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_og_title',
        'description' => $ll . 'tx_news_domain_model_news.tx_gastgeber_og_title.description',
        'config' => [
            'type' => 'input',
            'size' => 50,
            'max' => 255,
            'eval' => 'trim',
            'placeholder' => 'Optional abweichender Titel für Teilen in sozialen Medien',
        ],
    ],
    'tx_gastgeber_og_description' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_og_description',
        'description' => $ll . 'tx_news_domain_model_news.tx_gastgeber_og_description.description',
        'config' => [
            'type' => 'text',
            'cols' => 50,
            'rows' => 3,
            'placeholder' => 'Optional abweichender Beschreibungstext für Facebook, WhatsApp, LinkedIn usw.',
        ],
    ],
    'tx_gastgeber_seo_noindex' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_seo_noindex',
        'description' => $ll . 'tx_news_domain_model_news.tx_gastgeber_seo_noindex.description',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'default' => 0,
        ],
    ],
    'tx_gastgeber_certifications' => [
        'exclude' => true,
        'label' => $ll . 'tx_news_domain_model_news.tx_gastgeber_certifications',
        'config' => [
            'type' => 'text',
            'cols' => 40,
            'rows' => 3,
            'enableRichtext' => true,
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('tx_news_domain_model_news', $additionalColumns);

if (isset($GLOBALS['TCA']['tx_news_domain_model_news']['columns']['categories'])) {
    $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['categories']['description'] = $ll . 'tx_news_domain_model_news.categories.description';
}

$GLOBALS['TCA']['tx_news_domain_model_news']['palettes']['gastgeberAddress'] = [
    'label' => $ll . 'palettes.gastgeberAddress',
    'showitem' => 'tx_gastgeber_street, tx_gastgeber_address_addition, --linebreak--, tx_gastgeber_zip, tx_gastgeber_city, tx_gastgeber_country',
];
$GLOBALS['TCA']['tx_news_domain_model_news']['palettes']['gastgeberGeo'] = [
    'label' => $ll . 'palettes.gastgeberGeo',
    'showitem' => 'tx_gastgeber_show_on_map, --linebreak--, tx_gastgeber_latitude, tx_gastgeber_longitude',
];
$GLOBALS['TCA']['tx_news_domain_model_news']['palettes']['gastgeberContact'] = [
    'label' => $ll . 'palettes.gastgeberContact',
    'showitem' => 'tx_gastgeber_phone, tx_gastgeber_email, --linebreak--, tx_gastgeber_website, tx_gastgeber_booking_url',
];
$GLOBALS['TCA']['tx_news_domain_model_news']['palettes']['gastgeberCapacity'] = [
    'label' => $ll . 'palettes.gastgeberCapacity',
    'showitem' => 'tx_gastgeber_price_from, tx_gastgeber_capacity_people, --linebreak--, tx_gastgeber_rooms, tx_gastgeber_beds',
];

$GLOBALS['TCA']['tx_news_domain_model_news']['palettes']['gastgeberSeoBasics'] = [
    'label' => $ll . 'palettes.gastgeberSeoBasics',
    'showitem' => 'tx_gastgeber_seo_title, --linebreak--, tx_gastgeber_seo_description, --linebreak--, tx_gastgeber_focus_keyword',
];
$GLOBALS['TCA']['tx_news_domain_model_news']['palettes']['gastgeberSeoSocial'] = [
    'label' => $ll . 'palettes.gastgeberSeoSocial',
    'showitem' => 'tx_gastgeber_og_title, --linebreak--, tx_gastgeber_og_description, --linebreak--, tx_gastgeber_seo_noindex',
];

ExtensionManagementUtility::addToAllTCAtypes(
    'tx_news_domain_model_news',
    '--div--;' . $ll . 'tabs.gastgeberAddress,' .
    '--palette--;;gastgeberAddress,' .
    '--palette--;;gastgeberGeo,' .
    '--div--;' . $ll . 'tabs.gastgeberContact,' .
    '--palette--;;gastgeberContact,' .
    '--div--;' . $ll . 'tabs.gastgeberPrices,' .
    '--palette--;;gastgeberCapacity, tx_gastgeber_price_note,' .
    '--div--;' . $ll . 'tabs.gastgeberEquipment,' .
    'tx_gastgeber_equipment, tx_gastgeber_opening_times, tx_gastgeber_certifications,' .
    '--div--;' . $ll . 'tabs.gastgeberSeo,' .
    '--palette--;;gastgeberSeoBasics,' .
    '--palette--;;gastgeberSeoSocial'
);
