<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\FormEngine\ItemsProcFunc;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Provides a backend-friendly category selector that only shows the
 * Gastgeber taxonomy instead of the complete global sys_category tree.
 */
final class GastgeberCategoryItems
{
    private const DEFAULT_ROOT_TITLE = 'Gastgeber';

    /**
     * @param array<string, mixed> $parameters
     */
    public function itemsProcFunc(array &$parameters): void
    {
        $items = [];
        foreach ($this->findGastgeberRootUids() as $rootUid) {
            $this->appendCategoryWithChildren($items, $rootUid, 0, []);
        }

        if ($items === []) {
            $items[] = [
                'label' => 'Keine Gastgeber-Kategorien gefunden. Bitte zuerst Kategorien mit "gastgeber:categories:create" anlegen.',
                'value' => 0,
                'disabled' => true,
            ];
        }

        $parameters['items'] = $items;
    }

    /**
     * @return list<int>
     */
    private function findGastgeberRootUids(): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_category');
        $result = $queryBuilder
            ->select('uid')
            ->from('sys_category')
            ->where(
                $queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter(self::DEFAULT_ROOT_TITLE)),
                $queryBuilder->expr()->eq('parent', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
            )
            ->orderBy('sorting', 'ASC')
            ->addOrderBy('uid', 'ASC')
            ->executeQuery();

        $uids = [];
        while (($uid = $result->fetchOne()) !== false) {
            $uid = (int)$uid;
            if ($uid > 0) {
                $uids[] = $uid;
            }
        }

        if ($uids !== []) {
            return $uids;
        }

        // Fallback: In manchen Projekten liegt die Kategorie "Gastgeber" nicht auf Root-Ebene.
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_category');
        $result = $queryBuilder
            ->select('uid')
            ->from('sys_category')
            ->where(
                $queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter(self::DEFAULT_ROOT_TITLE))
            )
            ->orderBy('parent', 'ASC')
            ->addOrderBy('sorting', 'ASC')
            ->executeQuery();

        while (($uid = $result->fetchOne()) !== false) {
            $uid = (int)$uid;
            if ($uid > 0) {
                $uids[] = $uid;
            }
        }

        return array_values(array_unique($uids));
    }

    /**
     * @param list<array{label:string,value:int,group?:string,icon?:string,description?:string,disabled?:bool}> $items
     * @param list<int> $visited
     */
    private function appendCategoryWithChildren(array &$items, int $uid, int $level, array $visited): void
    {
        if ($uid <= 0 || in_array($uid, $visited, true)) {
            return;
        }
        $visited[] = $uid;

        $category = $this->fetchCategory($uid);
        if ($category === null) {
            return;
        }

        $prefix = $level > 0 ? str_repeat('— ', $level) : '';
        $items[] = [
            'label' => $prefix . (string)$category['title'],
            'value' => (int)$category['uid'],
        ];

        foreach ($this->fetchChildCategoryUids($uid) as $childUid) {
            $this->appendCategoryWithChildren($items, $childUid, $level + 1, $visited);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchCategory(int $uid): ?array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_category');
        $row = $queryBuilder
            ->select('uid', 'title')
            ->from('sys_category')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT))
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
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_category');
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

    private function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
