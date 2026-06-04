<?php

declare(strict_types=1);

return array (
  'ctrl' => 
  array (
    'title' => 'Gastgeber / Unterkunft',
    'label' => 'title',
    'tstamp' => 'tstamp',
    'crdate' => 'crdate',
    'cruser_id' => 'cruser_id',
    'delete' => 'deleted',
    'sortby' => 'sorting',
    'versioningWS' => true,
    'languageField' => 'sys_language_uid',
    'transOrigPointerField' => 'l10n_parent',
    'transOrigDiffSourceField' => 'l10n_diffsource',
    'enablecolumns' => 
    array (
      'disabled' => 'hidden',
      'starttime' => 'starttime',
      'endtime' => 'endtime',
    ),
    'searchFields' => 'title,teaser,description,street,zip,city,email,phone,website,external_id',
    'iconfile' => 'EXT:gastgeber/Resources/Public/Icons/Extension.svg',
    'security' => 
    array (
      'ignorePageTypeRestriction' => true,
    ),
  ),
  'types' => 
  array (
    1 => 
    array (
      'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;visibility,
                    title, slug, type, district, stars, star_superior, teaser, description, featured, priority, external_id,
                --div--;LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:tabs.media,
                    media, logo, documents, video_url,
                --div--;LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:tabs.addressMap,
                    street, address_addition, zip, city, country, show_on_map, geocode_on_save, latitude, longitude,
                --div--;LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:tabs.contactBooking,
                    contact_name, phone, mobile, email, website, booking_url, booking_text, request_email,
                --div--;LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:tabs.pricesCapacity,
                    price_from, price_info, persons, bedrooms, beds, bathrooms, size_sqm, units, season_info, capacity_note,
                --div--;LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:tabs.features,
                    features, certificates, equipment_text, opening_times, accessibility_text, sustainability_text,
                --div--;LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:tabs.seo,
                    seo_title, meta_description, og_title, og_description, noindex,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    --palette--;;access,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended
            ',
    ),
  ),
  'palettes' => 
  array (
    'visibility' => 
    array (
      'label' => 'Sichtbarkeit',
      'showitem' => 'hidden, --linebreak--, starttime, endtime',
    ),
    'language' => 
    array (
      'label' => 'Sprache',
      'showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource',
    ),
    'access' => 
    array (
      'label' => 'Zugriff',
      'showitem' => 'hidden, starttime, endtime',
    ),
  ),
  'columns' => 
  array (
    'sys_language_uid' => 
    array (
      'config' => 
      array (
        'type' => 'language',
      ),
      'label' => 'Sprache',
    ),
    'l10n_parent' => 
    array (
      'displayCond' => 'FIELD:sys_language_uid:>:0',
      'config' => 
      array (
        'type' => 'select',
        'renderType' => 'selectSingle',
        'foreign_table' => 'tx_gastgeber_domain_model_host',
        'foreign_table_where' => 'AND {#tx_gastgeber_domain_model_host}.{#pid}=###CURRENT_PID### AND {#tx_gastgeber_domain_model_host}.{#sys_language_uid} IN (-1,0)',
        'default' => 0,
      ),
      'label' => 'Übersetzung von',
    ),
    'l10n_diffsource' => 
    array (
      'config' => 
      array (
        'type' => 'passthrough',
      ),
      'label' => 'Übersetzungsänderungen',
    ),
    'hidden' => 
    array (
      'config' => 
      array (
        'type' => 'check',
        'renderType' => 'checkboxToggle',
      ),
      'label' => 'Deaktivieren',
    ),
    'starttime' => 
    array (
      'config' => 
      array (
        'type' => 'datetime',
        'default' => 0,
      ),
      'label' => 'Veröffentlichungsbeginn',
    ),
    'endtime' => 
    array (
      'config' => 
      array (
        'type' => 'datetime',
        'default' => 0,
        'range' => 
        array (
          'upper' => 2145916800,
        ),
      ),
      'label' => 'Veröffentlichungsende',
    ),
    'title' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'required' => true,
        'max' => 255,
        'eval' => 'trim',
      ),
      'label' => 'Name der Unterkunft',
      'description' => 'Offizieller Name der Unterkunft, z. B. Hotel, Ferienwohnung oder Pension.',
    ),
    'slug' => 
    array (
      'config' => 
      array (
        'type' => 'slug',
        'size' => 50,
        'generatorOptions' => 
        array (
          'fields' => 
          array (
            0 => 'title',
          ),
          'fieldSeparator' => '-',
          'replacements' => 
          array (
            '/' => '-',
          ),
        ),
        'fallbackCharacter' => '-',
        'eval' => 'uniqueInSite',
        'default' => '',
      ),
      'label' => 'URL-Segment / Slug',
      'description' => 'Wird für die sprechende Detail-URL verwendet und automatisch aus dem Namen erzeugt.',
    ),
    'type' => 
    array (
      'config' => 
      array (
        'type' => 'select',
        'renderType' => 'selectSingle',
        'foreign_table' => 'tx_gastgeber_domain_model_type',
        'minitems' => 0,
        'maxitems' => 1,
        'default' => 0,
      ),
      'label' => 'Unterkunftsart',
      'description' => 'Unterkunftsart wie Hotel, Ferienwohnung, Ferienhaus, Pension oder Camping.',
    ),
    'district' => 
    array (
      'config' => 
      array (
        'type' => 'select',
        'renderType' => 'selectSingle',
        'foreign_table' => 'tx_gastgeber_domain_model_district',
        'items' => 
        array (
          0 => 
          array (
            'label' => 'Bitte wählen',
            'value' => 0,
          ),
        ),
        'default' => 0,
      ),
      'label' => 'Ortsteil / Lage',
      'description' => 'Optionaler Ortsteil oder Lagebereich.',
    ),
    'stars' => 
    array (
      'label' => 'Sterne-Klassifizierung',
      'description' => 'Sterne direkt an der Unterkunft pflegen. Diese Auswahl wird im Bild-Badge der Listen- und Detailansicht angezeigt. Zertifikate können zusätzlich gepflegt werden.',
      'config' => 
      array (
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => 
        array (
          array('label' => 'Keine Sterne anzeigen', 'value' => 0),
          array('label' => '★ 1 Stern', 'value' => 1),
          array('label' => '★★ 2 Sterne', 'value' => 2),
          array('label' => '★★★ 3 Sterne', 'value' => 3),
          array('label' => '★★★★ 4 Sterne', 'value' => 4),
          array('label' => '★★★★★ 5 Sterne', 'value' => 5),
        ),
        'default' => 0,
      ),
    ),
    'star_superior' => 
    array (
      'label' => 'Superior-Zusatz anzeigen',
      'description' => 'Aktivieren, wenn die Sterne-Klassifizierung als „Superior“ ausgegeben werden soll, z. B. 4 Sterne Superior.',
      'config' => 
      array (
        'type' => 'check',
        'renderType' => 'checkboxToggle',
        'default' => 0,
      ),
    ),
    'teaser' => 
    array (
      'label' => 'Teaser / Kurzbeschreibung',
      'description' => 'Kurzer redaktioneller Text für Listen, Teaser-Elemente, Karten und automatische SEO-Beschreibungen. Darf Links und einfache Formatierungen enthalten.',
      'config' => 
      array (
        'type' => 'text',
        'enableRichtext' => true,
        'richtextConfiguration' => 'default',
        'rows' => 6,
        'cols' => 80,
        'softref' => 'typolink_tag,email[subst],url',
      ),
    ),
    'description' => 
    array (
      'label' => 'Beschreibung',
      'description' => 'Ausführlicher Beschreibungstext für die Detailseite.',
      'config' => 
      array (
        'type' => 'text',
        'enableRichtext' => true,
        'richtextConfiguration' => 'default',
        'rows' => 10,
        'cols' => 80,
        'softref' => 'typolink_tag,email[subst],url',
      ),
    ),
    'featured' => 
    array (
      'config' => 
      array (
        'type' => 'check',
        'renderType' => 'checkboxToggle',
      ),
      'label' => 'Hervorgehoben anzeigen',
      'description' => 'Hervorgehobene Gastgeber können in Teasern oder sortiert bevorzugt angezeigt werden.',
    ),
    'priority' => 
    array (
      'config' => 
      array (
        'type' => 'number',
        'size' => 8,
        'default' => 0,
      ),
      'label' => 'Sortierung / Priorität',
      'description' => 'Niedrige Werte werden in der Empfehlungssortierung bevorzugt angezeigt.',
    ),
    'external_id' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'max' => 120,
        'eval' => 'trim',
      ),
      'label' => 'Gastgebernummer / externe ID',
      'description' => 'Optionale interne Nummer oder ID aus einem externen System.',
    ),
    'media' => 
    array (
      'label' => 'Bildergalerie / Hero-Bild',
      'description' => 'Das erste Bild wird als Hero-Bild verwendet. Weitere Bilder erscheinen in Galerie und Modal.',
      'config' => 
      array (
        'type' => 'file',
        'allowed' => 'common-image-types',
        'maxitems' => 99,
      ),
    ),
    'logo' => 
    array (
      'config' => 
      array (
        'type' => 'file',
        'allowed' => 'common-image-types',
        'maxitems' => 1,
      ),
      'label' => 'Logo des Gastgebers',
    ),
    'documents' => 
    array (
      'config' => 
      array (
        'type' => 'file',
        'allowed' => 'pdf',
        'maxitems' => 10,
      ),
      'label' => 'Dokumente / Hausprospekt',
    ),
    'video_url' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'renderType' => 'inputLink',
        'eval' => 'trim',
      ),
      'label' => 'Video-URL',
    ),
    'street' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'max' => 255,
        'eval' => 'trim',
      ),
      'label' => 'Straße / Hausnummer',
    ),
    'address_addition' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'max' => 255,
        'eval' => 'trim',
      ),
      'label' => 'Adresszusatz',
    ),
    'zip' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'max' => 20,
        'eval' => 'trim',
        'default' => '21274',
      ),
      'label' => 'PLZ',
    ),
    'city' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'max' => 255,
        'eval' => 'trim',
        'default' => 'Undeloh',
      ),
      'label' => 'Ort',
    ),
    'country' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'max' => 120,
        'eval' => 'trim',
        'default' => 'Deutschland',
      ),
      'label' => 'Land',
    ),
    'show_on_map' => 
    array (
      'config' => 
      array (
        'type' => 'check',
        'renderType' => 'checkboxToggle',
        'default' => 1,
      ),
      'label' => 'Auf Karte anzeigen',
    ),
    'geocode_on_save' => 
    array (
      'label' => 'Koordinaten aus Anschrift ermitteln',
      'description' => 'Beim Speichern werden Breitengrad und Längengrad aus Straße, PLZ, Ort und Land ermittelt.',
      'config' => 
      array (
        'type' => 'check',
        'renderType' => 'checkboxToggle',
        'default' => 0,
      ),
    ),
    'latitude' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'size' => 20,
        'eval' => 'trim,double',
        'default' => '',
      ),
      'label' => 'Breitengrad',
    ),
    'longitude' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'size' => 20,
        'eval' => 'trim,double',
        'default' => '',
      ),
      'label' => 'Längengrad',
    ),
    'contact_name' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'max' => 255,
        'eval' => 'trim',
      ),
      'label' => 'Ansprechpartner',
    ),
    'phone' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'max' => 120,
        'eval' => 'trim',
      ),
      'label' => 'Telefon',
    ),
    'mobile' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'max' => 120,
        'eval' => 'trim',
      ),
      'label' => 'Mobiltelefon',
    ),
    'email' => 
    array (
      'config' => 
      array (
        'type' => 'email',
        'max' => 255,
      ),
      'label' => 'E-Mail',
    ),
    'website' => 
    array (
      'config' => 
      array (
        'type' => 'link',
        'allowedTypes' => 
        array (
          0 => 'url',
        ),
      ),
      'label' => 'Webseite',
    ),
    'booking_url' => 
    array (
      'config' => 
      array (
        'type' => 'link',
        'allowedTypes' => 
        array (
          0 => 'url',
          1 => 'page',
        ),
      ),
      'label' => 'Buchungslink',
    ),
    'booking_text' => 
    array (
      'label' => 'Text in der Box Anfragen / Buchen',
      'description' => 'Eigenständiger redaktioneller Text für die Kontakt-/Buchungsbox, unabhängig von Preise & Kapazität.',
      'config' => 
      array (
        'type' => 'text',
        'enableRichtext' => true,
        'richtextConfiguration' => 'default',
        'rows' => 5,
      ),
    ),
    'request_email' => 
    array (
      'config' => 
      array (
        'type' => 'email',
        'max' => 255,
      ),
      'label' => 'E-Mail für Anfragen',
    ),
    'price_from' => 
    array (
      'config' => 
      array (
        'type' => 'number',
        'format' => 'decimal',
        'default' => 0,
      ),
      'label' => 'Preis ab',
    ),
    'price_info' => 
    array (
      'config' => 
      array (
        'type' => 'text',
        'rows' => 4,
        'eval' => 'trim',
      ),
      'label' => 'Preisinformationen',
    ),
    'persons' => 
    array (
      'config' => 
      array (
        'type' => 'number',
        'default' => 0,
      ),
      'label' => 'Personen maximal',
    ),
    'bedrooms' => 
    array (
      'config' => 
      array (
        'type' => 'number',
        'default' => 0,
      ),
      'label' => 'Schlafzimmer',
    ),
    'beds' => 
    array (
      'config' => 
      array (
        'type' => 'number',
        'default' => 0,
      ),
      'label' => 'Betten',
    ),
    'bathrooms' => 
    array (
      'config' => 
      array (
        'type' => 'number',
        'default' => 0,
      ),
      'label' => 'Badezimmer',
    ),
    'size_sqm' => 
    array (
      'config' => 
      array (
        'type' => 'number',
        'default' => 0,
      ),
      'label' => 'Größe in m²',
    ),
    'units' => 
    array (
      'config' => 
      array (
        'type' => 'number',
        'default' => 0,
      ),
      'label' => 'Einheiten / Wohnungen / Zimmer',
    ),
    'season_info' => 
    array (
      'config' => 
      array (
        'type' => 'text',
        'rows' => 4,
        'eval' => 'trim',
      ),
      'label' => 'Saisonzeiten / Preiszeiten',
    ),
    'capacity_note' => 
    array (
      'config' => 
      array (
        'type' => 'text',
        'rows' => 4,
        'eval' => 'trim',
      ),
      'label' => 'Hinweis zu Kapazität',
    ),
    'features' => 
    array (
      'label' => 'Merkmale / Ausstattung',
      'description' => 'Strukturierte Merkmale auswählen. Links in der Auswahl steht ein Icon-Hinweis mit Merkmaltext und Gruppe. Im Frontend werden die ersten 6 Merkmale als 3er-Raster angezeigt, weitere Merkmale erscheinen im Modal.',
      'config' => 
      array (
        'type' => 'select',
        'renderType' => 'selectMultipleSideBySide',
        'foreign_table' => 'tx_gastgeber_domain_model_feature',
        'MM' => 'tx_gastgeber_host_feature_mm',
        'itemsProcFunc' => 'D3Werk\\Gastgeber\\FormEngine\\ItemsProcFunc\\FeatureItems->addIconLabels',
        'size' => 18,
        'autoSizeMax' => 30,
        'fieldControl' => 
        array (
          'editPopup' => 
          array (
            'disabled' => false,
          ),
          'addRecord' => 
          array (
            'disabled' => false,
          ),
          'listModule' => 
          array (
            'disabled' => false,
          ),
        ),
      ),
    ),
    'certificates' => 
    array (
      'label' => 'Klassifizierung / Sterne / Zertifikate',
      'description' => 'Optionale Zertifikate, Prüfsiegel oder bisherige Sterne-Datensätze. Für die normale Sterne-Ausgabe bitte bevorzugt das Feld „Sterne-Klassifizierung“ verwenden. Wenn dort nichts gewählt ist, wird die erste Stern-Klassifizierung aus dieser Auswahl als Fallback verwendet.',
      'config' => 
      array (
        'type' => 'select',
        'renderType' => 'selectMultipleSideBySide',
        'foreign_table' => 'tx_gastgeber_domain_model_certificate',
        'MM' => 'tx_gastgeber_host_certificate_mm',
        'itemsProcFunc' => 'D3Werk\\Gastgeber\\FormEngine\\ItemsProcFunc\\CertificateItems->addStarLabels',
        'size' => 8,
        'autoSizeMax' => 20,
        'fieldControl' => 
        array (
          'editPopup' => array ('disabled' => false),
          'addRecord' => array ('disabled' => false),
          'listModule' => array ('disabled' => false),
        ),
      ),
    ),
    'equipment_text' => 
    array (
      'config' => 
      array (
        'type' => 'text',
        'enableRichtext' => true,
        'richtextConfiguration' => 'default',
        'rows' => 8,
      ),
      'label' => 'Zusätzlicher Ausstattungstext',
    ),
    'opening_times' => 
    array (
      'config' => 
      array (
        'type' => 'text',
        'rows' => 4,
        'eval' => 'trim',
      ),
      'label' => 'Öffnungs- / Saisonzeiten',
    ),
    'accessibility_text' => 
    array (
      'config' => 
      array (
        'type' => 'text',
        'rows' => 4,
        'eval' => 'trim',
      ),
      'label' => 'Barrierefreiheit',
    ),
    'sustainability_text' => 
    array (
      'config' => 
      array (
        'type' => 'text',
        'rows' => 4,
        'eval' => 'trim',
      ),
      'label' => 'Nachhaltigkeit',
    ),
    'seo_title' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'max' => 255,
        'eval' => 'trim',
      ),
      'label' => 'SEO-Titel',
    ),
    'meta_description' => 
    array (
      'config' => 
      array (
        'type' => 'text',
        'rows' => 3,
        'eval' => 'trim',
      ),
      'label' => 'Meta-Beschreibung',
    ),
    'og_title' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'max' => 255,
        'eval' => 'trim',
      ),
      'label' => 'Open-Graph-Titel',
    ),
    'og_description' => 
    array (
      'config' => 
      array (
        'type' => 'text',
        'rows' => 3,
        'eval' => 'trim',
      ),
      'label' => 'Open-Graph-Beschreibung',
    ),
    'noindex' => 
    array (
      'config' => 
      array (
        'type' => 'check',
        'renderType' => 'checkboxToggle',
      ),
      'label' => 'Nicht indexieren',
    ),
  ),
);
