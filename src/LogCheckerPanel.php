<?php declare(strict_types=1);

namespace Bruha\Tracy;

use DateTimeImmutable;
use SplFileInfo;
use Tracy\Debugger;
use Tracy\IBarPanel;

/**
 * Class LogCheckerPanel
 *
 * @package Bruha\Tracy
 */
final class LogCheckerPanel implements IBarPanel
{

    public const ORDER_BY_TIMESTAMP   = 'ORDER_BY_TIMESTAMP';
    public const ORDER_BY_OCCURRENCES = 'ORDER_BY_OCCURRENCES';

    private const SUCCESS = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAPCAMAAAAMCGV4AAAAflBMVEUAAAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAAAiAASFJtHAAAAKXRSTlMAAQMEBQ0QERMXLC8wMTQ1OkNHTk9SWV9hYmdwkZilvMjP2t7v8/n7/Y4B51wAAABmSURBVAgdbcEHEoIwAEXBpyhiV7A3sBDy739BQ0JmGMddvPRK37Te0zN46wZropmkDeWczklOUX+GBBcFC7xsqeBIa/RMS3mHMc5E0bnCSayiF62dohV/3OVZ2xizBR6mserk/PoCRMkPd/99Fe4AAAAASUVORK5CYII=';
    private const ERROR   = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAPCAMAAAAMCGV4AAAAflBMVEUAAAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD/AAD7v5q5AAAAKXRSTlMAAQMEBQ0QERMXLC8wMTQ1OkNHTk9SWV9hYmdwkZilvMjP2t7v8/n7/Y4B51wAAABmSURBVAgdbcEHEoIwAEXBpyhiV7A3sBDy739BQ0JmGMddvPRK37Te0zN46wZropmkDeWczklOUX+GBBcFC7xsqeBIa/RMS3mHMc5E0bnCSayiF62dohV/3OVZ2xizBR6mserk/PoCRMkPd/99Fe4AAAAASUVORK5CYII=';
    private const LABEL   = '<span title="Log Checker"><img src="%s" style="padding-left: 2px; margin-top: 5px;"><span class="tracy-label"></span></span>';
    private const CACHE   = 'LogChecker.cache';

    private const LOGS_REGEX = '#\[(.+?)\] (.+?): (.+?) in ((?:\S+?):(?:\d+))(?:.+?)  @  (.+?)  @@  (.+)#';
    private const LOG_REGEX  = '#exception--\d{4}-\d{2}-\d{2}--\d{2}-\d{2}--\S{10}\.html#';

    /**
     * @var Log[]
     */
    private $logs = [];

    /**
     * @var int
     */
    private $logsCount = 0;

    /**
     * @var int
     */
    private $occurrencesCount = 0;

    /**
     * @var string
     */
    private $cache = '../temp/cache';

    /**
     * @var string
     */
    private $order = self::ORDER_BY_TIMESTAMP;

    /**
     * @var array
     */
    private $excludedTypes = [];

    /**
     * @var array
     */
    private $excludedMessages = [];

    /**
     * @var array
     */
    private $excludedPaths = [];

    /**
     * @var array
     */
    private $excludedUrls = [];

