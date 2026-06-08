<?php

declare(strict_types=1);

/*
 * GASTGEBER_MAP_CSP_NOOP_FINAL_2026_06_08 / GASTGEBER_MAP_JS_FORCE_FINAL_2026_06_08
 *
 * Safe no-op CSP configuration.
 *
 * Reason:
 * A previous package added ContentSecurityPolicies.php with OpenStreetMap
 * mutations and caused a 503 in this TYPO3 installation. Uploading a ZIP to
 * GitHub does not delete files that are no longer present in the ZIP, so this
 * file intentionally overwrites the problematic file with an empty CSP map.
 * The map assets are loaded locally from EXT:gastgeber; no external Leaflet CDN
 * is required. OSM tile access can be configured later in the site-wide CSP if
 * CSP enforcement is active.
 */

use TYPO3\CMS\Core\Type\Map;

return Map::fromEntries();
