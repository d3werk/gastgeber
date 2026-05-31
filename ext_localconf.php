<?php

declare(strict_types=1);

use D3Werk\Gastgeber\Controller\FilterController;
use GeorgRinger\News\Controller\NewsController;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

// EXT:news ProxyClassGenerator: erweitert das News-Domain-Model um Gastgeber-Felder.
$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Model/News']['gastgeber'] = 'gastgeber';

// Eigene Gastgeber-Content-Elemente für Redakteure.
// Die Liste/Detail-Logik bleibt bewusst bei EXT:news, damit Routing, Demand,
// Pagination und Detail-Verlinkung weiterhin kompatibel zu tx_news_pi1 bleiben.
ExtensionUtility::configurePlugin(
    'Gastgeber',
    'List',
    [
        NewsController::class => 'list',
    ],
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'Gastgeber',
    'Detail',
    [
        NewsController::class => 'detail',
    ],
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'Gastgeber',
    'Filter',
    [
        FilterController::class => 'index',
    ],
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

// Wichtig: EXT:news-Links und Filter verwenden tx_news_pi1[...] als Parameter.
// Die eigenen Gastgeber-Plugins lesen damit dieselben URL-Parameter wie die News-Plugins.
ExtensionManagementUtility::addTypoScriptSetup(trim('
plugin.tx_news {
    view {
        templateRootPaths.160 = EXT:gastgeber/Resources/Private/Templates/
        partialRootPaths.160 = EXT:gastgeber/Resources/Private/Partials/
        layoutRootPaths.160 = EXT:gastgeber/Resources/Private/Layouts/
    }
    settings {
        cropMaxCharacters = 220
        displayDummyIfNoMedia = 0
        disableOverrideDemand = 0
        gastgeber {
            defaultView = cards
            viewParameter = tx_gastgeber_view
            showViewSwitch = 1
            map {
                defaultLatitude = 53.1966
                defaultLongitude = 9.9762
                defaultZoom = 13
                detailZoom = 16
                tileUrl = https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png
                attribution = &copy; OpenStreetMap contributors
            }
        }
        list {
            paginate {
                itemsPerPage = 12
                insertAbove = 0
                insertBelow = 1
            }
            media {
                image {
                    maxWidth = 900
                    maxHeight = 700
                    lazyLoading = lazy
                }
            }
        }
        detail {
            showSocialShareButtons = 0
            showPrevNext = 0
        }
    }
}
plugin.tx_gastgeber_list {
    view {
        templateRootPaths.160 = EXT:gastgeber/Resources/Private/Templates/
        partialRootPaths.160 = EXT:gastgeber/Resources/Private/Partials/
        layoutRootPaths.160 = EXT:gastgeber/Resources/Private/Layouts/
        pluginNamespace = tx_news_pi1
    }
    settings < plugin.tx_news.settings
}
plugin.tx_gastgeber_detail {
    view {
        templateRootPaths.160 = EXT:gastgeber/Resources/Private/Templates/
        partialRootPaths.160 = EXT:gastgeber/Resources/Private/Partials/
        layoutRootPaths.160 = EXT:gastgeber/Resources/Private/Layouts/
        pluginNamespace = tx_news_pi1
    }
    settings < plugin.tx_news.settings
}
page.includeCSS.gastgeber = EXT:gastgeber/Resources/Public/Css/gastgeber.css
page.includeCSSLibs.gastgeberLeaflet = https://unpkg.com/leaflet@1.9.4/dist/leaflet.css
page.includeJSLibs.gastgeberLeaflet = https://unpkg.com/leaflet@1.9.4/dist/leaflet.js
page.includeJSFooter.gastgeberMap = EXT:gastgeber/Resources/Public/JavaScript/gastgeber-map.js
'));
