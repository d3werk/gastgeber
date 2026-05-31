<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use D3Werk\Gastgeber\Utility\CategoryIconResolver;

final class FilterController extends ActionController
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly CategoryIconResolver $categoryIconResolver,
    ) {
    }

    public function indexAction(): ResponseInterface
    {
        $rootCategoryUids = $this->parseUidList((string)($this->settings['rootCategoryUids'] ?? ''));
        if ($rootCategoryUids === []) {
            $rootCategoryUids = $this->findDefaultMerkmaleRootUids();
        }

        $selectedCategoryUids = $this->getSelectedNewsCategoryUids();
        $categoryConjunction = (int)($this->settings['categoryConjunction'] ?? 2);
        if (!in_array($categoryConjunction, [2, 3], true)) {
            $categoryConjunction = 2;
        }

        $tree = [];
        foreach ($rootCategoryUids as $rootCategoryUid) {
            $rootNode = $this->getCategoryNode($rootCategoryUid, $selectedCategoryUids, []);
            if ($rootNode !== null) {
                $tree[] = $rootNode;
            }
        }

        $this->view->assignMultiple([
            'categoryTree' => $tree,
            'selectedCategoryUids' => $selectedCategoryUids,
            'selectedCategoryCount' => count($selectedCategoryUids),
            'targetPid' => $this->resolveTargetPid((string)($this->settings['targetPid'] ?? '')),
            'categoryConjunction' => $categoryConjunction,
            'headline' => trim((string)($this->settings['headline'] ?? '')),
            'introText' => trim((string)($this->settings['introText'] ?? '')),
            'resetLabel' => trim((string)($this->settings['resetLabel'] ?? '')),
            'showRootCategories' => (bool)($this->settings['showRootCategories'] ?? false),
            'selectedView' => $this->getSelectedView(),
        ]);

        return $this->htmlResponse();
    }

    /**
     * @return list<int>
     */
    private function parseUidList(string $uidList): array
    {
        $uids = [];
        foreach (preg_split('/[,\s]+/', $uidList) ?: [] as $value) {
            $uid = (int)$value;
            if ($uid > 0) {
                $uids[] = $uid;
            }
        }

        return array_values(array_unique($uids));
    }

    /**
     * @return list<int>
     */
    private function getSelectedNewsCategoryUids(): array
    {
        $queryParams = [];
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if ($request instanceof ServerRequestInterface) {
            $queryParams = $request->getQueryParams();
        }

        $newsParams = $queryParams['tx_news_pi1'] ?? [];
        if (!is_array($newsParams)) {
            return [];
        }

        $overwriteDemand = $newsParams['overwriteDemand'] ?? [];
        if (!is_array($overwriteDemand)) {
            return [];
        }

        $categories = $overwriteDemand['categories'] ?? '';
        if (is_array($categories)) {
            $categories = implode(',', $categories);
        }

        return $this->parseUidList((string)$categories);
    }

    /**
     * @return list<int>
     */
    private function findDefaultMerkmaleRootUids(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_category');
        $result = $queryBuilder
            ->select('c.uid')
            ->from('sys_category', 'c')
            ->leftJoin(
                'c',
                'sys_category',
                'p',
                $queryBuilder->expr()->eq('c.parent', $queryBuilder->quoteIdentifier('p.uid'))
            )
            ->where(
                $queryBuilder->expr()->eq('c.title', $queryBuilder->createNamedParameter('Merkmale'))
            )
            ->andWhere(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq('p.title', $queryBuilder->createNamedParameter('Gastgeber')),
                    $queryBuilder->expr()->eq('c.parent', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
                )
            )
            ->orderBy('c.sorting', 'ASC')
            ->executeQuery();

        $uids = [];
        while (($uid = $result->fetchOne()) !== false) {
            $uid = (int)$uid;
            if ($uid > 0) {
                $uids[] = $uid;
            }
        }

        return $uids;
    }

    /**
     * @param list<int> $selectedCategoryUids
     * @param list<int> $visited
     * @return array<string, mixed>|null
     */
    private function getCategoryNode(int $categoryUid, array $selectedCategoryUids, array $visited): ?array
    {
        if (in_array($categoryUid, $visited, true)) {
            return null;
        }
        $visited[] = $categoryUid;

        $category = $this->fetchCategory($categoryUid);
        if ($category === null) {
            return null;
        }

        if ((int)($category['tx_gastgeber_filter_hidden'] ?? 0) === 1) {
            return null;
        }

        $children = [];
        foreach ($this->fetchChildCategoryUids($categoryUid) as $childUid) {
            $childNode = $this->getCategoryNode($childUid, $selectedCategoryUids, $visited);
            if ($childNode !== null) {
                $children[] = $childNode;
            }
        }

        $isActive = in_array($categoryUid, $selectedCategoryUids, true);
        $toggleList = $selectedCategoryUids;
        if ($isActive) {
            $toggleList = array_values(array_filter(
                $toggleList,
                static fn (int $uid): bool => $uid !== $categoryUid
            ));
        } else {
            $toggleList[] = $categoryUid;
        }
        $toggleList = array_values(array_unique(array_filter($toggleList, static fn (int $uid): bool => $uid > 0)));

        $icon = $this->categoryIconResolver->resolve($categoryUid);

        $node = [
            'uid' => $categoryUid,
            'title' => (string)$category['title'],
            'description' => (string)($category['description'] ?? ''),
            'iconUrl' => $icon['url'],
            'iconCssClass' => $icon['cssClass'],
            'children' => $children,
            'active' => $isActive,
            'filterCategoryList' => implode(',', $toggleList),
            'hasFilterCategories' => $toggleList !== [],
        ];
        $selfNode = $node;
        $node['selfAsList'] = [$selfNode];

        return $node;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchCategory(int $categoryUid): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_category');
        $row = $queryBuilder
            ->select('uid', 'title', 'description', 'tx_gastgeber_filter_hidden')
            ->from('sys_category')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($categoryUid, Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAssociative();

        return is_array($row) ? $row : null;
    }

    /**
     * @return list<int>
     */
    private function fetchChildCategoryUids(int $parentUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_category');
        $result = $queryBuilder
            ->select('uid')
            ->from('sys_category')
            ->where(
                $queryBuilder->expr()->eq('parent', $queryBuilder->createNamedParameter($parentUid, Connection::PARAM_INT))
            )
            ->orderBy('sorting', 'ASC')
            ->addOrderBy('title', 'ASC')
            ->executeQuery();

        $uids = [];
        while (($uid = $result->fetchOne()) !== false) {
            $uid = (int)$uid;
            if ($uid > 0) {
                $uids[] = $uid;
            }
        }

        return $uids;
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

    private function resolveTargetPid(string $targetPid): int
    {
        $parsedTargetPid = (int)$targetPid;
        if ($parsedTargetPid > 0) {
            return $parsedTargetPid;
        }

        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if ($request instanceof ServerRequestInterface) {
            $routing = $request->getAttribute('routing');
            if (is_object($routing) && method_exists($routing, 'getPageId')) {
                return (int)$routing->getPageId();
            }
        }

        return 0;
    }
}
