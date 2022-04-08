<?php

declare(strict_types=1);

namespace Biozshock\Rss\Parser;

use Biozshock\Rss\Model\Feed;
use Biozshock\Rss\Model\Record;
use DateTimeInterface;

class Atom extends AbstractXmlParser
{
    private static array $feedPropertiesMapping = [
        'title' => 'setTitle',
        'subtitle' => 'setDescription',
    ];

    private static array $propertiesMapping = [
        'title' => 'setTitle',
        'id' => 'setGuid',
    ];

    public function create(\DOMDocument $document, string $link): ?Feed
    {
        $feed = null;
        $nodes = $document->getElementsByTagName('entry');
        if ($nodes->length) {
            $feed = $this->extractFeed($document->getElementsByTagName('feed')->item(0));
            $feed->setSource($link);
            foreach ($nodes as $node) {
                try {
                    $feed->addRecord($this->extract($node));
                } catch (\Exception $e) {
                    throw new \RuntimeException($e->getMessage());
                }
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

        if ($date = $this->getNodeValueByTagName($element, 'updated')) {
            $feed->setPublishedDate(\DateTime::createFromFormat(\DateTime::ATOM, $date));
        }

        $nodeList = $element->getElementsByTagName('link');
        foreach ($nodeList as $nodeResult) {
            /** @var \DomElement $nodeResult */
            if ('alternate' === $nodeResult->getAttribute('rel')) {
                $feed->setLink($nodeResult->getAttribute('href'));
                break;
            }
        }

        if (!$feed->getLink()) {
            $feed->setLink($this->getNodeValueByTagName($element, 'id'));
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

        $this->setContent($node, $item);
        $this->setLink($node, $item);
        $this->setAuthor($node, $item);
        $this->setDate($node, $item);
        $this->setTags($node, $item);

        return $item;
    }

    private function setContent(\DOMElement $node, Record $item): void
    {
        $item->setContent(
            $this->getNodeValueByTagName($node, 'content')
                ?: $this->getNodeValueByTagName($node, 'summary')
        );
    }

    private function setLink(\DOMElement $node, Record $item): void
    {
        $nodeList = $node->getElementsByTagName('link');
        foreach ($nodeList as $nodeResult) {
            /** @var \DomElement $nodeResult */
            if ('alternate' === $nodeResult->getAttribute('rel')) {
                $item->setLink($nodeResult->getAttribute('href'));
                break;
            }
        }
    }

    private function setAuthor(\DOMElement $node, Record $item): void
    {
        $nodeList = $node->getElementsByTagName('author');
        foreach ($nodeList->item(0)->childNodes as $nodeResult) {
            if ('name' === $nodeResult->nodeName) {
                $item->setAuthor($nodeResult->nodeValue);
                break;
            }
        }
    }

    private function setDate(\DOMElement $node, Record $item): void
    {
        $date = $this->getNodeValueByTagName($node, 'published') ?? $this->getNodeValueByTagName($node, 'updated');
        if ($date && strtotime($date)) {
            $item->setPublicationDate(\DateTime::createFromFormat(DateTimeInterface::ATOM, $date));
        }
    }

    private function setTags(\DOMElement $node, Record $item): void
    {
        $tags = $node->getElementsByTagName('category');
        foreach ($tags as $tag) {
            /** @var \DomElement $tag */
            if ($tag->getAttribute('term')) {
                $item->addTag($tag->getAttribute('label') ?: $tag->getAttribute('term'));
            }
        }
    }
}
