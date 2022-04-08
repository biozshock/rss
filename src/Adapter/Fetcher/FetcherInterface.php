<?php declare(strict_types=1);

namespace Biozshock\Rss\Adapter\Fetcher;

use Biozshock\Rss\Model\Feed;

interface FetcherInterface
{
    public function fetch(string $url): ?Feed;
}
