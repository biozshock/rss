<?php
/**
 * Created by PhpStorm.
 * User: bumz
 * Date: 8/9/15
 * Time: 12:21 PM
 */

namespace Biozshock\Rss\Adapter\Store;

use Biozshock\Rss\Model\Feed;

interface StoreInterface
{
    public function loadFeeds();
    public function loadFeed($id);
    public function loadItems($feedId);
    public function loadItem($id);
    public function save(Feed $feed);
}
