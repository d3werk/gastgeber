<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Removes leading standalone star/classification markers from RTE text.
 * Keeps the remaining HTML intact so RTE links continue to work.
 */
final class CleanRatingPrefixViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'HTML/text to clean.', false, '');
    }

    public function render(): string
    {
        $value = (string)($this->arguments['value'] ?: $this->renderChildren());
        if (trim($value) === '') {
            return '';
        }

        return $this->removeLeadingStars($value);
    }

    private function removeLeadingStars(string $value): string
    {
        $cleaned = $value;

        // Remove leading paragraphs/divs that only contain star symbols, e.g. <p>***</p>.
        $cleaned = (string)preg_replace(
            '~^\s*(?:(?:<p[^>]*>|<div[^>]*>)\s*(?:\*|★|☆|✱|✶|✷|✸|✹|✺|✦|✧|&\#9733;|&star;|&nbsp;|\s){1,20}\s*(?:</p>|</div>)\s*)+~iu',
            '',
            $cleaned
        );

        // Remove plain leading star markers in text/RTE teasers, e.g. "*** Das Vier Sterne Hotel...".
        $cleaned = (string)preg_replace(
            '~^\s*(?:\*|★|☆|✱|✶|✷|✸|✹|✺|✦|✧|&\#9733;|&star;|&nbsp;|\s){1,20}(?=(?:[A-ZÄÖÜa-zäöü0-9]|<))~u',
            '',
            $cleaned
        );

        // Remove empty first paragraph left behind by the cleanup.
        $cleaned = (string)preg_replace('~^\s*<p[^>]*>\s*</p>\s*~iu', '', $cleaned);

        return trim($cleaned);
    }
}
