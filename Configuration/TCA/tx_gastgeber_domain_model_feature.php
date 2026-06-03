<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:gastgeber/Resources/Private/Language/locallang_db.xlf:tx_gastgeber_domain_model_feature',
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
    'types' => ['1' => ['showitem' => '--palette--;;visibility,title,slug,group,icon,icon_class,filterable,show_in_card,show_in_detail,top_feature,description,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,sys_language_uid,l10n_parent,l10n_diffsource']],
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
      'foreign_table' => 'tx_gastgeber_domain_model_feature',
      'foreign_table_where' => 'AND {#tx_gastgeber_domain_model_feature}.{#pid}=###CURRENT_PID### AND {#tx_gastgeber_domain_model_feature}.{#sys_language_uid} IN (-1,0)',
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
  'group' => 
  array (
    'config' => 
    array (
      'type' => 'select',
      'renderType' => 'selectSingle',
      'foreign_table' => 'tx_gastgeber_domain_model_featuregroup',
      'foreign_table_where' => 'AND {#tx_gastgeber_domain_model_featuregroup}.{#hidden}=0 ORDER BY {#tx_gastgeber_domain_model_featuregroup}.{#sorting},{#tx_gastgeber_domain_model_featuregroup}.{#title}',
      'items' => 
      array (
        0 => 
        array (
          'label' => '',
          'value' => 0,
        ),
      ),
      'default' => 0,
    ),
  ),
  'icon' => 
  array (
    'config' => 
    array (
      'type' => 'file',
      'allowed' => 'common-image-types',
      'maxitems' => 1,
    ),
  ),
  'icon_class' => 
  array (
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
    'config' => 
    array (
      'type' => 'check',
      'renderType' => 'checkboxToggle',
      'default' => 1,
    ),
  ),
  'show_in_card' => 
  array (
    'config' => 
    array (
      'type' => 'check',
      'renderType' => 'checkboxToggle',
      'default' => 1,
    ),
  ),
  'show_in_detail' => 
  array (
    'config' => 
    array (
      'type' => 'check',
      'renderType' => 'checkboxToggle',
      'default' => 1,
    ),
  ),
  'top_feature' => 
  array (
    'config' => 
    array (
      'type' => 'check',
      'renderType' => 'checkboxToggle',
      'default' => 0,
    ),
  ),
),
];
