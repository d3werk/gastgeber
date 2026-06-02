<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class HostRepository extends Repository
{
    protected $defaultOrderings = [
        'featured' => QueryInterface::ORDER_DESCENDING,
        'sorting' => QueryInterface::ORDER_ASCENDING,
        'title' => QueryInterface::ORDER_ASCENDING,
    ];


    public function findByUid($uid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->matching($query->equals('uid', (int)$uid));
        return $query->execute()->getFirst();
    }

    /**
     * @param array<string,mixed> $settings
     * @param array<int,int> $categoryUids
     */
    public function findDemanded(array $settings = [], array $categoryUids = [], string $conjunction = 'or')
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $constraints = [];

        $storagePid = $settings['storagePid'] ?? $settings['startingpoint'] ?? '';
        if ($storagePid !== '') {
            $pids = array_filter(array_map('intval', explode(',', (string)$storagePid)));
            if ($pids !== []) {
                $constraints[] = $query->in('pid', $pids);
            }
        }

        $baseCategories = $this->cleanUidList($settings['categories'] ?? '');
        foreach ($baseCategories as $categoryUid) {
            $constraints[] = $query->contains('categories', $categoryUid);
        }

        $categoryUids = array_values(array_unique(array_filter(array_map('intval', $categoryUids))));
        if ($categoryUids !== []) {
            $categoryConstraints = [];
            foreach ($categoryUids as $categoryUid) {
                $categoryConstraints[] = $query->contains('categories', $categoryUid);
            }
            $constraints[] = strtolower($conjunction) === 'and'
                ? $query->logicalAnd(...$categoryConstraints)
                : $query->logicalOr(...$categoryConstraints);
        }

        if ($constraints !== []) {
            $query->matching($query->logicalAnd(...$constraints));
        }

        $limit = (int)($settings['itemsPerPage'] ?? 0);
        if ($limit > 0) {
            $query->setLimit($limit);
        }

        return $query->execute();
    }

    /** @return array<int,int> */
    private function cleanUidList(string|array|null $value): array
    {
        if (is_array($value)) {
            return array_values(array_unique(array_filter(array_map('intval', $value))));
        }
        if ($value === null || trim((string)$value) === '') {
            return [];
        }
        return array_values(array_unique(array_filter(array_map('intval', explode(',', (string)$value)))));
    }
}
