<?php
/**
 * Created by PhpStorm.
 * User: bumz
 * Date: 8/9/15
 * Time: 12:28 PM
 */

namespace Biozshock\Rss\Adapter\Fetcher;

use Biozshock\Rss\Parser\Extractor;

class GuzzleAdapter implements FetcherInterface
{
    private $client;

    private $extractor;

    public function __construct(\GuzzleHttp\Client $client, Extractor $extractor)
    {
        $this->client = $client;
        $this->extractor = $extractor;
    }

    public function fetch($url)
    {
        return $this->extract($this->client->get($url), $url);
    }

    private function extract(\Psr\Http\Message\ResponseInterface $response, $url)
    {
        if ($response->getStatusCode() === 200) {
            return $this->extractor->extract($response->getBody(), $url);
        }

        return null;
    }
}
