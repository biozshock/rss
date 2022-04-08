<?php declare(strict_types=1);

namespace Biozshock\Rss\Model;

class Feed
{
    private ?int $id = null;

    private string $source;

    private string $link = '';

    private string $title = '';

    private string $description = '';

    private \DateTime $publishedDate;

    private \DateTime $lastFetched;

    private \DateTime $lastModified;

    /**
     * @var array<Record>
     */
    private array $records = [];

    public function __construct()
    {
        $this->publishedDate = new \DateTime();
        $this->lastFetched = new \DateTime();
        $this->lastModified = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getPublishedDate(): \DateTime
    {
        return $this->publishedDate;
    }

    public function setPublishedDate(\DateTime $publishedDate): void
    {
        $this->publishedDate = $publishedDate;
    }

    public function getLastFetched(): \DateTime
    {
        return $this->lastFetched;
    }

    public function setLastFetched(\DateTime $lastFetched): void
    {
        $this->lastFetched = $lastFetched;
    }

    public function getLastModified(): \DateTime
    {
        return $this->lastModified;
    }

    public function setLastModified(\DateTime $lastModified): void
    {
        $this->lastModified = $lastModified;
    }

    /**
     * @return array<Record>
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * @param array<Record> $records
     */
    public function setRecords(array $records): void
    {
        $this->records = $records;
    }

    public function addRecord(Record $record): void
    {
        $this->records[] = $record;
    }
}
