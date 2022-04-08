<?php

declare(strict_types=1);

namespace Biozshock\Rss\Parser;

use Biozshock\Rss\Model\Feed;

abstract class AbstractXmlParser
{
    abstract public function create(\DOMDocument $document, string $link): ?Feed;

    protected function getNodeValueByTagName(\DOMElement $node, string $tagName): ?string
    {
        $results = $node->getElementsByTagName($tagName);
        for ($i = 0; $i < $results->length; ++$i) {
            $result = $results->item($i);
            if (null === $result || '' === $result->nodeValue) {
                continue;
            }

            return $result->nodeValue;
        }

        return null;
    }
}
