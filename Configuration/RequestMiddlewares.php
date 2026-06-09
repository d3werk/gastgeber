<?php

declare(strict_types=1);

return [
    'frontend' => [
        'd3-werk/gastgeber/slug-routing' => [
            // GASTGEBER_ROUTE_PRESITE_REWRITE_FINAL_2026_06_09
            // Muss VOR typo3/cms-frontend/site laufen, damit der TYPO3 PageRouter
            // nicht bereits /gastgeber/{slug} als nicht existente Seite mit 404 verwirft.
            'target' => \D3Werk\Gastgeber\Middleware\SlugRoutingMiddleware::class,
            'before' => [
                'typo3/cms-frontend/site',
            ],
        ],
    ],
];
