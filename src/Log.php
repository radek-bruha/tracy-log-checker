<?php declare(strict_types=1);

namespace Bruha\Tracy;

use DateTimeImmutable;

final class Log
{

    private DateTimeImmutable $timestamp;

    private string $type;

    private string $message;

    private string $path;

    private string $url;

    private string $file;

    private string $hash;

    /**
     * @var Log[]
     */
    private array $innerLogs = [];

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function setTimestamp(DateTimeImmutable $timestamp): Log
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Log
    {
        $this->type = $type;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): Log
    {
        $this->message = $message;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): Log
    {
        $this->path = $path;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): Log
    {
        $this->url = $url;

        return $this;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function setFile(string $file): Log
    {
        $this->file = $file;

        return $this;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): Log
    {
        $this->hash = $hash;

        return $this;
    }

    public function addInnerLog(Log $innerLog): Log
    {
        $this->innerLogs[] = $innerLog;

        return $this;
    }

    /**
     * @return Log[]
     */
    public function getInnerLogs(): array
    {
        return $this->innerLogs;
    }

}
