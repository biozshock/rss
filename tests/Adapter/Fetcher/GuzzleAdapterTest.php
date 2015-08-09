<?php
/**
 * Created by PhpStorm.
 * User: bumz
 * Date: 8/9/15
 * Time: 12:54 PM
 */

namespace Biozshock\Rss\Test\Adapter\Fetcher;

use \Biozshock\Rss\Adapter\Fetcher\GuzzleAdapter;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class GuzzleAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testFetch()
    {
        $extractorMock = $this->getMock('\Biozshock\Rss\Parser\Extractor');
        $extractorMock->expects(static::once())
            ->method('extract')
            ->will(static::returnValue('done'));

        $mock = new MockHandler([
            new Response(200, [], '<xml></xml>'),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $fetcher = new GuzzleAdapter($client, $extractorMock);

        static::assertEquals('done', $fetcher->fetch('url'));
    }
}
