<?php

declare(strict_types=1);

namespace Biozshock\Rss\Parser;

use Biozshock\Rss\Model\Feed;
use Biozshock\Rss\Model\Record;

class Rss extends AbstractXmlParser
{
    /**
     * @var array<string, string>
     */
    private static array $feedPropertiesMapping = [
        'title' => 'setTitle',
        'description' => 'setDescription',
        'link' => 'setLink',
    ];

    /**
     * @var array<string, string>
     */
    private static array $propertiesMapping = [
        'title' => 'setTitle',
        'guid' => 'setGuid',
        'link' => 'setLink',
        'description' => 'setContent',
        'author' => 'setAuthor',
    ];

    public function create(\DOMDocument $document, string $link): ?Feed
    {
        $nodes = $document->getElementsByTagName('item');

        if (0 === $nodes->length || null === $feedNode = $document->getElementsByTagName('channel')->item(0)) {
            return null;
        }

        $feed = $this->extractFeed($feedNode);
        $feed->setSource($link);
        foreach ($nodes as $node) {
            try {
                $feed->addRecord($this->extract($node));
            } catch (\Throwable $e) {
                throw new \RuntimeException($e->getMessage());
            }
        }

        return $feed;
    }

    private function extractFeed(\DOMElement $element): Feed
    {
        $feed = new Feed();
        foreach (self::$feedPropertiesMapping as $nodeName => $methodName) {
            $feed->$methodName($this->getNodeValueByTagName($element, $nodeName));
        }

        if ((null !== $date = $this->getNodeValueByTagName($element, 'pubDate')) && false !== $publishedDate = \DateTime::createFromFormat(\DateTime::RSS, $date)) {
            $feed->setPublishedDate($publishedDate);
        }

        $feed->setLastFetched(new \DateTime());

        return $feed;
    }

    private function extract(\DOMElement $node): Record
    {
        $item = new Record();

        foreach (self::$propertiesMapping as $nodeName => $methodName) {
            $item->$methodName($this->getNodeValueByTagName($node, $nodeName));
        }

        if ((null !== $date = $this->getNodeValueByTagName($node, 'pubDate')) && false !== $publicationDate = \DateTime::createFromFormat(\DateTime::RSS, $date)) {
            $item->setPublicationDate($publicationDate);
        }

        $tags = $node->getElementsByTagName('category');
        if (0 === $tags->length) {
            return $item;
        }

        foreach ($tags as $tag) {
            /** @var \DOMElement $tag */
            if (null !== $tag->nodeValue && '' !== $tag->nodeValue) {
                $item->addTag($tag->nodeValue);
            }
        }

        return $item;
    }
}
