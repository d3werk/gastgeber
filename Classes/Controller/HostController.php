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
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

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
        private readonly ConfigurationManagerInterface $configurationManager,
    ) {}

    public function listAction(): ResponseInterface
    {
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
            $this->view->assign('host', null);
            return $this->htmlResponse();
        }

        $this->applySeo($host);
        $this->view->assignMultiple([
            'host' => $host,
            'settings' => $this->settings,
        ]);
        return $this->htmlResponse();
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
        $contentObject = $this->configurationManager->getContentObject();
        $contentData = is_array($contentObject->data ?? null) ? $contentObject->data : [];

        // Das RTE-Feld im Reiter "Allgemein" ist das TYPO3-Standardfeld bodytext.
        // Der ältere FlexForm-Wert settings.introText bleibt als Fallback erhalten.
        $bodytext = trim((string)($contentData['bodytext'] ?? ''));
        if ($bodytext !== '') {
            return $bodytext;
        }

        return trim((string)($this->settings['introText'] ?? ''));
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

