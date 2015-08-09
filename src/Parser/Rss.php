<?php
/**
 * Created by PhpStorm.
 * User: bumz
 * Date: 8/9/15
 * Time: 1:46 PM
 */

namespace Biozshock\Rss\Parser;

use Biozshock\Rss\Model\Feed;
use Biozshock\Rss\Model\Record;

class Rss extends AbstractXmlParser
{
    private static $feedPropertiesMapping = array(
        'title' => 'setTitle',
        'description' => 'setDescription',
        'link' => 'setLink',
    );

    private static $propertiesMapping = array(
        'title' => 'setTitle',
        'guid'  => 'setGuid',
        'link'  => 'setLink',
        'description' => 'setContent',
        'author' => 'setAuthor'
    );

    public function create(\DOMDocument $document, $link)
    {
        $feed = null;
        $nodes = $document->getElementsByTagName('item');
        if ($nodes->length) {
            $feed = $this->extractFeed($document->getElementsByTagName('channel')->item(0));
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

    private function extractFeed(\DOMElement $element)
    {
        $feed = new Feed();
        foreach (self::$feedPropertiesMapping as $nodeName => $methodName) {
            $feed->$methodName($this->getNodeValueByTagName($element, $nodeName));
        }

        if ($date = $this->getNodeValueByTagName($element, 'pubDate')) {
            $feed->setPublishedDate(\DateTime::createFromFormat(\DateTime::RSS, $date));
        }

        $feed->setLastFetched(new \DateTime());

        return $feed;
    }

    private function extract(\DOMElement $node)
    {
        $item = new Record();

        foreach (self::$propertiesMapping as $nodeName => $methodName) {
            $item->$methodName($this->getNodeValueByTagName($node, $nodeName));
        }

        if ($date = $this->getNodeValueByTagName($node, 'pubDate')) {
            $item->setPublicationDate(\DateTime::createFromFormat(\DateTime::RSS, $date));
        }

        $tags = $node->getElementsByTagName('category');
        if ($tags->length) {
            foreach ($tags as $tag) {
                /** @var \DomElement $tag */
                if ($tag->nodeValue) {
                    $item->addTag($tag->nodeValue);
                }
            }
        }

        return $item;
    }
}
