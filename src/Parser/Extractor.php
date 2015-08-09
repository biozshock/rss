<?php
/**
 * Created by PhpStorm.
 * User: bumz
 * Date: 8/9/15
 * Time: 2:13 PM
 */

namespace Biozshock\Rss\Parser;


class Extractor
{
    private $extractors;

    public function extract($text, $link)
    {
        $previousValue = libxml_use_internal_errors(true);
        $document = new \DomDocument();
        $document->recover=true;
        $document->loadXML($text);
        libxml_use_internal_errors($previousValue);

        if ($document->getElementsByTagName('rss')->length) {
            return $this->getRssParser()->create($document, $link);
        } elseif ($document->getElementsByTagName('feed')->length) {
            return $this->getAtomParser()->create($document, $link);
        }

        return [];
    }

    private function load()
    {
        if (count($this->extractors)) {
            return;
        }
        $this->extractors['rss'] = new Rss();
        $this->extractors['atom'] = new Atom();
    }

    /**
     * @return Rss
     */
    private function getRssParser()
    {
        $this->load();
        return $this->extractors['rss'];
    }

    /**
     * @return Atom
     */
    private function getAtomParser()
    {
        $this->load();
        return $this->extractors['atom'];
    }
}
