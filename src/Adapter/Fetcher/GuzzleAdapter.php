<?php declare(strict_types=1);

namespace Biozshock\Rss\Adapter\Fetcher;

use Biozshock\Rss\Model\Feed;
use Biozshock\Rss\Parser\Extractor;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class GuzzleAdapter implements FetcherInterface
{
    private Client $client;

    private Extractor $extractor;

    public function __construct(Client $client, Extractor $extractor)
    {
        $this->client = $client;
        $this->extractor = $extractor;
    }

    public function fetch(string $url): ?Feed
    {
        return $this->extract($this->client->get($url), $url);
    }

    private function extract(ResponseInterface $response, string $url): ?Feed
    {
        if ($response->getStatusCode() === 200) {
            return $this->extractor->extract((string) $response->getBody(), $url);
        }

        return null;
    }
}
