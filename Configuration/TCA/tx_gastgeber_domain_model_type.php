<?php

declare(strict_types=1);

return array (
  'ctrl' => 
  array (
    'title' => 'Unterkunftsart',
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
      'showitem' => '--palette--;;visibility,title,slug,icon,icon_class,show_in_filter,description,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,sys_language_uid,l10n_parent,l10n_diffsource',
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
        'foreign_table' => 'tx_gastgeber_domain_model_type',
        'foreign_table_where' => 'AND {#tx_gastgeber_domain_model_type}.{#pid}=###CURRENT_PID### AND {#tx_gastgeber_domain_model_type}.{#sys_language_uid} IN (-1,0)',
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
      'label' => 'Name der Unterkunftsart',
      'description' => 'Bezeichnung der Unterkunftsart, z. B. Hotel, Ferienwohnung, Ferienhaus oder Pension.',
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
      'description' => 'URL- und Filtersegment für diese Unterkunftsart.',
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
    'icon' => 
    array (
      'config' => 
      array (
        'type' => 'file',
        'allowed' => 'common-image-types',
        'maxitems' => 1,
      ),
      'label' => 'Icon',
      'description' => 'Optionales Icon für Filter, Teaser und Detailausgabe.',
    ),
    'icon_class' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'max' => 255,
        'eval' => 'trim',
        'placeholder' => 'bi bi-house, fa-solid fa-hotel ...',
      ),
      'label' => 'Icon-CSS-Klasse',
      'description' => 'Alternative zum Upload, z. B. bi bi-house oder fa-solid fa-hotel.',
    ),
    'show_in_filter' => 
    array (
      'config' => 
      array (
        'type' => 'check',
        'renderType' => 'checkboxToggle',
        'default' => 1,
      ),
      'label' => 'Im Filter anzeigen',
      'description' => 'Aktivierte Unterkunftsarten erscheinen in der Filterspalte.',
    ),
  ),
);
