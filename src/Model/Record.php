<?php declare(strict_types=1);

namespace Biozshock\Rss\Model;

class Record
{
    private ?int $id = null;

    private Feed $feed;

    private string $title;

    private string $content = '';

    private string $picture = '';

    private \DateTime $publicationDate;

    private string $author = '';

    private string $link = '';

    private string $guid = '';

    /**
     * @var array<string>
     */
    private array $tags = [];

    public function __construct()
    {
        $this->publicationDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getFeed(): Feed
    {
        return $this->feed;
    }

    public function setFeed(Feed $feed): void
    {
        $this->feed = $feed;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getPicture(): string
    {
        return $this->picture;
    }

    public function setPicture(string $picture): void
    {
        $this->picture = $picture;
    }

    public function getPublicationDate(): \DateTime
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(\DateTime $publicationDate): void
    {
        $this->publicationDate = $publicationDate;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function setGuid(string $guid): void
    {
        $this->guid = $guid;
    }

    /**
     * @return array<string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array<string> $tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function addTag(string $tag): void
    {
        $this->tags[] = $tag;
    }
}
