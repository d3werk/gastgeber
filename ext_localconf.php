<?php

declare(strict_types=1);

use D3Werk\Gastgeber\Controller\FilterController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

// EXT:news ProxyClassGenerator: erweitert das News-Domain-Model um Gastgeber-Felder.
$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Model/News']['gastgeber'] = 'gastgeber';

ExtensionUtility::configurePlugin(
    'Gastgeber',
    'Filter',
    [
        FilterController::class => 'index',
    ],
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);
