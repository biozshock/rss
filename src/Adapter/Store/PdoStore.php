<?php declare(strict_types=1);

namespace Biozshock\Rss\Adapter\Store;

use Biozshock\Rss\Model\Feed;
use Biozshock\Rss\Model\Record;
use PDO;

class PdoStore implements StoreInterface
{
    private string $dsn;
    private string $username;
    private string $password;
    private ?PDO $pdo = null;

    public function __construct(string $dsn, string $username, string $password)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return array<Feed>
     */
    public function loadFeeds(): array
    {
        $this->init();
        $statement = $this->pdo->query('Select * from Feed');
        $cursor = $statement->fetchAll();

        $result = [];

        foreach ($cursor as $row) {
            $result[] = $this->hydrateFeed($row);
        }

        return $result;
    }

    public function loadFeed(int $id): ?Feed
    {
        $this->init();
        $statement = $this->pdo->prepare('Select * from Feed where id = ?');
        $statement->execute([$id]);
        $cursor = $statement->fetchAll();

        if (!$cursor) {
            return null;
        }

        return $this->hydrateFeed($cursor[0]);
    }

    public function loadFeedByUrl(string $url): ?Feed
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

    /**
     * @return array<Record>
     */
    public function loadItems(int $feedId): array
    {
        $this->init();
        $statement = $this->pdo->prepare('Select * from Record where feed_id = ?');
        $statement->execute([$feedId]);
        $cursor = $statement->fetchAll();

        $result = [];

        foreach ($cursor as $row) {
            $result[] = $this->hydrateRecord($row);
        }

        return $result;
    }

    public function loadItem(int $id): ?Record
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

    public function loadLastItem(int $feedId): ?Record
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

    public function save(Feed $feed): void
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

                if (false === $lastInsertId = $this->pdo->lastInsertId()) {
                    throw new \RuntimeException('Can not insert row.');
                }

                $feed->setId((int) $lastInsertId);
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
                        json_encode($record->getTags(), JSON_THROW_ON_ERROR),
                    ]);
                }
            }

            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
        }

    }

    protected function init(): PDO
    {
        if (null === $this->pdo) {
            $this->pdo = new \PDO($this->dsn, $this->username, $this->password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        return $this->pdo;
    }

    /**
     * @param array<mixed> $row
     */
    private function hydrateFeed(array $row): Feed
    {
        $feed = new Feed();
        $feed->setId((int) $row['id']);
        $feed->setSource($row['source']);
        $feed->setLink($row['link']);
        $feed->setDescription($row['description']);
        $feed->setTitle($row['title']);

        $feed->setPublishedDate(new \DateTime($row['published_date']));
        $feed->setLastFetched(new \DateTime($row['last_fetched']));
        $feed->setLastModified(new \DateTime($row['last_modified']));

        return $feed;
    }

    /**
     * @param array<mixed> $row
     */
    private function hydrateRecord(array $row): Record
    {
        $feed = new Record();
        $feed->setId((int) $row['id']);
        $feed->setTitle($row['title']);
        $feed->setContent($row['content']);
        $feed->setPicture($row['picture']);
        $feed->setAuthor($row['author']);
        $feed->setLink($row['link']);
        $feed->setGuid($row['guid']);
        $feed->setPublicationDate(new \DateTime($row['publication_date']));
        $feed->setTags(json_decode($row['tags'], true, 512, JSON_THROW_ON_ERROR));

        return $feed;
    }

    private function formatDateTime(\DateTime $time = null): ?string
    {
        return $time ? $time->setTimeZone(new \DateTimeZone(date_default_timezone_get()))->format('Y-m-d H:i:s') : null;
    }
}
