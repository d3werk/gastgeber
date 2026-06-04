<?php

declare(strict_types=1);

return array (
  'ctrl' => 
  array (
    'title' => 'Ortsteil',
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
      'showitem' => '--palette--;;visibility,title,slug,latitude,longitude,description,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,sys_language_uid,l10n_parent,l10n_diffsource',
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
        'foreign_table' => 'tx_gastgeber_domain_model_district',
        'foreign_table_where' => 'AND {#tx_gastgeber_domain_model_district}.{#pid}=###CURRENT_PID### AND {#tx_gastgeber_domain_model_district}.{#sys_language_uid} IN (-1,0)',
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
      'label' => 'Name des Ortsteils',
      'description' => 'Name des Ortsteils oder Lagebereichs.',
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
    'latitude' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'eval' => 'trim,double',
        'size' => 20,
      ),
      'label' => 'Breitengrad',
      'description' => 'Optionaler Kartenmittelpunkt für diesen Ortsteil.',
    ),
    'longitude' => 
    array (
      'config' => 
      array (
        'type' => 'input',
        'eval' => 'trim,double',
        'size' => 20,
      ),
      'label' => 'Längengrad',
      'description' => 'Optionaler Kartenmittelpunkt für diesen Ortsteil.',
    ),
  ),
);
