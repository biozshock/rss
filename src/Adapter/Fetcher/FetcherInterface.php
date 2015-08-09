<?php
/**
 * Created by PhpStorm.
 * User: bumz
 * Date: 8/9/15
 * Time: 12:20 PM
 */

namespace Biozshock\Rss\Adapter\Fetcher;

interface FetcherInterface
{
    public function fetch($url);
}
