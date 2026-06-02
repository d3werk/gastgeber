<?php

declare(strict_types=1);

use D3Werk\Gastgeber\Controller\FilterController;
use D3Werk\Gastgeber\Controller\HostController;
use D3Werk\Gastgeber\Hooks\HostGeocodeDataHandlerHook;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::configurePlugin(
    'Gastgeber',
    'List',
    [HostController::class => 'list'],
    [HostController::class => 'list']
);

ExtensionUtility::configurePlugin(
    'Gastgeber',
    'Detail',
    [HostController::class => 'detail'],
    []
);

ExtensionUtility::configurePlugin(
    'Gastgeber',
    'Filter',
    [FilterController::class => 'index'],
    [FilterController::class => 'index']
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = HostGeocodeDataHandlerHook::class;
