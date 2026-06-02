<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Utility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CategoryIconResolver
{
    /** @return array<string,mixed> */
    public function getPresentation(int $categoryUid, string $fallbackTitle = ''): array
    {
        $row = $this->fetchCategoryRow($categoryUid);
        $title = $fallbackTitle !== '' ? $fallbackTitle : (string)($row['title'] ?? '');
        $presentation = [
            'uid' => $categoryUid,
            'title' => $title,
            'iconClass' => (string)($row['tx_gastgeber_icon_class'] ?? ''),
            'iconUrl' => $this->resolveIconUrl($categoryUid),
            'isRating' => $this->isRatingTitle($title),
            'ratingStars' => $this->ratingStars($title),
            'hideFilter' => (bool)($row['tx_gastgeber_hide_filter'] ?? false),
            'topFeature' => (bool)($row['tx_gastgeber_top_feature'] ?? false),
        ];
        return $presentation;
    }

    private function fetchCategoryRow(int $categoryUid): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_category');
        $row = $queryBuilder
            ->select('*')
            ->from('sys_category')
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($categoryUid, \PDO::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();
        return is_array($row) ? $row : [];
    }

    private function resolveIconUrl(int $categoryUid): string
    {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $references = $fileRepository->findByRelation('sys_category', 'tx_gastgeber_icon', $categoryUid);
        if ($references === []) {
            return '';
        }
        $file = $references[0]->getOriginalFile();
        return $file->getPublicUrl() ?? '';
    }

    private function isRatingTitle(string $title): bool
    {
        return (bool)preg_match('/^(keine\s+sterne|[1-5]\s*stern|[1-5]\s*sterne|\*{1,5})$/iu', trim($title));
    }

    private function ratingStars(string $title): int
    {
        $normalized = trim($title);
        if (preg_match('/^([1-5])\s*stern/iu', $normalized, $matches)) {
            return (int)$matches[1];
        }
        if (preg_match('/^(\*{1,5})$/', $normalized, $matches)) {
            return strlen($matches[1]);
        }
        return 0;
    }
}
