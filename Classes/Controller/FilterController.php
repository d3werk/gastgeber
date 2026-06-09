<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Controller;

use D3Werk\Gastgeber\Domain\Repository\DistrictRepository;
use D3Werk\Gastgeber\Domain\Repository\FeatureGroupRepository;
use D3Werk\Gastgeber\Domain\Repository\FeatureRepository;
use D3Werk\Gastgeber\Domain\Repository\HostTypeRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class FilterController extends ActionController
{
    public function __construct(
        private readonly HostTypeRepository $hostTypeRepository,
        private readonly FeatureRepository $featureRepository,
        private readonly FeatureGroupRepository $featureGroupRepository,
        private readonly DistrictRepository $districtRepository,
    ) {}

    public function indexAction(): ResponseInterface
    {
        $features = $this->featureRepository->findAllIgnoringStorage();
        $featureGroups = $this->featureGroupRepository->findAllIgnoringStorage();

        $this->view->assignMultiple([
            'types' => $this->hostTypeRepository->findAllIgnoringStorage(),
            'features' => $features,
            'featureGroups' => $featureGroups,
            'filterFeatureGroups' => $this->buildFilterFeatureGroups($featureGroups, $features),
            'districts' => $this->districtRepository->findAllIgnoringStorage(),
            'settings' => $this->settings,
        ]);
        return $this->htmlResponse();
    }

    /**
     * Small fallback for the standalone filter plugin. The normal list plugin
     * builds the same grouped structure in HostController.
     *
     * @param iterable<object> $featureGroups
     * @param iterable<object> $features
     * @return array<int,array{uid:int,tableName:string,title:string,icon:mixed,iconClass:string,features:array<int,object>}>
     */
    private function buildFilterFeatureGroups(iterable $featureGroups, iterable $features): array
    {
        $groups = [];

        foreach ($featureGroups as $group) {
            if (!is_object($group) || !method_exists($group, 'getUid')) {
                continue;
            }
            $uid = (int)$group->getUid();
            if ($uid <= 0) {
                continue;
            }
            $groups[$uid] = [
                'uid' => $uid,
                'tableName' => 'tx_gastgeber_domain_model_featuregroup',
                'title' => method_exists($group, 'getTitle') ? (string)$group->getTitle() : '',
                'icon' => method_exists($group, 'getIcon') ? $group->getIcon() : null,
                'iconClass' => method_exists($group, 'getIconClass') ? (string)$group->getIconClass() : '',
                'features' => [],
            ];
        }

        foreach ($features as $feature) {
            if (!is_object($feature) || !method_exists($feature, 'getUid')) {
                continue;
            }

            // GASTGEBER_LIST_FILTER_FEATURES_FINAL_2026_06_09:
            // Auch im Standalone-Filter alle gepflegten Merkmale ausgeben.
            $group = method_exists($feature, 'getGroup') ? $feature->getGroup() : null;
            $groupUid = is_object($group) && method_exists($group, 'getUid') ? (int)$group->getUid() : 0;

            if ($groupUid <= 0) {
                $groupUid = 0;
                if (!isset($groups[$groupUid])) {
                    $groups[$groupUid] = [
                        'uid' => 0,
                        'tableName' => '',
                        'title' => 'Weitere Merkmale',
                        'icon' => null,
                        'iconClass' => '',
                        'features' => [],
                    ];
                }
            }

            if ($groupUid > 0 && !isset($groups[$groupUid])) {
                $groups[$groupUid] = [
                    'uid' => $groupUid,
                    'tableName' => 'tx_gastgeber_domain_model_featuregroup',
                    'title' => method_exists($group, 'getTitle') ? (string)$group->getTitle() : '',
                    'icon' => method_exists($group, 'getIcon') ? $group->getIcon() : null,
                    'iconClass' => method_exists($group, 'getIconClass') ? (string)$group->getIconClass() : '',
                    'features' => [],
                ];
            }

            $groups[$groupUid]['features'][] = $feature;
        }

        return array_values(array_filter(
            $groups,
            static fn (array $group): bool => !empty($group['features'])
        ));
    }
}
