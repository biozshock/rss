<?php
/**
 * Created by PhpStorm.
 * User: bumz
 * Date: 8/9/15
 * Time: 1:56 PM
 */

namespace Biozshock\Rss\Parser;

class AbstractXmlParser
{
    protected function getNodeValueByTagName(\DOMElement $node, $tagName)
    {
        $results = $node->getElementsByTagName($tagName);
        for ($i = 0; $i < $results->length; $i++) {
            $result = $results->item($i);
            if (!$result->nodeValue) {
                continue;
            }
            return $result->nodeValue;
        }
        return false;
    }
}
