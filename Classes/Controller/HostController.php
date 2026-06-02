<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Controller;

use D3Werk\Gastgeber\Domain\Model\Host;
use D3Werk\Gastgeber\Domain\Repository\HostRepository;
use D3Werk\Gastgeber\Utility\FilterDataProvider;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class HostController extends ActionController
{
    public function __construct(
        private readonly HostRepository $hostRepository,
        private readonly FilterDataProvider $filterDataProvider,
        private readonly PageRenderer $pageRenderer,
        private readonly MetaTagManagerRegistry $metaTagManagerRegistry,
    ) {}

    public function listAction(): ResponseInterface
    {
        $settings = $this->settings;
        $activeCategories = $this->readCategoryArguments();
        $viewMode = $this->readViewMode($settings['defaultView'] ?? $settings['list']['defaultView'] ?? 'cards');
        $conjunction = (string)($settings['categoryConjunction'] ?? $settings['list']['categoryConjunction'] ?? 'or');
        $hosts = $this->hostRepository->findDemanded($settings, $activeCategories, $conjunction);
        $filterGroups = $this->filterDataProvider->getFilterGroups(
            $settings['filterRootCategories'] ?? '',
            $activeCategories,
            (bool)($settings['showRootCategories'] ?? false)
        );

        $this->view->assignMultiple([
            'hosts' => $hosts,
            'filterGroups' => $filterGroups,
            'activeCategories' => $activeCategories,
            'viewMode' => $viewMode,
            'settings' => $settings,
        ]);
        return $this->htmlResponse();
    }

    public function detailAction(?Host $host = null): ResponseInterface
    {
        if ($host === null) {
            $hostUid = (int)($this->request->getArgument('host') ?? 0);
            if ($hostUid > 0) {
                $host = $this->hostRepository->findByUid($hostUid);
            }
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

    /** @return array<int,int> */
    private function readCategoryArguments(): array
    {
        $categories = [];
        if ($this->request->hasArgument('categories')) {
            $argument = $this->request->getArgument('categories');
            if (is_array($argument)) {
                $categories = array_map('intval', $argument);
            } else {
                $categories = array_map('intval', explode(',', (string)$argument));
            }
        }
        return array_values(array_unique(array_filter($categories)));
    }

    private function readViewMode(string $defaultView): string
    {
        $view = $defaultView;
        if ($this->request->hasArgument('view')) {
            $view = (string)$this->request->getArgument('view');
        }
        return in_array($view, ['cards', 'list', 'map'], true) ? $view : 'cards';
    }

    private function applySeo(Host $host): void
    {
        $title = $host->getSeoTitle() !== '' ? $host->getSeoTitle() : $host->getTitle();
        $description = $host->getMetaDescription() !== '' ? $host->getMetaDescription() : $host->getTeaser();
        if ($title !== '') {
            $this->pageRenderer->setTitle($title);
            $this->metaTagManagerRegistry->getManagerForProperty('og:title')->addProperty('og:title', $host->getOgTitle() !== '' ? $host->getOgTitle() : $title);
        }
        if ($description !== '') {
            $this->metaTagManagerRegistry->getManagerForProperty('description')->addProperty('description', $description);
            $this->metaTagManagerRegistry->getManagerForProperty('og:description')->addProperty('og:description', $host->getOgDescription() !== '' ? $host->getOgDescription() : $description);
        }
        if ($host->isNoindex()) {
            $this->metaTagManagerRegistry->getManagerForProperty('robots')->addProperty('robots', 'noindex,follow');
        }
    }
}
