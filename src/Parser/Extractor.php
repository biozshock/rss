<?php

declare(strict_types=1);

namespace Biozshock\Rss\Parser;

use Biozshock\Rss\Model\Feed;

class Extractor
{
    private ?Rss $rss = null;
    private ?Atom $atom = null;

    public function extract(string $text, string $link): ?Feed
    {
        $previousValue = libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $document->recover = true;
        $document->loadXML($text);
        libxml_use_internal_errors($previousValue);

        if (0 !== $document->getElementsByTagName('rss')->length) {
            return $this->getRssParser()->create($document, $link);
        }

        if (0 !== $document->getElementsByTagName('feed')->length) {
            return $this->getAtomParser()->create($document, $link);
        }

        return null;
    }

    private function getRssParser(): Rss
    {
        if (null === $this->rss) {
            $this->rss = new Rss();
        }

        return $this->rss;
    }

    private function getAtomParser(): Atom
    {
        if (null === $this->atom) {
            $this->atom = new Atom();
        }

        return $this->atom;
    }
}
