<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\FormEngine\ItemsProcFunc;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Improves the feature selector in host records.
 *
 * TYPO3's side-by-side selector can only render simple option labels reliably.
 * To still give editors a visual orientation, this class prefixes each feature
 * with a small icon hint derived from the configured icon CSS class and appends
 * the feature group. The actual frontend output still uses uploaded SVG/PNG
 * icons or the configured CSS icon class.
 */
final class FeatureItems
{
    /**
     * @param array<string,mixed> $params
     */
    public function addIconLabels(array &$params): void
    {
        if (empty($params['items']) || !is_array($params['items'])) {
            return;
        }

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
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
            $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_gastgeber_domain_model_feature');
            $rows = $queryBuilder
                ->select('f.uid', 'f.title', 'f.icon_class', 'g.title AS group_title')
                ->from('tx_gastgeber_domain_model_feature', 'f')
                ->leftJoin(
                    'f',
                    'tx_gastgeber_domain_model_featuregroup',
                    'g',
                    $queryBuilder->expr()->eq('g.uid', $queryBuilder->quoteIdentifier('f.group'))
                )
                ->where($queryBuilder->expr()->in('f.uid', $queryBuilder->createNamedParameter($uids, \TYPO3\CMS\Core\Database\Connection::PARAM_INT_ARRAY)))
                ->executeQuery()
                ->fetchAllAssociative();
        } catch (\Throwable) {
            // During first install / Analyze Database Structure the tables may not exist yet.
            return;
        }

        $featureMeta = [];
        foreach ($rows as $row) {
            $featureMeta[(int)$row['uid']] = [
                'title' => (string)($row['title'] ?? ''),
                'iconClass' => (string)($row['icon_class'] ?? ''),
                'groupTitle' => (string)($row['group_title'] ?? ''),
            ];
        }

        foreach ($params['items'] as &$item) {
            $value = (int)($item['value'] ?? ($item[1] ?? 0));
            if ($value <= 0 || !isset($featureMeta[$value])) {
                continue;
            }

            $meta = $featureMeta[$value];
            $label = trim($meta['title']);
            $group = trim($meta['groupTitle']);
            $prefix = $this->resolveTextIcon($meta['iconClass'], $label);

            $newLabel = trim($prefix . ' ' . $label);
            if ($group !== '') {
                $newLabel .= '  ·  ' . $group;
            }

            if (array_key_exists('label', $item)) {
                $item['label'] = $newLabel;
            } else {
                $item[0] = $newLabel;
            }
        }
        unset($item);
    }

    private function resolveTextIcon(string $iconClass, string $title): string
    {
        $haystack = mb_strtolower($iconClass . ' ' . $title);
        $map = [
            'wifi' => '🛜',
            'wlan' => '🛜',
            'parking' => '🅿️',
            'parkplatz' => '🅿️',
            'car' => '🅿️',
            'dog' => '🐕',
            'haustier' => '🐕',
            'pet' => '🐕',
            'breakfast' => '☕',
            'frühstück' => '☕',
            'coffee' => '☕',
            'restaurant' => '🍽️',
            'kitchen' => '🍳',
            'küche' => '🍳',
            'bed' => '🛏️',
            'bett' => '🛏️',
            'baby' => '👶',
            'kind' => '👶',
            'family' => '👨‍👩‍👧',
            'familie' => '👨‍👩‍👧',
            'wheelchair' => '♿',
            'barriere' => '♿',
            'accessible' => '♿',
            'ev' => '🔌',
            'ladestation' => '🔌',
            'charging' => '🔌',
            'bike' => '🚲',
            'fahrrad' => '🚲',
            'bicycle' => '🚲',
            'hiking' => '🥾',
            'wandern' => '🥾',
            'garden' => '🌿',
            'garten' => '🌿',
            'terrasse' => '🌿',
            'balkon' => '🌿',
            'pool' => '🏊',
            'sauna' => '♨️',
            'spa' => '♨️',
            'tv' => '📺',
            'washing' => '🧺',
            'waschmaschine' => '🧺',
            'dishwasher' => '🍽️',
            'geschirrspüler' => '🍽️',
            'horse' => '🐴',
            'pferd' => '🐴',
        ];

        foreach ($map as $needle => $icon) {
            if (str_contains($haystack, $needle)) {
                return $icon;
            }
        }

        return '•';
    }
}
