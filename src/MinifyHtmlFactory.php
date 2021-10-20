<?php

declare(strict_types=1);

namespace BuzzingPixel\Minify;

use Minify_HTML;

class MinifyHtmlFactory
{
    /**
     * @see Minify_HTML::__construct()
     *
     * @param mixed[]|null $options
     */
    public function make(
        string $html,
        ?array $options = null,
    ): Minify_HTML {
        if ($options === null) {
            $options = [
                'cssMinifier' => '\Minify_CSSmin::minify',
                'jsMinifier' => '\JSMin\JSMin::minify',
            ];
        }

        return new Minify_HTML($html, $options);
    }
}
