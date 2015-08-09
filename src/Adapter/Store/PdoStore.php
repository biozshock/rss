<?php
/**
 * Created by PhpStorm.
 * User: bumz
 * Date: 8/9/15
 * Time: 5:36 PM
 */

namespace Biozshock\Rss\Adapter\Store;

use Biozshock\Rss\Model\Feed;
use Biozshock\Rss\Model\Record;

class PdoStore implements StoreInterface
{
    private $dsn;
    private $username;
    private $password;

    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct($dsn, $username, $password)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
    }

    public function loadFeeds()
    {
        $this->init();
        $statement = $this->pdo->prepare('Select * from Feed');
        $statement->execute();
        $cursor = $statement->fetchAll();

        $result = [];

        foreach ($cursor as $row) {
            $result[] = $this->hydrateFeed($row);
        }

        return $result;
    }

    public function loadFeed($id)
    {
        $this->init();
        $statement = $this->pdo->prepare('Select * from Feed where id = ?');
        $statement->execute(array($id));
        $cursor = $statement->fetchAll();

        if (!$cursor) {
            return null;
        }

        return $this->hydrateFeed($cursor[0]);
    }

    public function loadFeedByUrl($url)
    {
        $this->init();
        $statement = $this->pdo->prepare('Select * from Feed where source = ?');
        $statement->execute(array($url));
        $cursor = $statement->fetchAll();

        if (!$cursor) {
            return null;
        }

        return $this->hydrateFeed($cursor[0]);
    }

    public function loadItems($feedId)
    {
        $this->init();
        $statement = $this->pdo->prepare('Select * from Record where feed_id = ?');
        $statement->execute(array($feedId));
        $cursor = $statement->fetchAll();

        $result = [];

        foreach ($cursor as $row) {
            $result[] = $this->hydrateRecord($row);
        }

        return $result;
    }

    public function loadItem($id)
    {
        $this->init();
        $statement = $this->pdo->prepare('Select * from Record where id = ?');
        $statement->execute(array($id));
        $cursor = $statement->fetchAll();

        if (!$cursor) {
            return null;
        }

        return $this->hydrateRecord($cursor[0]);
    }

    public function loadLastItem($feedId)
    {
        $this->init();
        $statement = $this->pdo->prepare('Select * from Record where feed_id = ? ORDER BY id DESC LIMIT 1');
        $statement->execute(array($feedId));
        $cursor = $statement->fetchAll();

        if (!$cursor) {
            return null;
        }

        return $this->hydrateRecord($cursor[0]);
    }

    public function save(Feed $feed)
    {
        $this->init();
        $updateFeed = true;
        // if there are no such feed yet.
        if (!$feed->getId()) {
            if (!($loadedFeed = $this->loadFeedByUrl($feed->getSource()))) {
                $updateFeed = false;
                // sure if there are only mysql it's much more efficient to make through sql variables :)
                $statement = $this->pdo->prepare('insert into Feed (`id`, `source`, `link`, `description`, `title`, ' .
                    '`published_date`,`last_fetched`, `last_modified`) values (null, ?, ?, ?, ?, ?, ?, ?)');

                $statement->execute([
                    $feed->getSource(),
                    $feed->getLink(),
                    $feed->getDescription(),
                    $feed->getTitle(),
                    $this->formatDateTime($feed->getPublishedDate()),
                    $this->formatDateTime($feed->getLastFetched()),
                    $this->formatDateTime($feed->getLastModified()),
                ]);

                $feed->setId($this->pdo->lastInsertId());
            } else {
                $feed->setId($loadedFeed->getId());
            }
        }

        $this->pdo->beginTransaction();

        try {

            if ($updateFeed) {

                $statement = $this->pdo->prepare('UPDATE Feed SET `link` = ?, `description` = ?, `title` = ?, ' .
                        '`published_date` = ?, `last_fetched` = ?, `last_modified` = ? WHERE id = ?');

                $statement->execute([
                    $feed->getLink(),
                    $feed->getDescription(),
                    $feed->getTitle(),
                    $this->formatDateTime($feed->getPublishedDate()),
                    $this->formatDateTime($feed->getLastFetched()),
                    $this->formatDateTime($feed->getLastModified()),
                    $feed->getId(),
                ]);

            }

            //TODO: collision with same pubdates
            $lastRecord = $this->loadLastItem($feed->getId());
            $statement = $this->pdo->prepare('insert into Record (`id`, `feed_id`, `title`, `content`, `picture`, ' .
                        '`author`, `link`, `guid`, `publication_date`, `tags`) values (null, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

            foreach ($feed->getRecords() as $record) {
                /** @var Record $record */

                if (!$lastRecord || $record->getPublicationDate() > $lastRecord->getPublicationDate()) {
                    $statement->execute([
                        $feed->getId(),
                        $record->getTitle(),
                        $record->getContent(),
                        $record->getPicture(),
                        $record->getAuthor(),
                        $record->getLink(),
                        $record->getGuid(),
                        $this->formatDateTime($record->getPublicationDate()),
                        json_encode($record->getTags()),
                    ]);
                }
            }

            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
        }

    }

    /**
     * @return \PDO
     */
    protected function init()
    {
        if (!$this->pdo) {
            $this->pdo = new \PDO($this->dsn, $this->username, $this->password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        return $this->pdo;
    }

    private function hydrateFeed(array $row)
    {
        $feed = new Feed();
        $feed->setId($row['id']);
        $feed->setSource($row['source']);
        $feed->setLink($row['link']);
        $feed->setDescription($row['description']);
        $feed->setTitle($row['title']);

        $feed->setPublishedDate(new \DateTime($row['published_date']));
        $feed->setLastFetched(new \DateTime($row['last_fetched']));
        $feed->setLastModified(new \DateTime($row['last_modified']));

        return $feed;
    }

    private function hydrateRecord(array $row)
    {
        $feed = new Record();
        $feed->setId($row['id']);
        $feed->setTitle($row['title']);
        $feed->setContent($row['content']);
        $feed->setPicture($row['picture']);
        $feed->setAuthor($row['author']);
        $feed->setLink($row['link']);
        $feed->setGuid($row['guid']);
        $feed->setPublicationDate(new \DateTime($row['publication_date']));
        $feed->setTags(json_decode($row['tags'], true));

        return $feed;
    }

    /**
     * @param \DateTime $time
     * @return string
     */
    private function formatDateTime(\DateTime $time = null)
    {
        return $time ? $time->setTimeZone(new \DateTimeZone(date_default_timezone_get()))->format('Y-m-d H:i:s') : null;
    }
}
