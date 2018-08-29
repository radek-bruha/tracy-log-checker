<?php declare(strict_types=1);

namespace Bruha\Tracy;

use DateTimeImmutable;

/**
 * Class Log
 *
 * @package Bruha\Tracy
 */
final class Log
{

    /**
     * @var DateTimeImmutable
     */
    private $timestamp;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $hash;

    /**
     * @var Log[]
     */
    private $innerLogs = [];

    /**
     * @return DateTimeImmutable
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * @param DateTimeImmutable $timestamp
     *
     * @return Log
     */
    public function setTimestamp(DateTimeImmutable $timestamp): Log
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Log
     */
    public function setType(string $type): Log
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return Log
     */
    public function setMessage(string $message): Log
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return Log
     */
    public function setPath(string $path): Log
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return Log
     */
    public function setUrl(string $url): Log
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param string $file
     *
     * @return Log
     */
    public function setFile(string $file): Log
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     *
     * @return Log
     */
    public function setHash(string $hash): Log
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @param Log $innerLog
     *
     * @return Log
     */
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