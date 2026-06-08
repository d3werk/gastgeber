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
        $this->view->assignMultiple([
            'types' => $this->hostTypeRepository->findAllIgnoringStorage(),
            'features' => $this->featureRepository->findAllIgnoringStorage(),
            'featureGroups' => $this->featureGroupRepository->findAllIgnoringStorage(),
            'districts' => $this->districtRepository->findAllIgnoringStorage(),
            'settings' => $this->settings,
        ]);
        return $this->htmlResponse();
    }
}
