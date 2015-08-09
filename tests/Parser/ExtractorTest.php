<?php
/**
 * Created by PhpStorm.
 * User: bumz
 * Date: 8/9/15
 * Time: 2:29 PM
 */

namespace Biozshock\Rss\Test\Parser;

class ExtractorTest extends \PHPUnit_Framework_TestCase
{
    public function testExtractRss()
    {
        $document = file_get_contents(__DIR__ . '/../Fixtures/wellformed.xml');

        $extractor = new \Biozshock\Rss\Parser\Extractor();
        $result = $extractor->extract($document, 'link');

        static::assertEquals('First item', $result->getRecords()[0]->getTitle());
    }

    public function testExtractRssBroken()
    {
        $document = file_get_contents(__DIR__ . '/../Fixtures/broken.xml');

        $extractor = new \Biozshock\Rss\Parser\Extractor();
        $result = $extractor->extract($document, 'link');

        static::assertEquals('First item', $result->getRecords()[0]->getTitle());
        static::assertEquals('Second item', $result->getRecords()[1]->getTitle());
        static::assertEquals(['Programming'], $result->getRecords()[1]->getTags());
    }

    public function testExtractAtom()
    {
        $document = file_get_contents(__DIR__ . '/../Fixtures/atom.xml');

        $extractor = new \Biozshock\Rss\Parser\Extractor();
        $result = $extractor->extract($document, 'link');

        static::assertEquals('use Perl; Shutting Down Indefinitely', $result->getRecords()[0]->getTitle());

        static::assertNotEmpty($result->getRecords()[0]->getContent());
        static::assertNotEmpty($result->getRecords()[1]->getContent());
    }
}
