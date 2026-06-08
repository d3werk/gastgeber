<?php

declare(strict_types=1);

use D3Werk\Gastgeber\Controller\FilterController;
use D3Werk\Gastgeber\Controller\HostController;
use D3Werk\Gastgeber\Hooks\HostGeocodeDataHandlerHook;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::configurePlugin('Gastgeber', 'List', [HostController::class => 'list,detail'], [HostController::class => 'list']);
ExtensionUtility::configurePlugin('Gastgeber', 'Detail', [HostController::class => 'detail'], []);
ExtensionUtility::configurePlugin('Gastgeber', 'Map', [HostController::class => 'map,detail'], [HostController::class => 'map']);
ExtensionUtility::configurePlugin('Gastgeber', 'Teaser', [HostController::class => 'teaser,detail'], [HostController::class => 'teaser']);
ExtensionUtility::configurePlugin('Gastgeber', 'Categories', [HostController::class => 'categories'], [HostController::class => 'categories']);
ExtensionUtility::configurePlugin('Gastgeber', 'Filter', [FilterController::class => 'index'], [FilterController::class => 'index']);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = HostGeocodeDataHandlerHook::class;

$iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
$iconRegistry->registerIcon('ext-gastgeber', SvgIconProvider::class, ['source' => 'EXT:gastgeber/Resources/Public/Icons/Extension.svg']);
