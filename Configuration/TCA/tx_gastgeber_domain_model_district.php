<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:tx_gastgeber_domain_model_district',
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
        'enablecolumns' => ['disabled' => 'hidden'],
        'searchFields' => 'title,slug,description',
        'iconfile' => 'EXT:gastgeber/Resources/Public/Icons/Extension.svg',
        'security' => ['ignorePageTypeRestriction' => true],
    ],
    'types' => ['1' => ['showitem' => '--palette--;;visibility,title,slug,latitude,longitude,description,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,sys_language_uid,l10n_parent,l10n_diffsource']],
    'palettes' => [
        'visibility' => ['showitem' => 'hidden'],
    ],
    'columns' => array (
  'sys_language_uid' => 
  array (
    'config' => 
    array (
      'type' => 'language',
    ),
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
  ),
  'l10n_diffsource' => 
  array (
    'config' => 
    array (
      'type' => 'passthrough',
    ),
  ),
  'hidden' => 
  array (
    'config' => 
    array (
      'type' => 'check',
      'renderType' => 'checkboxToggle',
    ),
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
  ),
  'latitude' => 
  array (
    'config' => 
    array (
      'type' => 'input',
      'eval' => 'trim,double',
      'size' => 20,
    ),
  ),
  'longitude' => 
  array (
    'config' => 
    array (
      'type' => 'input',
      'eval' => 'trim,double',
      'size' => 20,
    ),
  ),
),
];
