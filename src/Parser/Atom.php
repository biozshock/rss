<?php

declare(strict_types=1);

namespace Biozshock\Rss\Parser;

use Biozshock\Rss\Model\Feed;
use Biozshock\Rss\Model\Record;
use DateTimeInterface;

class Atom extends AbstractXmlParser
{
    /**
     * @var array<string, string>
     */
    private static array $feedPropertiesMapping = [
        'title' => 'setTitle',
        'subtitle' => 'setDescription',
    ];

    /**
     * @var array<string, string>
     */
    private static array $propertiesMapping = [
        'title' => 'setTitle',
        'id' => 'setGuid',
    ];

    public function create(\DOMDocument $document, string $link): ?Feed
    {
        $nodes = $document->getElementsByTagName('entry');

        if (0 === $nodes->length || null === $feedElement = $document->getElementsByTagName('feed')->item(0)) {
            return null;
        }

        $feed = $this->extractFeed($feedElement);
        $feed->setSource($link);
        foreach ($nodes as $node) {
            try {
                $feed->addRecord($this->extract($node));
            } catch (\Exception $e) {
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

        if ((null !== $date = $this->getNodeValueByTagName($element, 'updated')) && false !== $publishedDate = \DateTime::createFromFormat(\DateTime::ATOM, $date)) {
            $feed->setPublishedDate($publishedDate);
        }

        $nodeList = $element->getElementsByTagName('link');
        foreach ($nodeList as $nodeResult) {
            /** @var \DOMElement $nodeResult */
            if ('alternate' === $nodeResult->getAttribute('rel')) {
                $feed->setLink($nodeResult->getAttribute('href'));
                break;
            }
        }

        if ('' !== $feed->getLink() && null !== $link = $this->getNodeValueByTagName($element, 'id')) {
            $feed->setLink($link);
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
        $content = $this->getNodeValueByTagName($node, 'content');
        if (null !== $content && '' !== $content) {
            $item->setContent($content);

            return;
        }

        $content = $this->getNodeValueByTagName($node, 'summary');
        if (null !== $content && '' !== $content) {
            $item->setContent($content);

            return;
        }

        $item->setContent('');
    }

    private function setLink(\DOMElement $node, Record $item): void
    {
        $nodeList = $node->getElementsByTagName('link');
        foreach ($nodeList as $nodeResult) {
            /** @var \DOMElement $nodeResult */
            if ('alternate' === $nodeResult->getAttribute('rel')) {
                $item->setLink($nodeResult->getAttribute('href'));
                break;
            }
        }
    }

    private function setAuthor(\DOMElement $node, Record $item): void
    {
        $nodeList = $node->getElementsByTagName('author')->item(0);

        if (null === $nodeList) {
            return;
        }

        foreach ($nodeList->childNodes as $nodeResult) {
            /** @var \DOMNode $nodeResult */
            if ('name' === $nodeResult->nodeName && null !== $nodeResult->nodeValue) {
                $item->setAuthor($nodeResult->nodeValue);
                break;
            }
        }
    }

    private function setDate(\DOMElement $node, Record $item): void
    {
        $date = $this->getNodeValueByTagName($node, 'published') ?? $this->getNodeValueByTagName($node, 'updated');
        if (null !== $date && false !== $publicationDate = \DateTime::createFromFormat(DateTimeInterface::ATOM, $date)) {
            $item->setPublicationDate($publicationDate);
        }
    }

    private function setTags(\DOMElement $node, Record $item): void
    {
        $tags = $node->getElementsByTagName('category');
        foreach ($tags as $tag) {
            /** @var \DOMElement $tag */
            if ('' !== $tag->getAttribute('term')) {
                $item->addTag('' !== $tag->getAttribute('label') ? $tag->getAttribute('label') : $tag->getAttribute('term'));
            }
        }
    }
}
