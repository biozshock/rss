<?php

declare(strict_types=1);

namespace Biozshock\Rss\Tests\Adapter\Fetcher;

use Biozshock\Rss\Adapter\Fetcher\GuzzleAdapter;
use Biozshock\Rss\Model\Feed;
use Biozshock\Rss\Parser\Extractor;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class GuzzleAdapterTest extends TestCase
{
    public function testFetch(): void
    {
        $extractorMock = $this->createMock(Extractor::class);
        $result = $this->createMock(Feed::class);
        $extractorMock->expects(self::once())
            ->method('extract')
            ->willReturn($result);

        $mock = new MockHandler([
            new Response(200, [], '<xml></xml>'),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $fetcher = new GuzzleAdapter($client, $extractorMock);

        self::assertSame($result, $fetcher->fetch('url'));
    }
}
