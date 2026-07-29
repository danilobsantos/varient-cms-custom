<?php

namespace App\Libraries;

require_once APPPATH . "ThirdParty/emogrifier/vendor/autoload.php";

use Pelago\Emogrifier\CssInliner;

/**
 * EmailProcessor Class
 * * This class handles the conversion of CSS styles from <style> blocks
 * into inline attributes for better email client compatibility.
 */
class EmailProcessor
{
    /**
     * Converts CSS rules in the provided HTML into inline styles
     *
     * * @param string $html The rendered HTML content (view output).
     * @return string The processed HTML with inline styles.
     */
    public function inline(string $html): string
    {
        // Return early if the HTML is empty
        if (empty(trim($html))) {
            return $html;
        }

        try {
            /**
             * CssInliner::fromHtml() parses the raw HTML string.
             * inlineCss() applies the CSS from <style> tags to matching HTML elements.
             * render() returns the resulting HTML as a string.
             */
            return CssInliner::fromHtml($html)
                ->inlineCss()
                ->render();
        } catch (\Exception $e) {
            return $html;
        }
    }
}