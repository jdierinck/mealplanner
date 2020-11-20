<?php

namespace AppBundle\RecipeParser;

class Text {

    /**
     * Cleanup for clipped HTML prior to parsing with RecipeParser.
     *
     * @param string HTML
     * @return string HTML
     */
    static public function cleanupHtml($html) {
    	$html = trim($html);
        // $html = preg_replace('/(\r\n|\r)/', "\n", $html);            // Normalize line breaks
        $html = preg_replace('/(\n|\r|\r\n)/', '', $html);            // remove line breaks

        // $html = preg_replace('/\s+/', ' ', $html);   // squash multi-spaces

        $html = str_replace('&nbsp;', ' ', $html);                   // get rid of non-breaking space (html code)
        $html = str_replace('&#160;', ' ', $html);                   // get rid of non-breaking space (numeric)
        $html = preg_replace('/\xC2\xA0/', ' ', $html);              // get rid of non-breaking space (UTF-8)
        $html = preg_replace('/[\x{0096}-\x{0097}]/u', '-', $html);  // ndash, mdash

        // Strip out script tags so they don't accidentally get executed if we ever display
        // clipped content to end-users.
        // $html = \RecipeParser\Text::stripTagAndContents('script', $html);
        // $html = \RecipeParser\Text::stripConditionalComments($html);

        return $html;
    }

    /**
     * Remove whitespace and convert html entities
     *
     * @param string
     * @return string
     */
    public static function formatLine($str) {
        $str = preg_replace('/\s+/', ' ', $str);   // squash multi-spaces
        $str = trim($str);
        $str = html_entity_decode($str);
        $str = strip_tags($str);
        return $str;
    }

    /**
     * Normalize paragraphs
     *
     * @param string
     * @return string
     */
    public static function formatParagraph($str) {
        // $str = str_replace("\r", "", $str);

        $str = preg_replace('/(\r\n|\r)/', "\n", $str); // Normalize line breaks
        $str = str_replace('&nbsp;', ' ', $str);       // get rid of non-breaking space (html code)
        $str = preg_replace("/\s+/", " ", $str);      // squash multiple whitespaces
        $str = trim($str);
        $str = html_entity_decode($str);
        return $str;
    }

    /**
     * Get lowest value from a recipeYield string
	 *
     * @param string
     * @return string | void
     */
    public static function getYield($str) {
    	$str = self::formatLine($str);
    	$arr = explode(' ', $str);
    	$result = array_filter($arr, function($v){ // filter out words
    		return preg_match('/\d+/', $v);
    	});
    	if (!empty($result)) {
    		array_walk($result, function(&$val){
    			if (preg_match('/^\d-\d$/', $val)) { // e.g. '2-4'
    				$val = substr($val, 0, 1);
    			}
    		});
	    	sort($result); // note: will also re-index arrray
	    	return $result[0];
    	}
    	return NULL;
    }

	public static function fractionToDecimal($fraction) {
	    // Split fraction into whole number and fraction components
	    preg_match('/^(?P<whole>\d+)?\s?((?P<numerator>\d+)\/(?P<denominator>\d+))?$/', $fraction, $components);

	    // Extract whole number, numerator, and denominator components
	    $whole = $components['whole'] ?: 0;
	    $numerator = $components['numerator'] ?: 0;
	    $denominator = $components['denominator'] ?: 0;

	    // Create decimal value
	    $decimal = $whole;
	    $numerator && $denominator && $decimal += round($numerator/$denominator, 2);

	    return $decimal;
	}

	public static function utf8FractionToFloat($symbol)
	{
	    $translit = trim(iconv('UTF-8', 'ASCII//TRANSLIT', $symbol));

	    if ($translit && preg_match('~^(\d+)/(\d+)$~', $translit, $match)) {
	        return $match[1] / $match[2];
	    }
	}

    /**
     * Check if JSON can be fixed by removing single quotes
     */
    public static function fixJSON($json) {
        $json = preg_replace('/\'(?!s)/','"', $json); // replace single quotes with double quotes except those followed by an s (plurals)
        if (json_decode($json) !== NULL) {
            return $json;
        }
    }

}