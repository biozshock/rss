<?php
/**
 * Created by PhpStorm.
 * User: bumz
 * Date: 8/9/15
 * Time: 8:05 PM
 */

namespace Biozshock\Rss\Test\Adapter\Store;

use Biozshock\Rss\Adapter\Store\PdoStore;
use Biozshock\Rss\Model\Feed;
use Biozshock\Rss\Model\Record;

class PdoStoreTest extends \PHPUnit_Framework_TestCase
{
    private $dsn = 'sqlite:/tmp/mydb.sq3';

    public function testSaveFeeds()
    {
        $store = new PdoStore($this->dsn, null, null);
        $date1 = new \DateTime('2015-01-01 00:00:00');
        $date2 = new \DateTime('-1 day');

        $feed = new Feed();
        $feed->setSource('http://site.tld');

        $record1 = new Record();
        $record1->setTitle('item 1');
        $record1->setPublicationDate($date1);
        $feed->addRecord($record1);

        $record2 = new Record();
        $record2->setTitle('item 2');
        $record2->setPublicationDate($date2);
        $feed->addRecord($record2);

        $store->save($feed);

        $loadedFeed = $store->loadFeed(1);
        static::assertEquals($feed->getSource(), $loadedFeed->getSource());

        $records = $store->loadItems($loadedFeed->getId());
        static::assertEquals($feed->getRecords()[0]->getTitle(), $records[0]->getTitle());
        static::assertEquals($feed->getRecords()[1]->getTitle(), $records[1]->getTitle());

        return $feed;
    }

    /**
     * @param Feed $feed
     * @depends testSaveFeeds
     */
    public function testSaveSameFeed(Feed $feed)
    {
        $store = new PdoStore($this->dsn, null, null);

        // we should search for feed
        $feed->setId(null);
        $record = new Record();
        $record->setTitle('item 2');
        $record->setPublicationDate(new \DateTime('-10 hours'));
        $feed->addRecord($record);

        $store->save($feed);

        $loadedFeed = $store->loadFeed(1);
        $records = $store->loadItems($loadedFeed->getId());

        static::assertEquals($record->getTitle(), $records[2]->getTitle());
    }

    public function setUp()
    {
        $sql = file_get_contents(__DIR__ . '/../../Fixtures/data.sql');
        $pdo = new \PDO($this->dsn);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec($sql);
    }
}
