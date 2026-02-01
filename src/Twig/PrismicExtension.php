<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PrismicExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('as_text', [$this, 'asText']),
            new TwigFilter('as_html', [$this, 'asHtml']),
        ];
    }

    /**
     * Converts Prismic Rich Text or Title to plain text
     */
    public function asText($value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (!is_array($value)) {
            return '';
        }

        $text = '';
        foreach ($value as $block) {
            if (isset($block['text'])) {
                $text .= $block['text'] . ' ';
            }
        }

        return trim($text);
    }

    /**
     * Converts Prismic Rich Text to simple HTML
     * For a complete implementation, use Prismic's LinkResolver and HTMLSerializer
     */
    public function asHtml($value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (!is_array($value)) {
            return '';
        }

        $html = '';
        foreach ($value as $block) {
            $text = $block['text'] ?? '';
            $type = $block['type'] ?? 'paragraph';

            switch ($type) {
                case 'heading1':
                    $html .= "<h1>$text</h1>";
                    break;
                case 'heading2':
                    $html .= "<h2>$text</h2>";
                    break;
                case 'heading3':
                    $html .= "<h3>$text</h3>";
                    break;
                case 'paragraph':
                    $html .= "<p>$text</p>";
                    break;
                case 'list-item':
                    $html .= "<li>$text</li>";
                    break;
                default:
                    $html .= "<p>$text</p>";
                    break;
            }
        }

        return $html;
    }
}