    /**
     * LogCheckerPanel constructor
     *
     * @param string $cache
     * @param string $order
     * @param array  $excludedTypes
     * @param array  $excludedMessages
     * @param array  $excludedPaths
     * @param array  $excludedUrls
     */
    public function __construct(
        string $cache = '../temp/cache',
        string $order = self::ORDER_BY_TIMESTAMP,
        array $excludedTypes = [],
        array $excludedMessages = [],
        array $excludedPaths = [],
        array $excludedUrls = []
    ) {
        $cache = sprintf('%s/%s', Debugger::$logDirectory, $cache);

        if (!file_exists($cache)) {
            mkdir($cache, 0777, TRUE);
        }

        $this->cache            = realpath($cache) ?: $cache;
        $this->order            = $order;
        $this->excludedTypes    = $excludedTypes;
        $this->excludedMessages = $excludedMessages;
        $this->excludedPaths    = $excludedPaths;
        $this->excludedUrls     = $excludedUrls;

        $url      = self::getCurrentUrl();
        $urlQuery = parse_url($url, PHP_URL_QUERY);

        if (is_string($urlQuery)) {
            parse_str($urlQuery, $result);

            if (isset($result['log-checker-file-select'])) {
                $this->handleFileSelect($result['log-checker-file-select']);
            }

            if (isset($result['log-checker-file-delete'])) {
                $this->handleFileDelete($result['log-checker-file-delete']);
            }

            if (isset($result['log-checker-directory-delete'])) {
                $this->handleDirectoryDelete();
            }
        }

        $this->processLogs();
    }

    /**
     * @return string
     */
    public function getTab(): string
    {
        return sprintf(self::LABEL, $this->logs !== [] ? self::ERROR : self::SUCCESS);
    }

    /**
     * @return string
     */
    public function getPanel(): string
    {
        ob_start();

        $logs             = $this->logs;
        $logsCount        = $this->logsCount;
        $occurrencesCount = $this->occurrencesCount;

        $logs;
        $logsCount;
        $occurrencesCount;

        include __DIR__ . '/LogCheckerPanel.phtml';

        return ob_get_clean() ?: '';
    }

    /**
     *
     */
    private function processLogs(): void
    {
        /** @var array $logFiles */
        $logFiles = glob(sprintf('%s/*.log', Debugger::$logDirectory));
        $logSizes = array_sum(
            array_map(
                static function (string $file): int {
                    return (new SplFileInfo($file))->getSize();
                },
                $logFiles
            )
        );

        $cache = sprintf('%s/%s', $this->cache, self::CACHE);

        if (file_exists($cache)) {
            $cache = unserialize(file_get_contents($cache) ?: '');

            if ($cache !== FALSE && $cache['size'] === $logSizes) {
                $this->logs             = $cache['logs'];
                $this->logsCount        = $cache['logsCount'];
                $this->occurrencesCount = $cache['occurrencesCount'];

                return;
            }
        }

        foreach ($logFiles as $logFile) {
            $logFile = new SplFileInfo($logFile);
            $logPath = $logFile->getRealPath();

            if (is_string($logPath)) {
                $logLines = array_reverse(file($logPath) ?: []);

                foreach ($logLines as $logLine) {
                    preg_match(self::LOGS_REGEX, $logLine, $matches);

                    if (!$matches || count($matches) !== 7) {
                        continue;
                    }

                    $logFile = new SplFileInfo(sprintf('%s/%s', Debugger::$logDirectory, $matches[6]));

                    if (!is_string($logFile->getRealPath())) {
                        continue;
                    }

                    $this->logsCount += 1;

                    if ($this->isSatisfying($logLine)) {
                        $this->occurrencesCount += 1;

                        $log = (new Log())
                            ->setTimestamp(
                                DateTimeImmutable::createFromFormat(
                                    'Y-m-d H-i-s',
                                    $matches[1]
                                ) ?: new DateTimeImmutable()
                            )
                            ->setType($matches[2])
                            ->setMessage($matches[3])
                            ->setPath($matches[4])
                            ->setUrl($matches[5])
                            ->setFile($logFile->getFilename())
                            ->setHash(md5(sprintf('%s %s %s', $matches[2], $matches[3], $matches[6])));

                        if (isset($this->logs[$log->getHash()])) {
                            $this->logs[$log->getHash()]->addInnerLog($log);
                        } else {
                            $this->logs[$log->getHash()] = $log->addInnerLog($log);
                        }
                    }
                }
            }
        }

        usort(
            $this->logs,
            function (Log $one, Log $two): int {
                if ($this->order === self::ORDER_BY_TIMESTAMP) {
                    return $two->getTimestamp() <=> $one->getTimestamp();
                }

                if (count($one->getInnerLogs()) !== count($two->getInnerLogs())) {
                    return $two->getInnerLogs() <=> $one->getInnerLogs();
                }

                return $two->getTimestamp() <=> $one->getTimestamp();
            }
        );

        file_put_contents(
            sprintf('%s/%s', $this->cache, self::CACHE),
            serialize(
                [
                    'size'             => $logSizes,
                    'logs'             => $this->logs,
                    'logsCount'        => $this->logsCount,
                    'occurrencesCount' => $this->occurrencesCount,
                ]
            )
        );
    }

