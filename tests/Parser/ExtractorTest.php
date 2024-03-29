<?php

declare(strict_types=1);

namespace Biozshock\Rss\Tests\Parser;

use Biozshock\Rss\Parser\Extractor;
use PHPUnit\Framework\TestCase;

class ExtractorTest extends TestCase
{
    public function testExtractRss(): void
    {
        $document = file_get_contents(__DIR__ . '/../Fixtures/wellformed.xml');
        self::assertNotFalse($document);

        $extractor = new Extractor();
        $result = $extractor->extract($document, 'link');

        self::assertEquals('First item', $result->getRecords()[0]->getTitle());
    }

    public function testExtractRssBroken(): void
    {
        $document = file_get_contents(__DIR__ . '/../Fixtures/broken.xml');
        self::assertNotFalse($document);

        $extractor = new Extractor();
        $result = $extractor->extract($document, 'link');

        self::assertEquals('First item', $result->getRecords()[0]->getTitle());
        self::assertEquals('Second item', $result->getRecords()[1]->getTitle());
        self::assertEquals(['Programming'], $result->getRecords()[1]->getTags());
    }

    public function testExtractAtom(): void
    {
        $document = file_get_contents(__DIR__ . '/../Fixtures/atom.xml');
        $extractor = new Extractor();

        self::assertNotFalse($document);
        $result = $extractor->extract($document, 'link');

        self::assertEquals('use Perl; Shutting Down Indefinitely', $result->getRecords()[0]->getTitle());

        self::assertNotEmpty($result->getRecords()[0]->getContent());
        self::assertNotEmpty($result->getRecords()[1]->getContent());
    }
}
