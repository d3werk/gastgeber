<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Utility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FilterDataProvider
{
    public function __construct(private readonly CategoryIconResolver $categoryIconResolver) {}

    /**
     * @param array<int,int> $activeCategories
     * @return array<int,array<string,mixed>>
     */
    public function getFilterGroups(string|array|null $rootCategoryUids, array $activeCategories = [], bool $showRoots = false): array
    {
        $roots = $this->normalizeUidList($rootCategoryUids);
        if ($roots === []) {
            $roots = $this->findGastgeberRootUids();
        }
        $groups = [];
        foreach ($roots as $rootUid) {
            $children = $this->fetchChildren($rootUid);
            if ($children === []) {
                continue;
            }
            if ($showRoots) {
                $rootRow = $this->fetchCategory($rootUid);
                if ($rootRow !== []) {
                    array_unshift($children, $rootRow);
                }
            }
            foreach ($children as $child) {
                $subChildren = $this->fetchVisibleChildren((int)$child['uid']);
                if ($subChildren !== []) {
                    $items = $this->buildItems($subChildren, $activeCategories);
                    if ($items !== []) {
                        $groups[] = [
                            'uid' => (int)$child['uid'],
                            'title' => (string)$child['title'],
                            'items' => $items,
                        ];
                    }
                } elseif (!(bool)($child['tx_gastgeber_hide_filter'] ?? false)) {
                    $items = $this->buildItems([$child], $activeCategories);
                    if ($items !== []) {
                        $groups[] = [
                            'uid' => (int)$child['uid'],
                            'title' => (string)$child['title'],
                            'items' => $items,
                        ];
                    }
                }
            }
        }
        return $groups;
    }

    /** @param array<int,array<string,mixed>> $rows */
    private function buildItems(array $rows, array $activeCategories): array
    {
        $items = [];
        foreach ($rows as $row) {
            if ((bool)($row['tx_gastgeber_hide_filter'] ?? false)) {
                continue;
            }
            $uid = (int)$row['uid'];
            $active = in_array($uid, $activeCategories, true);
            $linkCategories = $active ? array_values(array_diff($activeCategories, [$uid])) : array_values(array_unique([...$activeCategories, $uid]));
            $items[] = [
                'uid' => $uid,
                'title' => (string)$row['title'],
                'active' => $active,
                'linkCategories' => $linkCategories,
                'presentation' => $this->categoryIconResolver->getPresentation($uid, (string)$row['title']),
            ];
        }
        return $items;
    }

    /** @return array<int,int> */
    private function normalizeUidList(string|array|null $value): array
    {
        if (is_array($value)) {
            return array_values(array_unique(array_filter(array_map('intval', $value))));
        }
        if ($value === null || trim((string)$value) === '') {
            return [];
        }
        return array_values(array_unique(array_filter(array_map('intval', explode(',', (string)$value)))));
    }

    /** @return array<int,int> */
    private function findGastgeberRootUids(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_category');
        $rows = $queryBuilder
            ->select('uid')
            ->from('sys_category')
            ->where($queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter('Gastgeber')))
            ->executeQuery()
            ->fetchAllAssociative();
        return array_map(static fn(array $row): int => (int)$row['uid'], $rows);
    }

    private function fetchCategory(int $uid): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_category');
        $row = $queryBuilder
            ->select('*')
            ->from('sys_category')
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();
        return is_array($row) ? $row : [];
    }

    /** @return array<int,array<string,mixed>> */
    private function fetchChildren(int $parentUid): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_category');
        return $queryBuilder
            ->select('*')
            ->from('sys_category')
            ->where($queryBuilder->expr()->eq('parent', $queryBuilder->createNamedParameter($parentUid, \PDO::PARAM_INT)))
            ->orderBy('sorting', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /** @return array<int,array<string,mixed>> */
    private function fetchVisibleChildren(int $parentUid): array
    {
        return array_values(array_filter($this->fetchChildren($parentUid), static fn(array $row): bool => !(bool)($row['tx_gastgeber_hide_filter'] ?? false)));
    }
}
