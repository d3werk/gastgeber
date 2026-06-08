<?php

declare(strict_types=1);

return [
    'frontend' => [
        'd3-werk/gastgeber/slug-routing' => [
            'target' => \D3Werk\Gastgeber\Middleware\SlugRoutingMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];
