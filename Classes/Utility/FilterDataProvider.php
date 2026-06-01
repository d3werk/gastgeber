<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Utility;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

final class FilterDataProvider
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly CategoryIconResolver $categoryIconResolver,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(
        string $rootCategoryUids = '',
        string $categoryConjunction = 'or',
        string $targetPid = '',
        bool $showRootCategories = false,
        bool $preferGastgeberRoot = true,
    ): array {
        $rootUids = $this->parseUidList($rootCategoryUids);
        if ($rootUids === []) {
            $rootUids = $preferGastgeberRoot ? $this->findDefaultGastgeberRootUids() : $this->findDefaultMerkmaleRootUids();
        }

        $selectedCategoryUids = $this->getSelectedNewsCategoryUids();
        $categoryConjunction = $this->normalizeCategoryConjunction($categoryConjunction);

        $tree = [];
        foreach ($rootUids as $rootUid) {
            $rootNode = $this->getCategoryNode($rootUid, $selectedCategoryUids, []);
            if ($rootNode !== null) {
                $tree[] = $rootNode;
            }
        }

        return [
            'categoryTree' => $tree,
            'selectedCategoryUids' => $selectedCategoryUids,
            'selectedCategoryCount' => count($selectedCategoryUids),
            'targetPid' => $this->resolveTargetPid($targetPid),
            'categoryConjunction' => $categoryConjunction,
            'showRootCategories' => $showRootCategories,
            'hasCategories' => $tree !== [],
        ];
    }

    /**
     * @return list<int>
     */
    public function parseUidList(string $uidList): array
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

    public function normalizeCategoryConjunction(string $categoryConjunction): string
    {
        $categoryConjunction = strtolower(trim($categoryConjunction));
        if (in_array($categoryConjunction, ['or', 'and'], true)) {
            return $categoryConjunction;
        }

        return match ($categoryConjunction) {
            '3' => 'and',
            default => 'or',
        };
    }

    /**
     * @return list<int>
     */
    private function getSelectedNewsCategoryUids(): array
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return [];
        }

        $queryParams = $request->getQueryParams();
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
     * Bevorzugt die oberste Kategorie "Gastgeber". Dadurch kann ein einziges
     * Listen-/Filter-Element alle Gruppen wie Gastgeber-Art, Klassifizierung,
     * Merkmale und Zielgruppen anzeigen. Das ist redaktionell sicherer als nur
     * einzelne Blatt-Kategorien auszuwählen.
     *
     * @return list<int>
     */
    private function findDefaultGastgeberRootUids(): array
    {
        $uids = $this->findCategoryUidsByTitle('Gastgeber');
        if ($uids !== []) {
            return $uids;
        }

        return $this->findDefaultMerkmaleRootUids();
    }

    /**
     * @return list<int>
     */
    private function findDefaultMerkmaleRootUids(): array
    {
        $uids = $this->findCategoryUidsByTitle('Merkmale');
        return $uids !== [] ? $uids : [];
    }

    /**
     * @return list<int>
     */
    private function findCategoryUidsByTitle(string $title): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_category');
        $result = $queryBuilder
            ->select('uid')
            ->from('sys_category')
            ->where(
                $queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter($title))
            )
            ->orderBy('parent', 'ASC')
            ->addOrderBy('sorting', 'ASC')
            ->executeQuery();

        $uids = [];
        while (($uid = $result->fetchOne()) !== false) {
            $uid = (int)$uid;
            if ($uid > 0) {
                $uids[] = $uid;
            }
        }

        return array_values(array_unique($uids));
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
            $toggleList = array_values(array_filter($toggleList, static fn (int $uid): bool => $uid !== $categoryUid));
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
            'hasChildren' => $children !== [],
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
