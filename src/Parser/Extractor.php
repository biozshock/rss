<?php

declare(strict_types=1);

namespace Biozshock\Rss\Parser;

use Biozshock\Rss\Model\Feed;

class Extractor
{
    /**
     * @var array<AbstractXmlParser>
     */
    private array $extractors = [];

    public function extract(string $text, string $link): ?Feed
    {
        $previousValue = libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $document->recover = true;
        $document->loadXML($text);
        libxml_use_internal_errors($previousValue);

        if ($document->getElementsByTagName('rss')->length) {
            return $this->getRssParser()->create($document, $link);
        }

        if ($document->getElementsByTagName('feed')->length) {
            return $this->getAtomParser()->create($document, $link);
        }

        return null;
    }

    private function load(): void
    {
        if (count($this->extractors)) {
            return;
        }
        $this->extractors['rss'] = new Rss();
        $this->extractors['atom'] = new Atom();
    }

    private function getRssParser(): Rss
    {
        $this->load();

        return $this->extractors['rss'];
    }

    private function getAtomParser(): Atom
    {
        $this->load();

        return $this->extractors['atom'];
    }
}
