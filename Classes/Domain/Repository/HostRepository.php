<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class HostRepository extends Repository
{
    protected $defaultOrderings = [
        'featured' => QueryInterface::ORDER_DESCENDING,
        'priority' => QueryInterface::ORDER_DESCENDING,
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

    public function findOneBySlug(string $slug)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->matching($query->equals('slug', $slug));
        return $query->execute()->getFirst();
    }

    /**
     * @param array<string,mixed> $settings
     * @param array<string,mixed> $filters
     */
    public function findDemanded(array $settings = [], array $filters = [])
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $constraints = [];

        $storagePid = $settings['storagePid'] ?? $settings['startingpoint'] ?? '';
        $pids = $this->cleanUidList($storagePid);
        if ($pids !== []) {
            $constraints[] = $query->in('pid', $pids);
        }

        $baseTypes = $this->cleanUidList($settings['types'] ?? '');
        $typeUids = $this->cleanUidList($filters['types'] ?? []);
        $typeUids = $typeUids !== [] ? $typeUids : $baseTypes;
        if ($typeUids !== []) {
            $constraints[] = $query->in('type', $typeUids);
        }

        $districtUids = $this->cleanUidList($filters['districts'] ?? []);
        if ($districtUids !== []) {
            $constraints[] = $query->in('district', $districtUids);
        }

        $featureUids = $this->cleanUidList($filters['features'] ?? []);
        if ($featureUids !== []) {
            $hostUids = $this->findHostUidsByFeatures($featureUids, (string)($settings['featureConjunction'] ?? 'and'));
            if ($hostUids === []) {
                $constraints[] = $query->equals('uid', 0);
            } else {
                $constraints[] = $query->in('uid', $hostUids);
            }
        }

        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            $like = '%' . $search . '%';
            $constraints[] = $query->logicalOr(
                $query->like('title', $like),
                $query->like('teaser', $like),
                $query->like('description', $like),
                $query->like('street', $like),
                $query->like('city', $like)
            );
        }

        if ((bool)($settings['featuredOnly'] ?? false)) {
            $constraints[] = $query->equals('featured', true);
        }

        if ($constraints !== []) {
            $query->matching($query->logicalAnd(...$constraints));
        }

        $sort = (string)($filters['sort'] ?? ($settings['sort'] ?? 'default'));
        if ($sort === 'title') {
            $query->setOrderings(['title' => QueryInterface::ORDER_ASCENDING]);
        } elseif ($sort === 'price') {
            $query->setOrderings(['priceFrom' => QueryInterface::ORDER_ASCENDING, 'title' => QueryInterface::ORDER_ASCENDING]);
        }

        $limit = (int)($settings['itemsPerPage'] ?? 0);
        if ($limit > 0) {
            $query->setLimit($limit);
        }

        return $query->execute();
    }

    /** @param array<int,int> $featureUids @return array<int,int> */
    private function findHostUidsByFeatures(array $featureUids, string $conjunction): array
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_gastgeber_host_feature_mm');
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder
            ->select('uid_local')
            ->from('tx_gastgeber_host_feature_mm')
            ->where($queryBuilder->expr()->in('uid_foreign', $queryBuilder->createNamedParameter($featureUids, \TYPO3\CMS\Core\Database\Connection::PARAM_INT_ARRAY)))
            ->groupBy('uid_local');
        if (strtolower($conjunction) === 'and') {
            $queryBuilder->having('COUNT(DISTINCT uid_foreign) = ' . count($featureUids));
        }
        return array_map('intval', array_column($queryBuilder->executeQuery()->fetchAllAssociative(), 'uid_local'));
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
