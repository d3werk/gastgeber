<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class ToggleFilterArgumentsViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('filters', 'array', 'Current filters', true);
        $this->registerArgument('field', 'string', 'Filter field to toggle', true);
        $this->registerArgument('value', 'int', 'Value to toggle', true);
        $this->registerArgument('view', 'string', 'Current view', false, 'cards');
        $this->registerArgument('sort', 'string', 'Current sort', false, '');
    }

    /** @return array<string,mixed> */
    public function render(): array
    {
        $filters = (array)$this->arguments['filters'];
        $field = (string)$this->arguments['field'];
        $value = (int)$this->arguments['value'];
        $args = [
            'view' => (string)$this->arguments['view'],
        ];

        foreach (['search', 'sort'] as $scalarField) {
            $v = trim((string)($filters[$scalarField] ?? ''));
            if ($v !== '') {
                $args[$scalarField] = $v;
            }
        }
        if ((string)$this->arguments['sort'] !== '') {
            $args['sort'] = (string)$this->arguments['sort'];
        }

        foreach (['types', 'features', 'districts'] as $listField) {
            $currentValues = array_values(array_unique(array_filter(array_map('intval', (array)($filters[$listField] ?? [])))));
            if ($listField === $field) {
                if (in_array($value, $currentValues, true)) {
                    $currentValues = array_values(array_diff($currentValues, [$value]));
                } else {
                    $currentValues[] = $value;
                }
            }
            if ($currentValues !== []) {
                $args[$listField] = $currentValues;
            }
        }

        return $args;
    }
}
