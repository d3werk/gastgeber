<?php

declare(strict_types=1);

return array (
  'ctrl' => 
  array (
    'title' => 'Merkmal / Ausstattung',
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
    ),
    'searchFields' => 'title,slug,description',
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
      'showitem' => '--palette--;;visibility,title,slug,group,icon,icon_class,filterable,show_in_card,show_in_detail,top_feature,description,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,sys_language_uid,l10n_parent,l10n_diffsource',
    ),
  ),
  'palettes' => 
  array (
    'visibility' => 
    array (
      'label' => 'Sichtbarkeit',
      'showitem' => 'hidden',
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
        'foreign_table' => 'tx_gastgeber_domain_model_feature',
        'foreign_table_where' => 'AND {#tx_gastgeber_domain_model_feature}.{#pid}=###CURRENT_PID### AND {#tx_gastgeber_domain_model_feature}.{#sys_language_uid} IN (-1,0)',
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
    'title' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'required' => true,
        'max' => 255,
        'eval' => 'trim',
      ),
      'label' => 'Name des Merkmals',
      'description' => 'Bezeichnung des Merkmals, z. B. WLAN, Parkplatz, Haustiere erlaubt oder Terrasse.',
    ),
    'slug' => 
    array (
      'config' => 
      array (
        'type' => 'slug',
        'generatorOptions' => 
        array (
          'fields' => 
          array (
            0 => 'title',
          ),
          'fieldSeparator' => '-',
        ),
        'fallbackCharacter' => '-',
        'eval' => 'uniqueInPid',
        'default' => '',
      ),
      'label' => 'URL-Segment / Slug',
    ),
    'description' => 
    array (
      'config' => 
      array (
        'type' => 'text',
        'enableRichtext' => true,
        'richtextConfiguration' => 'default',
        'rows' => 5,
      ),
      'label' => 'Beschreibung',
    ),
    'group' => 
    array (
      'config' => 
      array (
        'type' => 'select',
        'renderType' => 'selectSingle',
        'foreign_table' => 'tx_gastgeber_domain_model_featuregroup',
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
      'label' => 'Merkmalsgruppe',
      'description' => 'Merkmalsgruppe für Filter, Detailansicht und Modal.',
    ),
    'icon' => 
    array (
      'label' => 'Merkmal-Icon',
      'description' => 'Optionales SVG/PNG-Icon für Filter, Liste und Detailansicht.',
      'config' => 
      array (
        'type' => 'file',
        'allowed' => 'common-image-types',
        'maxitems' => 1,
      ),
    ),
    'icon_class' => 
    array (
      'label' => 'Icon-CSS-Klasse',
      'description' => 'Alternative zum Upload, z. B. bi bi-wifi oder fa-solid fa-dog.',
      'config' => 
      array (
        'type' => 'input',
        'max' => 255,
        'eval' => 'trim',
        'placeholder' => 'bi bi-wifi, fa-solid fa-dog ...',
      ),
    ),
    'filterable' => 
    array (
      'label' => 'Im Filter anzeigen',
      'description' => 'Nur aktivierte Merkmale erscheinen in der linken Filterspalte.',
      'config' => 
      array (
        'type' => 'check',
        'renderType' => 'checkboxToggle',
        'default' => 1,
      ),
    ),
    'show_in_card' => 
    array (
      'label' => 'In Karten-/Listenansicht anzeigen',
      'description' => 'Wird als kompaktes Merkmal direkt im Gastgeber-Teaser ausgegeben.',
      'config' => 
      array (
        'type' => 'check',
        'renderType' => 'checkboxToggle',
        'default' => 1,
      ),
    ),
    'show_in_detail' => 
    array (
      'label' => 'In Detailansicht anzeigen',
      'description' => 'Wird in der Detailseite und ggf. im Ausstattungs-Modal angezeigt.',
      'config' => 
      array (
        'type' => 'check',
        'renderType' => 'checkboxToggle',
        'default' => 1,
      ),
    ),
    'top_feature' => 
    array (
      'label' => 'Als Top-Merkmal anzeigen',
      'description' => 'Besonders wichtige Merkmale werden prominent direkt unter der Bildergalerie angezeigt.',
      'config' => 
      array (
        'type' => 'check',
        'renderType' => 'checkboxToggle',
        'default' => 0,
      ),
    ),
  ),
);
