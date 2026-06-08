<?php

declare(strict_types=1);

/*
 * GASTGEBER_MAP_LOCAL_ASSET_FINAL_2026_06_08
 *
 * Leaflet itself is loaded locally from EXT:gastgeber. The only external
 * requests needed for the OpenStreetMap view are map tile images.
 */

use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Mutation;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationCollection;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationMode;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\UriValue;
use TYPO3\CMS\Core\Type\Map;

return Map::fromEntries(
    [
        Scope::frontend(),
        new MutationCollection(
            new Mutation(
                MutationMode::Extend,
                Directive::ImgSrc,
                new UriValue('https://tile.openstreetmap.org'),
                new UriValue('https://*.tile.openstreetmap.org'),
                new UriValue('https://*.openstreetmap.org'),
                new UriValue('data:')
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::ConnectSrc,
                new UriValue('https://tile.openstreetmap.org'),
                new UriValue('https://*.tile.openstreetmap.org'),
                new UriValue('https://*.openstreetmap.org')
            )
        ),
    ],
);
