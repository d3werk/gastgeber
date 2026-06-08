<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class ActiveFilterCountViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('filters', 'array', 'Current filters', true);
    }

    public function render(): int
    {
        $filters = (array)$this->arguments['filters'];
        $count = trim((string)($filters['search'] ?? '')) !== '' ? 1 : 0;
        foreach (['types', 'features', 'districts'] as $field) {
            $count += count(array_filter((array)($filters[$field] ?? [])));
        }
        return $count;
    }
}
