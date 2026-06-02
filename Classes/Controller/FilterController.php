<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Controller;

use D3Werk\Gastgeber\Utility\FilterDataProvider;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class FilterController extends ActionController
{
    public function __construct(private readonly FilterDataProvider $filterDataProvider) {}

    public function indexAction(): ResponseInterface
    {
        $activeCategories = [];
        if ($this->request->hasArgument('categories')) {
            $argument = $this->request->getArgument('categories');
            $activeCategories = is_array($argument)
                ? array_map('intval', $argument)
                : array_map('intval', explode(',', (string)$argument));
        }
        $filterGroups = $this->filterDataProvider->getFilterGroups(
            $this->settings['filterRootCategories'] ?? '',
            $activeCategories,
            (bool)($this->settings['showRootCategories'] ?? false)
        );
        $this->view->assignMultiple([
            'filterGroups' => $filterGroups,
            'activeCategories' => array_values(array_unique(array_filter($activeCategories))),
            'settings' => $this->settings,
        ]);
        return $this->htmlResponse();
    }
}
