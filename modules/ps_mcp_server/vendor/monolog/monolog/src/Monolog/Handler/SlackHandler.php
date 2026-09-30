<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use Monolog\Utils;
use Monolog\Handler\Slack\SlackRecord;


class SlackHandler extends SocketHandler
{
    
    private $token;

    
    private $slackRecord;

    
    public function __construct(
        string $token,
        string $channel,
        ?string $username = null,
        bool $useAttachment = true,
        ?string $iconEmoji = null,
        $level = Logger::CRITICAL,
        bool $bubble = true,
        bool $useShortAttachment = false,
        bool $includeContextAndExtra = false,
        array $excludeFields = array(),
        bool $persistent = false,
        float $timeout = 0.0,
        float $writingTimeout = 10.0,
        ?float $connectionTimeout = null,
        ?int $chunkSize = null
    ) {
        if (!extension_loaded('openssl')) {
            throw new MissingExtensionException('The OpenSSL PHP extension is required to use the SlackHandler');
        }

        parent::__construct(
            'ssl://slack.com:443',
            $level,
            $bubble,
            $persistent,
            $timeout,
            $writingTimeout,
            $connectionTimeout,
            $chunkSize
        );

        $this->slackRecord = new SlackRecord(
            $channel,
            $username,
            $useAttachment,
            $iconEmoji,
            $useShortAttachment,
            $includeContextAndExtra,
            $excludeFields
        );

        $this->token = $token;
    }

    public function getSlackRecord(): SlackRecord
    {
        return $this->slackRecord;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    
    protected function generateDataStream(array $record): string
    {
        $content = $this->buildContent($record);

        return $this->buildHeader($content) . $content;
    }

    
    private function buildContent(array $record): string
    {
        $dataArray = $this->prepareContentData($record);

        return http_build_query($dataArray);
    }

    
    protected function prepareContentData(array $record): array
    {
        $dataArray = $this->slackRecord->getSlackData($record);
        $dataArray['token'] = $this->token;

        if (!empty($dataArray['attachments'])) {
            $dataArray['attachments'] = Utils::jsonEncode($dataArray['attachments']);
        }

        return $dataArray;
    }

    
    private function buildHeader(string $content): string
    {
        $header = "POST /api/chat.postMessage HTTP/1.1\r\n";
        $header .= "Host: slack.com\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($content) . "\r\n";
        $header .= "\r\n";

        return $header;
    }

    
    protected function write(array $record): void
    {
        parent::write($record);
        $this->finalizeWrite();
    }

    
    protected function finalizeWrite(): void
    {
        $res = $this->getResource();
        if (is_resource($res)) {
            @fread($res, 2048);
        }
        $this->closeSocket();
    }

    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        parent::setFormatter($formatter);
        $this->slackRecord->setFormatter($formatter);

        return $this;
    }

    public function getFormatter(): FormatterInterface
    {
        $formatter = parent::getFormatter();
        $this->slackRecord->setFormatter($formatter);

        return $formatter;
    }

    
    public function setChannel(string $channel): self
    {
        $this->slackRecord->setChannel($channel);

        return $this;
    }

    
    public function setUsername(string $username): self
    {
        $this->slackRecord->setUsername($username);

        return $this;
    }

    public function useAttachment(bool $useAttachment): self
    {
        $this->slackRecord->useAttachment($useAttachment);

        return $this;
    }

    public function setIconEmoji(string $iconEmoji): self
    {
        $this->slackRecord->setUserIcon($iconEmoji);

        return $this;
    }

    public function useShortAttachment(bool $useShortAttachment): self
    {
        $this->slackRecord->useShortAttachment($useShortAttachment);

        return $this;
    }

    public function includeContextAndExtra(bool $includeContextAndExtra): self
    {
        $this->slackRecord->includeContextAndExtra($includeContextAndExtra);

        return $this;
    }

    
    public function excludeFields(array $excludeFields): self
    {
        $this->slackRecord->excludeFields($excludeFields);

        return $this;
    }
}