    /**
     * @param string $content
     *
     * @return bool
     */
    private function isSatisfying(string $content): bool
    {
        foreach ($this->excludedTypes as $excludedType) {
            if (preg_match($excludedType, $content) === 1) {
                return FALSE;
            }
        }

        foreach ($this->excludedMessages as $excludedMessage) {
            if (preg_match($excludedMessage, $content) === 1) {
                return FALSE;
            }
        }

        foreach ($this->excludedPaths as $excludedPath) {
            if (preg_match($excludedPath, $content) === 1) {
                return FALSE;
            }
        }

        foreach ($this->excludedUrls as $excludedUrl) {
            if (preg_match($excludedUrl, $content) === 1) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * @param string $file
     */
    private function handleFileSelect(string $file): void
    {
        $file = sprintf('%s/%s', Debugger::$logDirectory, $file);

        if (file_exists($file) && preg_match(self::LOG_REGEX, $file) === 1) {
            echo file_get_contents($file);
        }
    }

    /**
     * @param string $file
     */
    private function handleFileDelete(string $file): void
    {
        /** @var array $logFiles */
        $logFiles = glob(sprintf('%s/*.log', Debugger::$logDirectory));

        foreach ($logFiles as $logFile) {
            $logPath = (new SplFileInfo($logFile))->getRealPath();

            if (is_string($logPath)) {
                $logLines = file($logPath);

                if (is_array($logLines)) {
                    foreach ($logLines as $key => $logLine) {
                        if (strpos($logLine, $file) !== FALSE) {
                            unset($logLines[$key]);
                        }
                    }

                    file_put_contents($logPath, implode('', $logLines));
                    unlink(sprintf('%s/%s', Debugger::$logDirectory, $file));
                }
            }
        }

        header(sprintf('Location: %s', parse_url(self::getCurrentUrl(), PHP_URL_PATH)));
    }

    /**
     *
     */
    private function handleDirectoryDelete(): void
    {
        /** @var array $logFiles */
        $logFiles = glob(sprintf('%s/*.*', Debugger::$logDirectory));

        foreach ($logFiles as $logFile) {
            $logFile = new SplFileInfo($logFile);
            $logPath = $logFile->getRealPath();

            if (is_string($logPath)) {
                $logFile->isDir() ? rmdir($logPath) : unlink($logPath);
            }
        }

        header(sprintf('Location: %s', parse_url(self::getCurrentUrl(), PHP_URL_PATH)));
    }

    /**
     * @return string
     */
    private static function getCurrentUrl(): string
    {
        return sprintf(
            '%s://%s%s',
            isset($_SERVER['HTTPS']) ? 'https' : 'http',
            $_SERVER['HTTP_HOST'],
            $_SERVER['REQUEST_URI']
        );
    }

    /**
     * @param int|float $number
     * @param int       $decimals
     *
     * @return string
     */
    public static function number($number, int $decimals = 0): string
    {
        return number_format($number, $decimals, '.', ' ');
    }

    /**
     * @param string $link
     *
     * @return string
     */
    public static function link(string $link): string
    {
        $url = self::getCurrentUrl();

        return sprintf('%s%s%s', $url, strpos($url, '?') === FALSE ? '?' : '&', $link);
    }

}