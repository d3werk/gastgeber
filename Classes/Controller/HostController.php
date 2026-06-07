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
        // Absicherung für bereinigte Detail-URLs wie /gastgeber/hotel-acht-linden.
        // In einigen Installationen wird die Route zwar auf die Listen-Seite aufgelöst,
        // Extbase erhält beim harten Browser-Reload aber keine tx_gastgeber_* Argumente.
        // Dann würde die normale Liste statt der Detailansicht gerendert oder der Inhalt
        // leer wirken. Wenn der letzte Pfadbestandteil eindeutig ein Gastgeber-Slug ist,
        // rendern wir deshalb direkt die Detailansicht.
        if (!$this->hasRequestArguments(['search', 'types', 'features', 'districts', 'sort', 'view', 'host', 'slug'])) {
            $hostFromPath = $this->resolveHostFromCurrentRequestPath();
            if ($hostFromPath instanceof Host) {
                return $this->renderDetailResponse($hostFromPath, true);
            }
        }

        $filters = $this->readFilters();
        $viewMode = $this->readViewMode((string)($this->settings['defaultView'] ?? 'cards'), (bool)($this->settings['showMap'] ?? true));
        $hosts = $this->hostRepository->findDemanded($this->settings, $filters);
        $this->view->assignMultiple($this->buildListAssignments($hosts, $filters, $viewMode));
        return $this->htmlResponse();
    }

    public function mapAction(): ResponseInterface
    {
        $filters = $this->readFilters();
        $hosts = $this->hostRepository->findDemanded($this->settings, $filters);
        $this->view->assignMultiple($this->buildListAssignments($hosts, $filters, 'map'));
        return $this->htmlResponse();
    }

    public function teaserAction(): ResponseInterface
    {
        $settings = $this->settings;
        $settings['featuredOnly'] = (bool)($settings['featuredOnly'] ?? true);
        $hosts = $this->hostRepository->findDemanded($settings, []);
        $this->view->assignMultiple(['hosts' => $hosts, 'settings' => $settings]);
        return $this->htmlResponse();
    }

    public function categoriesAction(): ResponseInterface
    {
        $this->view->assignMultiple([
            'types' => $this->hostTypeRepository->findAllIgnoringStorage(),
            'settings' => $this->settings,
        ]);
        return $this->htmlResponse();
    }

    public function detailAction(?Host $host = null): ResponseInterface
    {
        if (!$host instanceof Host && $this->request->hasArgument('host')) {
            $argument = $this->request->getArgument('host');
            if (is_numeric($argument)) {
                $host = $this->hostRepository->findByUid((int)$argument);
            } elseif (is_string($argument) && $argument !== '') {
                $host = $this->hostRepository->findOneBySlug($argument);
            }
        }
        if (!$host instanceof Host && $this->request->hasArgument('slug')) {
            $host = $this->hostRepository->findOneBySlug((string)$this->request->getArgument('slug'));
        }
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
            'settings' => $this->settings,
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
            if ($this->request->hasArgument($argumentName)) {
                return true;
            }
        }

        return false;
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
    private function buildListAssignments(iterable $hosts, array $filters, string $viewMode): array
    {
        return [
            'hosts' => $hosts,
            'types' => $this->hostTypeRepository->findAllIgnoringStorage(),
            'featureGroups' => $this->featureGroupRepository->findAllIgnoringStorage(),
            'features' => $this->featureRepository->findAllIgnoringStorage(),
            'districts' => $this->districtRepository->findAllIgnoringStorage(),
            'filters' => $filters,
            'viewMode' => $viewMode,
            'mapModalId' => 'gastgeber-map-modal-' . substr(md5((string)microtime(true) . spl_object_id($this)), 0, 10),
            'activeTypeMap' => array_fill_keys($filters['types'] ?? [], true),
            'activeFeatureMap' => array_fill_keys($filters['features'] ?? [], true),
            'activeDistrictMap' => array_fill_keys($filters['districts'] ?? [], true),
            'settings' => $this->settings,
            'introText' => $this->resolveIntroText(),
        ];
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

