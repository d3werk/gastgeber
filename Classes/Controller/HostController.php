<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Controller;

use D3Werk\Gastgeber\Domain\Model\Host;
use D3Werk\Gastgeber\Domain\Repository\DistrictRepository;
use D3Werk\Gastgeber\Domain\Repository\FeatureGroupRepository;
use D3Werk\Gastgeber\Domain\Repository\FeatureRepository;
use D3Werk\Gastgeber\Domain\Repository\HostRepository;
use D3Werk\Gastgeber\Domain\Repository\HostTypeRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class HostController extends ActionController
{
    public function __construct(
        private readonly HostRepository $hostRepository,
        private readonly HostTypeRepository $hostTypeRepository,
        private readonly FeatureRepository $featureRepository,
        private readonly FeatureGroupRepository $featureGroupRepository,
        private readonly DistrictRepository $districtRepository,
        private readonly PageRenderer $pageRenderer,
        private readonly MetaTagManagerRegistry $metaTagManagerRegistry,
    ) {}

    public function listAction(): ResponseInterface
    {
        // GASTGEBER_DETAIL_DISPATCH_SAFE_FINAL_2026_06_08:
        // Detail-URLs müssen auch dann stabil funktionieren, wenn TYPO3/Extbase nach
        // Route-Enhancer, Middleware, cHash-Prüfung oder Cache-Kontext trotzdem die
        // Listen-Action ausführt. Sobald ein echter Gastgeber über host/slug/action=detail
        // oder über den Pfad /gastgeber/{slug} erkennbar ist, wird direkt das
        // Detail-Template gerendert. Dadurch bleibt F5/Strg+F5 auf sauberen SEO-URLs stabil.
        $hostFromDetailRequest = $this->resolveHostFromDetailRequestContext();
        if ($hostFromDetailRequest instanceof Host) {
            return $this->renderDetailResponse($hostFromDetailRequest, true);
        }

        // Absicherung für bereinigte Detail-URLs wie /gastgeber/hotel-acht-linden.
        // Nur wenn keine normalen Listen-/Filterargumente vorhanden sind, wird der Pfad
        // als möglicher Gastgeber-Slug interpretiert.
        if (!$this->hasRequestArguments(['search', 'types', 'features', 'districts', 'sort', 'view'])) {
            $hostFromPath = $this->resolveHostFromCurrentRequestPath();
            if ($hostFromPath instanceof Host) {
                return $this->renderDetailResponse($hostFromPath, true);
            }
        }

        $filters = $this->readFilters();
        $settings = $this->getNormalizedSettings();
        $viewMode = $this->readViewMode((string)($settings['defaultView'] ?? 'cards'), $this->getBooleanSetting('showMap', true));
        $hosts = $this->hostRepository->findDemanded($settings, $filters);
        $this->view->assignMultiple($this->buildListAssignments($hosts, $filters, $viewMode));
        return $this->htmlResponse();
    }

    public function mapAction(): ResponseInterface
    {
        $filters = $this->readFilters();
        $settings = $this->getNormalizedSettings();
        $hosts = $this->hostRepository->findDemanded($settings, $filters);
        $this->view->assignMultiple($this->buildListAssignments($hosts, $filters, 'map'));
        return $this->htmlResponse();
    }

    public function teaserAction(): ResponseInterface
    {
        $settings = $this->getNormalizedSettings();
        $settings['featuredOnly'] = (bool)($settings['featuredOnly'] ?? true);
        $hosts = $this->hostRepository->findDemanded($settings, []);
        $this->view->assignMultiple(['hosts' => $hosts, 'settings' => $settings]);
        return $this->htmlResponse();
    }

    public function categoriesAction(): ResponseInterface
    {
        $this->view->assignMultiple([
            'types' => $this->hostTypeRepository->findAllIgnoringStorage(),
            'settings' => $this->getNormalizedSettings(),
        ]);
        return $this->htmlResponse();
    }

    public function detailAction(): ResponseInterface
    {
        // GASTGEBER_DETAIL_DISPATCH_SAFE_FINAL_2026_06_08:
        // Kein typisiertes Host-Argument in der Action-Signatur verwenden.
        // Hintergrund: Je nach Route-Enhancer-/Middleware-Zustand kann Extbase den
        // Parameter host als UID, Slug oder Alias erhalten. Eine automatische
        // Property-Mapping-Exception würde die Action stoppen, bevor der Fallback
        // greifen kann. Deshalb lösen wir den Gastgeber bewusst selbst auf.
        $host = $this->resolveHostFromDetailRequestContext();
        if (!$host instanceof Host) {
            $host = $this->resolveHostFromCurrentRequestPath();
        }
        if (!$host instanceof Host) {
            $this->view->assign('host', null);
            return $this->htmlResponse();
        }

        return $this->renderDetailResponse($host);
    }

    private function renderDetailResponse(Host $host, bool $forceDetailTemplate = false): ResponseInterface
    {
        $this->applySeo($host);
        $this->view->assignMultiple([
            'host' => $host,
            'settings' => $this->getNormalizedSettings(),
        ]);

        // Wichtig für F5/Strg+F5 auf bereinigten URLs wie /gastgeber/hotel-acht-linden:
        // Wenn TYPO3 die Seite als Listen-Plugin ausführt und die Extension den Slug
        // erst in listAction() aus dem Pfad erkennt, würde htmlResponse() sonst das
        // Template Host/List.html rendern. Dieses Template erwartet eine Gastgeberliste
        // und wirkt deshalb leer. In diesem Fall wird explizit Host/Detail.html gerendert.
        if ($forceDetailTemplate) {
            return $this->htmlResponse($this->view->render('Detail'));
        }

        return $this->htmlResponse();
    }

    /** @param array<int,string> $argumentNames */
    private function hasRequestArguments(array $argumentNames): bool
    {
        foreach ($argumentNames as $argumentName) {
            if ($this->readRequestArgumentAsString($argumentName) !== '') {
                return true;
            }
        }

        return false;
    }

    private function resolveHostFromDetailRequestContext(): ?Host
    {
        // GASTGEBER_DETAIL_ROUTE_ARGUMENT_FINAL_2026_06_08:
        // TYPO3 kann den Route-Enhancer-Parameter host je nach Routing-Zustand als
        // Domain-Objekt, UID oder Slug in die Extbase-Request-Argumente legen.
        // Zusätzlich kann die SlugRoutingMiddleware den sauberen Pfad vor dem
        // PageResolver auf die echte Seite zurückschreiben. Dann wäre der Slug im
        // Controller nicht mehr zuverlässig aus getUri()->getPath() lesbar. Deshalb
        // werden hier zuerst echte Request-Argumente und Middleware-Attribute gelesen.
        foreach (['host', 'slug'] as $argumentName) {
            $host = $this->resolveHostFromRawRequestArgument($argumentName);
            if ($host instanceof Host) {
                return $host;
            }
        }

        foreach (['gastgeberResolvedHostUid', 'gastgeberResolvedSlug'] as $attributeName) {
            $host = $this->resolveHostByIdentifier($this->readRequestAttributeAsString($attributeName));
            if ($host instanceof Host) {
                return $host;
            }
        }

        $action = strtolower($this->readRequestArgumentAsString('action'));
        $hostIdentifier = $this->readRequestArgumentAsString('host');
        $slugIdentifier = $this->readRequestArgumentAsString('slug');

        // Ohne Detail-Absicht keine Listenansicht versehentlich in eine Detailansicht wandeln.
        if ($action !== 'detail' && $hostIdentifier === '' && $slugIdentifier === '') {
            return null;
        }

        foreach ([$hostIdentifier, $slugIdentifier] as $identifier) {
            $host = $this->resolveHostByIdentifier($identifier);
            if ($host instanceof Host) {
                return $host;
            }
        }

        return null;
    }

    private function resolveHostFromRawRequestArgument(string $argumentName): ?Host
    {
        try {
            if ($this->request->hasArgument($argumentName)) {
                $value = $this->request->getArgument($argumentName);
                if ($value instanceof Host) {
                    return $value;
                }
                if (is_array($value)) {
                    foreach (['uid', 'slug', '__identity'] as $key) {
                        if (isset($value[$key])) {
                            $host = $this->resolveHostByIdentifier($this->scalarToString($value[$key]));
                            if ($host instanceof Host) {
                                return $host;
                            }
                        }
                    }
                }

                $host = $this->resolveHostByIdentifier($this->scalarToString($value));
                if ($host instanceof Host) {
                    return $host;
                }
            }
        } catch (\Throwable) {
            // Fallback auf Query-Parameter und Middleware-Attribute.
        }

        return null;
    }

    private function resolveHostByIdentifier(string $identifier): ?Host
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        try {
            if (ctype_digit($identifier)) {
                $host = $this->hostRepository->findByUid((int)$identifier);
            } else {
                $host = $this->hostRepository->findOneBySlug($identifier);
            }
        } catch (\Throwable) {
            return null;
        }

        return $host instanceof Host ? $host : null;
    }

    private function readRequestArgumentAsString(string $argumentName): string
    {
        try {
            if ($this->request->hasArgument($argumentName)) {
                return $this->scalarToString($this->request->getArgument($argumentName));
            }
        } catch (\Throwable) {
            // Fallback auf PSR-7 Query-Parameter.
        }

        foreach ($this->collectQueryParameterSets() as $queryParams) {
            $value = $this->readArgumentFromQueryParams($queryParams, $argumentName);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function readRequestAttributeAsString(string $attributeName): string
    {
        foreach ([$this->request, $GLOBALS['TYPO3_REQUEST'] ?? null] as $request) {
            if (!is_object($request) || !method_exists($request, 'getAttribute')) {
                continue;
            }
            try {
                $value = $request->getAttribute($attributeName);
                $stringValue = $this->scalarToString($value);
                if ($stringValue !== '') {
                    return $stringValue;
                }
            } catch (\Throwable) {
                // Ignore request variants without this attribute.
            }
        }

        return '';
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function collectQueryParameterSets(): array
    {
        $sets = [];
        $requests = [$this->request, $GLOBALS['TYPO3_REQUEST'] ?? null];

        foreach ($requests as $request) {
            if (!is_object($request) || !method_exists($request, 'getQueryParams')) {
                continue;
            }
            try {
                $queryParams = $request->getQueryParams();
                if (is_array($queryParams)) {
                    $sets[] = $queryParams;
                }
            } catch (\Throwable) {
                // Ignore request variants without query params.
            }
        }

        if (is_array($_GET ?? null)) {
            $sets[] = $_GET;
        }

        return $sets;
    }

    /** @param array<string,mixed> $queryParams */
    private function readArgumentFromQueryParams(array $queryParams, string $argumentName): string
    {
        if (array_key_exists($argumentName, $queryParams)) {
            return $this->scalarToString($queryParams[$argumentName]);
        }

        foreach (['tx_gastgeber_list', 'tx_gastgeber_detail', 'tx_gastgeber_map', 'tx_gastgeber_teaser'] as $namespace) {
            if (!isset($queryParams[$namespace]) || !is_array($queryParams[$namespace])) {
                continue;
            }
            if (array_key_exists($argumentName, $queryParams[$namespace])) {
                return $this->scalarToString($queryParams[$namespace][$argumentName]);
            }
        }

        return '';
    }

    private function scalarToString(mixed $value): string
    {
        if ($value instanceof Host) {
            $slug = trim($value->getSlug());
            return $slug !== '' ? $slug : (string)$value->getUid();
        }
        if (is_scalar($value)) {
            return trim((string)$value);
        }
        if ($value instanceof \Stringable) {
            return trim((string)$value);
        }

        return '';
    }

    private function resolveHostFromCurrentRequestPath(): ?Host
    {
        $slug = $this->extractSlugFromCurrentRequestPath();
        if ($slug === '') {
            return null;
        }

        try {
            $host = $this->hostRepository->findOneBySlug($slug);
        } catch (\Throwable) {
            return null;
        }

        return $host instanceof Host ? $host : null;
    }

    private function extractSlugFromCurrentRequestPath(): string
    {
        $paths = [];
        $requests = [$this->request, $GLOBALS['TYPO3_REQUEST'] ?? null];

        foreach ($requests as $request) {
            if (!is_object($request)) {
                continue;
            }

            try {
                if (method_exists($request, 'getUri')) {
                    $uri = $request->getUri();
                    if (is_object($uri) && method_exists($uri, 'getPath')) {
                        $paths[] = (string)$uri->getPath();
                    }
                }
            } catch (\Throwable) {
                // Ignore non-PSR request variants.
            }

            try {
                if (method_exists($request, 'getAttribute')) {
                    $normalizedParams = $request->getAttribute('normalizedParams');
                    if (is_object($normalizedParams) && method_exists($normalizedParams, 'getRequestUri')) {
                        $requestUri = (string)$normalizedParams->getRequestUri();
                        $paths[] = (string)(parse_url($requestUri, PHP_URL_PATH) ?: '');
                    }
                }
            } catch (\Throwable) {
                // Ignore unavailable normalized params.
            }
        }

        foreach ($paths as $path) {
            $slug = $this->extractSlugFromPath($path);
            if ($slug !== '') {
                return $slug;
            }
        }

        return '';
    }

    private function extractSlugFromPath(string $path): string
    {
        $path = rawurldecode((string)(parse_url($path, PHP_URL_PATH) ?: $path));
        $path = trim($path, '/');
        if ($path === '') {
            return '';
        }

        $segments = array_values(array_filter(explode('/', $path), static fn (string $segment): bool => $segment !== ''));
        if ($segments === [] || count($segments) < 2) {
            return '';
        }

        // GASTGEBER_SEO_ROUTE_FINAL_2026_06_07:
        // Saubere Detail-URLs werden bewusst auf /gastgeber/{slug} begrenzt.
        // Dadurch wird nicht jeder beliebige letzte Pfadbestandteil als Gastgeber
        // interpretiert, sondern nur ein Slug direkt unter der Gastgeberseite.
        $parentSegment = strtolower((string)($segments[count($segments) - 2] ?? ''));
        if (!in_array($parentSegment, $this->getAllowedDetailBaseSegments(), true)) {
            return '';
        }

        $slug = (string)end($segments);
        if ($slug === '' || strlen($slug) > 180) {
            return '';
        }
        if (str_contains($slug, '.') || str_contains($slug, '?') || str_contains($slug, '&')) {
            return '';
        }
        if (preg_match('/^[a-z0-9][a-z0-9\-_]*$/i', $slug) !== 1) {
            return '';
        }

        return $slug;
    }

    /** @return array<int,string> */
    private function getAllowedDetailBaseSegments(): array
    {
        $configured = (string)($this->settings['detailBaseSegments'] ?? 'gastgeber');
        $segments = array_values(array_filter(array_map(
            static fn (string $segment): string => trim(strtolower($segment)),
            explode(',', $configured)
        ), static fn (string $segment): bool => $segment !== ''));

        return $segments !== [] ? $segments : ['gastgeber'];
    }

    /** @return array<string,mixed> */
    private function getNormalizedSettings(): array
    {
        $settings = $this->settings;

        $defaults = [
            'defaultView' => 'cards',
            'showFilter' => '1',
            'showViewSwitch' => '1',
            'showMap' => '1',
            'showAllFeaturesInFilter' => '1',
            'mapCenterLat' => '53.1966000',
            'mapCenterLng' => '9.9762000',
            'mapZoom' => '13',
            'mapMarkerIconUrl' => '',
            'mapMarkerIconRetinaUrl' => '',
            'mapMarkerShadowUrl' => '',
            'mapMarkerIconWidth' => '38',
            'mapMarkerIconHeight' => '46',
            'mapMarkerIconAnchorX' => '19',
            'mapMarkerIconAnchorY' => '46',
            'mapMarkerPopupAnchorX' => '0',
            'mapMarkerPopupAnchorY' => '-42',
            'mapMarkerShadowWidth' => '41',
            'mapMarkerShadowHeight' => '41',
            'mapMarkerShadowAnchorX' => '12',
            'mapMarkerShadowAnchorY' => '41',
        ];

        foreach ($defaults as $key => $defaultValue) {
            if (!array_key_exists($key, $settings) || $settings[$key] === null || $settings[$key] === '') {
                $settings[$key] = $defaultValue;
            }
        }

        // GASTGEBER_MAP_RESTORE_FINAL_2026_06_07:
        // Leere FlexForm-Werte dürfen die Kartenansicht nicht versehentlich deaktivieren.
        // Nur ein echter Wert 0/false/no/off deaktiviert die Kartenfunktion.
        $settings['showMap'] = $this->getBooleanSetting('showMap', true) ? '1' : '0';
        $settings['showFilter'] = $this->getBooleanSetting('showFilter', true) ? '1' : '0';
        $settings['showViewSwitch'] = $this->getBooleanSetting('showViewSwitch', true) ? '1' : '0';
        $settings['showAllFeaturesInFilter'] = $this->getBooleanSetting('showAllFeaturesInFilter', true) ? '1' : '0';

        return $settings;
    }

    private function getBooleanSetting(string $key, bool $default): bool
    {
        $value = $this->settings[$key] ?? null;
        if ($value === null || $value === '') {
            return $default;
        }
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value)) {
            return $value !== 0;
        }

        $normalized = strtolower(trim((string)$value));
        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }
        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return $default;
    }

    /** @return array<string,mixed> */
    private function buildListAssignments(iterable $hosts, array $filters, string $viewMode): array
    {
        $types = $this->hostTypeRepository->findAllIgnoringStorage();
        $featureGroups = $this->featureGroupRepository->findAllIgnoringStorage();
        $features = $this->featureRepository->findAllIgnoringStorage();
        $districts = $this->districtRepository->findAllIgnoringStorage();
        $settings = $this->getNormalizedSettings();

        return [
            'hosts' => $hosts,
            'types' => $types,
            'featureGroups' => $featureGroups,
            'features' => $features,
            // GASTGEBER_LIST_FILTER_FEATURES_FINAL_2026_06_09:
            // Der Filter rendert die Merkmale gruppiert serverseitig. Dadurch hängt die
            // Ausgabe nicht mehr von Fluid-Vergleichen wie {feature.group.uid} == {group.uid}
            // ab und es werden alle Merkmale einer Gruppe zuverlässig ausgegeben.
            'filterFeatureGroups' => $this->buildFilterFeatureGroups($featureGroups, $features, $settings),
            'districts' => $districts,
            'filters' => $filters,
            'viewMode' => $viewMode,
            'mapModalId' => 'gastgeber-map-modal-' . substr(md5((string)microtime(true) . spl_object_id($this)), 0, 10),
            'activeTypeMap' => array_fill_keys($filters['types'] ?? [], true),
            'activeFeatureMap' => array_fill_keys($filters['features'] ?? [], true),
            'activeDistrictMap' => array_fill_keys($filters['districts'] ?? [], true),
            'settings' => $settings,
            'introText' => $this->resolveIntroText(),
        ];
    }


    /**
     * @param iterable<object> $featureGroups
     * @param iterable<object> $features
     * @param array<string,mixed> $settings
     * @return array<int,array{uid:int,tableName:string,title:string,icon:mixed,iconClass:string,features:array<int,object>}>
     */
    private function buildFilterFeatureGroups(iterable $featureGroups, iterable $features, array $settings): array
    {
        $groups = [];

        foreach ($featureGroups as $group) {
            if (!is_object($group) || !method_exists($group, 'getUid')) {
                continue;
            }

            $uid = (int)$group->getUid();
            if ($uid <= 0) {
                continue;
            }

            $groups[$uid] = [
                'uid' => $uid,
                'tableName' => 'tx_gastgeber_domain_model_featuregroup',
                'title' => method_exists($group, 'getTitle') ? (string)$group->getTitle() : '',
                'icon' => method_exists($group, 'getIcon') ? $group->getIcon() : null,
                'iconClass' => method_exists($group, 'getIconClass') ? (string)$group->getIconClass() : '',
                'features' => [],
            ];
        }

        $showAllFeaturesInFilter = ((string)($settings['showAllFeaturesInFilter'] ?? '1')) !== '0';

        foreach ($features as $feature) {
            if (!is_object($feature) || !method_exists($feature, 'getUid')) {
                continue;
            }

            // Standard für Undeloh: Alle gepflegten Merkmale sollen im Filter sichtbar sein.
            // Optional kann über settings.showAllFeaturesInFilter = 0 wieder strikt das Feld
            // „Im Filter anzeigen“ verwendet werden.
            if (!$showAllFeaturesInFilter && method_exists($feature, 'isFilterable') && !$feature->isFilterable()) {
                continue;
            }

            $group = method_exists($feature, 'getGroup') ? $feature->getGroup() : null;
            $groupUid = is_object($group) && method_exists($group, 'getUid') ? (int)$group->getUid() : 0;

            if ($groupUid > 0 && !isset($groups[$groupUid])) {
                $groups[$groupUid] = [
                    'uid' => $groupUid,
                    'tableName' => 'tx_gastgeber_domain_model_featuregroup',
                    'title' => method_exists($group, 'getTitle') ? (string)$group->getTitle() : '',
                    'icon' => method_exists($group, 'getIcon') ? $group->getIcon() : null,
                    'iconClass' => method_exists($group, 'getIconClass') ? (string)$group->getIconClass() : '',
                    'features' => [],
                ];
            }

            if ($groupUid <= 0) {
                $groupUid = 0;
                if (!isset($groups[$groupUid])) {
                    $groups[$groupUid] = [
                        'uid' => 0,
                        'tableName' => '',
                        'title' => 'Weitere Merkmale',
                        'icon' => null,
                        'iconClass' => '',
                        'features' => [],
                    ];
                }
            }

            $groups[$groupUid]['features'][] = $feature;
        }

        return array_values(array_filter(
            $groups,
            static fn (array $group): bool => !empty($group['features'])
        ));
    }

    private function resolveIntroText(): string
    {
        $contentData = $this->resolveCurrentContentData();

        // Das RTE-Feld im Reiter "Allgemein" ist das TYPO3-Standardfeld bodytext.
        // Es hat Vorrang vor einem eventuell noch gespeicherten Altwert aus der FlexForm.
        $bodytext = trim((string)($contentData['bodytext'] ?? ''));
        if ($bodytext !== '') {
            return $bodytext;
        }

        // Fallback: Bei einzelnen Installationen enthält der aktuelle ContentObjectRenderer
        // nicht alle Spalten. Wenn die UID bekannt ist, lesen wir bodytext deshalb nochmals
        // direkt aus tt_content. So funktioniert das Feld zuverlässig auch beim klassischen
        // "Allgemeines Plugin [list]" mit list_type=gastgeber_list.
        $contentUid = (int)($contentData['uid'] ?? 0);
        if ($contentUid > 0) {
            $bodytext = $this->fetchBodytextFromContentElement($contentUid);
            if ($bodytext !== '') {
                return $bodytext;
            }
        }

        // Kompatibilität: Ältere Datensätze können noch settings.introText gespeichert haben.
        // Das Feld wird nicht mehr im Plugin-Reiter angezeigt, vorhandene Inhalte gehen aber
        // nicht verloren.
        return trim((string)($this->settings['introText'] ?? ''));
    }

    /** @return array<string,mixed> */
    private function resolveCurrentContentData(): array
    {
        $contentObjects = [];

        if (method_exists($this->request, 'getAttribute')) {
            $contentObjects[] = $this->request->getAttribute('currentContentObject');
        }

        $globalRequest = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (is_object($globalRequest) && method_exists($globalRequest, 'getAttribute')) {
            $contentObjects[] = $globalRequest->getAttribute('currentContentObject');
        }

        $frontendController = $GLOBALS['TSFE'] ?? null;
        if (is_object($frontendController) && isset($frontendController->cObj)) {
            $contentObjects[] = $frontendController->cObj;
        }

        foreach ($contentObjects as $contentObject) {
            if ($contentObject instanceof ContentObjectRenderer && is_array($contentObject->data)) {
                return $contentObject->data;
            }
            if (is_object($contentObject) && isset($contentObject->data) && is_array($contentObject->data)) {
                return $contentObject->data;
            }
        }

        return [];
    }

    private function fetchBodytextFromContentElement(int $contentUid): string
    {
        try {
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');
            $row = $connection->select(
                ['bodytext'],
                'tt_content',
                ['uid' => $contentUid]
            )->fetchAssociative();
        } catch (\Throwable) {
            return '';
        }

        return trim((string)($row['bodytext'] ?? ''));
    }

    /** @return array<string,mixed> */
    private function readFilters(): array
    {
        return [
            'search' => trim((string)($this->request->hasArgument('search') ? $this->request->getArgument('search') : '')),
            'types' => $this->readUidArray('types'),
            'features' => $this->readUidArray('features'),
            'districts' => $this->readUidArray('districts'),
            'sort' => (string)($this->request->hasArgument('sort') ? $this->request->getArgument('sort') : ''),
        ];
    }

    /** @return array<int,int> */
    private function readUidArray(string $argumentName): array
    {
        if (!$this->request->hasArgument($argumentName)) {
            return [];
        }
        $value = $this->request->getArgument($argumentName);
        if (is_array($value)) {
            return array_values(array_unique(array_filter(array_map('intval', $value))));
        }
        return array_values(array_unique(array_filter(array_map('intval', explode(',', (string)$value)))));
    }

    private function readViewMode(string $defaultView, bool $mapEnabled = true): string
    {
        $allowedViews = $mapEnabled ? ['cards', 'list', 'map'] : ['cards', 'list'];
        $fallbackView = in_array($defaultView, $allowedViews, true) ? $defaultView : 'cards';
        $view = $this->request->hasArgument('view') ? (string)$this->request->getArgument('view') : $fallbackView;
        return in_array($view, $allowedViews, true) ? $view : $fallbackView;
    }

    private function applySeo(Host $host): void
    {
        $title = $host->getSeoTitle() !== '' ? $host->getSeoTitle() : $host->getTitle();
        $description = $this->plainText($host->getMetaDescription());
        if ($description === '') {
            $description = $this->plainText($host->getTeaser());
        }
        if ($description === '') {
            $description = $this->plainText($host->getDescription());
        }
        if ($title !== '') {
            $this->pageRenderer->setTitle($title);
            $this->metaTagManagerRegistry->getManagerForProperty('og:title')->addProperty('og:title', $host->getOgTitle() !== '' ? $host->getOgTitle() : $title);
        }
        if ($description !== '') {
            $this->metaTagManagerRegistry->getManagerForProperty('description')->addProperty('description', $description);
            $ogDescription = $this->plainText($host->getOgDescription());
            $this->metaTagManagerRegistry->getManagerForProperty('og:description')->addProperty('og:description', $ogDescription !== '' ? $ogDescription : $description);
        }
        if ($host->isNoindex()) {
            $this->metaTagManagerRegistry->getManagerForProperty('robots')->addProperty('robots', 'noindex,follow');
        }
        $this->addStructuredData($host, $title, $description);
    }

    private function plainText(string $value): string
    {
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = (string)preg_replace('/\s+/', ' ', $value);
        return trim($value);
    }

    private function addStructuredData(Host $host, string $title, string $description): void
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'LodgingBusiness',
            'name' => $host->getTitle(),
            'description' => $description,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $host->getStreet(),
                'postalCode' => $host->getZip(),
                'addressLocality' => $host->getCity(),
                'addressCountry' => $host->getCountry(),
            ],
        ];
        if ($host->hasCoordinates()) {
            $data['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $host->getLatitude(),
                'longitude' => $host->getLongitude(),
            ];
        }
        if ($host->getPhone() !== '') {
            $data['telephone'] = $host->getPhone();
        }
        if ($host->getWebsite() !== '') {
            $data['url'] = $host->getWebsite();
        }
        if ($host->getPriceFrom() > 0) {
            $data['priceRange'] = 'ab ' . number_format($host->getPriceFrom(), 2, ',', '.') . ' EUR';
        }
        if ($host->getStarCount() > 0) {
            $data['starRating'] = [
                '@type' => 'Rating',
                'ratingValue' => $host->getStarCount(),
                'bestRating' => 5,
            ];
        }
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $this->pageRenderer->addHeaderData('<script type="application/ld+json">' . $json . '</script>');
    }
}

