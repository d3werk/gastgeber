<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Utility;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FileRepository;

final class CategoryIconResolver
{
    /** @var array<int, array{url:string, cssClass:string}> */
    private array $cache = [];

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly FileRepository $fileRepository,
    ) {
    }

    /**
     * @return array{url:string, cssClass:string}
     */
    public function resolve(int $categoryUid): array
    {
        if ($categoryUid <= 0) {
            return ['url' => '', 'cssClass' => ''];
        }

        if (isset($this->cache[$categoryUid])) {
            return $this->cache[$categoryUid];
        }

        $data = [
            'url' => $this->resolveFileUrl($categoryUid),
            'cssClass' => $this->resolveCssClass($categoryUid),
        ];

        $this->cache[$categoryUid] = $data;
        return $data;
    }

    private function resolveFileUrl(int $categoryUid): string
    {
        try {
            $fileReferences = $this->fileRepository->findByRelation('sys_category', 'tx_gastgeber_icon', $categoryUid);
            if ($fileReferences === []) {
                return '';
            }

            $fileReference = $fileReferences[0];
            if (method_exists($fileReference, 'getPublicUrl')) {
                return (string)$fileReference->getPublicUrl();
            }
        } catch (\Throwable) {
            return '';
        }

        return '';
    }

    private function resolveCssClass(int $categoryUid): string
    {
        try {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_category');
            $value = $queryBuilder
                ->select('tx_gastgeber_icon_css_class')
                ->from('sys_category')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($categoryUid, Connection::PARAM_INT))
                )
                ->setMaxResults(1)
                ->executeQuery()
                ->fetchOne();
        } catch (\Throwable) {
            return '';
        }

        return trim((string)($value ?: ''));
    }
}
