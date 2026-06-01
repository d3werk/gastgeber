<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Controller;

use D3Werk\Gastgeber\Utility\FilterDataProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class FilterController extends ActionController
{
    public function __construct(
        private readonly FilterDataProvider $filterDataProvider,
    ) {
    }

    public function indexAction(): ResponseInterface
    {
        $showRootCategories = (bool)($this->settings['showRootCategories'] ?? false);
        $filterData = $this->filterDataProvider->build(
            (string)($this->settings['rootCategoryUids'] ?? ''),
            (string)($this->settings['categoryConjunction'] ?? 'or'),
            (string)($this->settings['targetPid'] ?? ''),
            $showRootCategories,
            true
        );

        $this->view->assignMultiple([
            'categoryTree' => $filterData['categoryTree'],
            'selectedCategoryUids' => $filterData['selectedCategoryUids'],
            'selectedCategoryCount' => $filterData['selectedCategoryCount'],
            'targetPid' => $filterData['targetPid'],
            'categoryConjunction' => $filterData['categoryConjunction'],
            'headline' => trim((string)($this->settings['headline'] ?? '')),
            'introText' => trim((string)($this->settings['introText'] ?? '')),
            'resetLabel' => trim((string)($this->settings['resetLabel'] ?? '')),
            'showRootCategories' => $showRootCategories,
            'showViewSwitch' => (bool)($this->settings['showViewSwitch'] ?? true),
            'panelExpanded' => (bool)($this->settings['panelExpanded'] ?? true),
            'selectedView' => $this->getSelectedView(),
        ]);

        return $this->htmlResponse();
    }

    private function getSelectedView(): string
    {
        $allowed = ['cards', 'list', 'map'];
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return 'cards';
        }

        $queryParams = $request->getQueryParams();
        $selectedView = $queryParams['tx_gastgeber_view'] ?? 'cards';
        if (is_array($selectedView)) {
            return 'cards';
        }

        $selectedView = strtolower((string)$selectedView);
        return in_array($selectedView, $allowed, true) ? $selectedView : 'cards';
    }
}
