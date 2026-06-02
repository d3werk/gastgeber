<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class CurrentViewViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'Current value', true);
        $this->registerArgument('expected', 'string', 'Expected value', true);
        $this->registerArgument('then', 'string', 'Then', false, 'active');
        $this->registerArgument('else', 'string', 'Else', false, '');
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        return (string)$arguments['value'] === (string)$arguments['expected'] ? (string)$arguments['then'] : (string)$arguments['else'];
    }
}
