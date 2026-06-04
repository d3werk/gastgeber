<?php

declare(strict_types=1);

return array (
  'ctrl' => 
  array (
    'title' => 'Zertifikat / Klassifizierung',
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
      'showitem' => '--palette--;;visibility,title,slug,issuer,rating_value,icon,icon_class,url,description,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,sys_language_uid,l10n_parent,l10n_diffsource',
    ),
  ),
  'palettes' => 
  array (
    'visibility' => 
    array (
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
        'foreign_table' => 'tx_gastgeber_domain_model_certificate',
        'foreign_table_where' => 'AND {#tx_gastgeber_domain_model_certificate}.{#pid}=###CURRENT_PID### AND {#tx_gastgeber_domain_model_certificate}.{#sys_language_uid} IN (-1,0)',
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
      'label' => 'Name des Zertifikats',
      'description' => 'Bezeichnung der Klassifizierung oder Auszeichnung, z. B. 3 Sterne, DTV oder Bett+Bike.',
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
    'issuer' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'max' => 255,
        'eval' => 'trim',
      ),
      'label' => 'Herausgeber',
    ),
    'icon' => 
    array (
      'config' => 
      array (
        'type' => 'file',
        'allowed' => 'common-image-types',
        'maxitems' => 1,
      ),
      'label' => 'Zertifikat-Icon / Logo',
      'description' => 'Optionales Logo/Icon des Zertifikats.',
    ),
    'icon_class' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'max' => 255,
        'eval' => 'trim',
      ),
      'label' => 'Icon-CSS-Klasse',
      'description' => 'Alternative zum Upload, z. B. bi bi-star-fill.',
    ),
    'url' => 
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
      'label' => 'Webseite / Prüfsiegel-Link',
    ),
    'rating_value' => 
    array (
      'config' => 
      array (
        'type' => 'number',
        'format' => 'decimal',
        'default' => 0,
      ),
      'label' => 'Sterne / Bewertungswert',
      'description' => 'Numerischer Wert für Sterne-Badges, z. B. 3.0 oder 4.5.',
    ),
  ),
);
