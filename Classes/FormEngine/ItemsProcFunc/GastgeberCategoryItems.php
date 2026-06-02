<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\FormEngine\ItemsProcFunc;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GastgeberCategoryItems
{
    public function addItems(array &$parameters): void
    {
        $rootUids = $this->findRootUids();
        if ($rootUids === []) {
            $parameters['items'][] = ['label' => 'Gastgeber-Kategoriebaum noch nicht angelegt', 'value' => 0];
            return;
        }
        foreach ($rootUids as $rootUid) {
            $this->appendCategory($parameters['items'], $rootUid, 0);
        }
    }

    /** @return array<int,int> */
    private function findRootUids(): array
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_category');
        $rows = $qb->select('uid')->from('sys_category')
            ->where($qb->expr()->eq('title', $qb->createNamedParameter('Gastgeber')))
            ->orderBy('sorting', 'ASC')
            ->executeQuery()->fetchAllAssociative();
        return array_map(static fn(array $row): int => (int)$row['uid'], $rows);
    }

    private function appendCategory(array &$items, int $uid, int $level): void
    {
        $row = $this->fetchCategory($uid);
        if ($row === []) {
            return;
        }
        $items[] = [
            'label' => str_repeat('— ', $level) . (string)$row['title'],
            'value' => $uid,
        ];
        foreach ($this->fetchChildren($uid) as $child) {
            $this->appendCategory($items, (int)$child['uid'], $level + 1);
        }
    }

    private function fetchCategory(int $uid): array
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_category');
        $row = $qb->select('*')->from('sys_category')
            ->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid, \PDO::PARAM_INT)))
            ->executeQuery()->fetchAssociative();
        return is_array($row) ? $row : [];
    }

    private function fetchChildren(int $parent): array
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_category');
        return $qb->select('*')->from('sys_category')
            ->where($qb->expr()->eq('parent', $qb->createNamedParameter($parent, \PDO::PARAM_INT)))
            ->orderBy('sorting', 'ASC')
            ->executeQuery()->fetchAllAssociative();
    }
}
