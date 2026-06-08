<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Gibt aus einem TYPO3-Typolink-Feld den sichtbaren Linkpfad zurück.
 *
 * Hintergrund: In der Detailansicht soll der Webseiten-Link nicht als
 * generischer Buttontext ausgegeben werden, sondern so, wie der Pfad im
 * Gastgeber-Datensatz gepflegt wurde.
 */
final class TypolinkPathViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = true;

    public function initializeArguments(): void
    {
        $this->registerArgument('parameter', 'string', 'TYPO3-Typolink-Parameter / Linkpfad', true);
    }

    public function render(): string
    {
        $parameter = trim((string)$this->arguments['parameter']);
        if ($parameter === '') {
            return '';
        }

        $parameter = html_entity_decode($parameter, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $parameter = trim($parameter);

        if ($parameter === '') {
            return '';
        }

        return $this->extractLinkPath($parameter);
    }

    private function extractLinkPath(string $parameter): string
    {
        $firstCharacter = $parameter[0] ?? '';

        if ($firstCharacter === '"' || $firstCharacter === "'") {
            $quote = $firstCharacter;
            $length = strlen($parameter);
            $path = '';

            for ($i = 1; $i < $length; $i++) {
                $character = $parameter[$i];
                if ($character === $quote) {
                    return trim($path);
                }
                $path .= $character;
            }

            return trim($path);
        }

        if (preg_match('/^([^\s]+)/u', $parameter, $matches) === 1) {
            return trim($matches[1], " \t\n\r\0\x0B\"'");
        }

        return trim($parameter, " \t\n\r\0\x0B\"'");
    }
}
