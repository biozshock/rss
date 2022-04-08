<?php
/**
 * Created by PhpStorm.
 * User: bumz
 * Date: 8/9/15
 * Time: 12:21 PM
 */

namespace Biozshock\Rss\Adapter\Store;

use Biozshock\Rss\Model\Feed;
use Biozshock\Rss\Model\Record;

interface StoreInterface
{
    /**
     * @return array<Feed>
     */
    public function loadFeeds(): array;
    public function loadFeed(int $id): ?Feed;
    
    /**
     * @return array<Record>
     */
    public function loadItems(int $feedId): array;
    public function loadItem(int $id): ?Record;
    public function save(Feed $feed): void;
}
