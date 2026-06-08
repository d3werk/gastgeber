<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\FormEngine\ItemsProcFunc;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Adds editor friendly labels to certificate / star records in host forms.
 */
final class CertificateItems
{
    /**
     * @param array<string,mixed> $params
     */
    public function addStarLabels(array &$params): void
    {
        if (empty($params['items']) || !is_array($params['items'])) {
            return;
        }

        foreach ($params['items'] as &$item) {
            if (!is_array($item)) {
                continue;
            }
            if (!array_key_exists('label', $item)) {
                $item['label'] = (string)($item[0] ?? '');
            }
            if (!array_key_exists('value', $item)) {
                $item['value'] = $item[1] ?? 0;
            }
        }
        unset($item);

        $uids = [];
        foreach ($params['items'] as $item) {
            $value = $item['value'] ?? ($item[1] ?? null);
            if ((int)$value > 0) {
                $uids[] = (int)$value;
            }
        }
        $uids = array_values(array_unique($uids));
        if ($uids === []) {
            return;
        }

        try {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('tx_gastgeber_domain_model_certificate');
            $rows = $queryBuilder
                ->select('uid', 'title', 'issuer', 'rating_value')
                ->from('tx_gastgeber_domain_model_certificate')
                ->where($queryBuilder->expr()->in('uid', $queryBuilder->createNamedParameter($uids, Connection::PARAM_INT_ARRAY)))
                ->executeQuery()
                ->fetchAllAssociative();
        } catch (\Throwable) {
            return;
        }

        $meta = [];
        foreach ($rows as $row) {
            $title = (string)($row['title'] ?? '');
            $stars = (int)round((float)($row['rating_value'] ?? 0));
            if ($stars <= 0 && preg_match('/([1-5])/', $title, $matches) === 1) {
                $stars = (int)$matches[1];
            }
            $stars = max(0, min(5, $stars));
            $meta[(int)$row['uid']] = [
                'title' => $title,
                'issuer' => (string)($row['issuer'] ?? ''),
                'stars' => $stars,
            ];
        }

        foreach ($params['items'] as &$item) {
            $value = (int)($item['value'] ?? ($item[1] ?? 0));
            if ($value <= 0 || !isset($meta[$value])) {
                continue;
            }
            $entry = $meta[$value];
            $label = trim(($entry['stars'] > 0 ? str_repeat('★', $entry['stars']) . ' ' : '') . $entry['title']);
            if ($entry['issuer'] !== '') {
                $label .= '  ·  ' . $entry['issuer'];
            }
            $item['label'] = $label;
            $item[0] = $label;
        }
        unset($item);
    }
}
