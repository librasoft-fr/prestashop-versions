<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Logger;
use Monolog\Utils;


class IFTTTHandler extends AbstractProcessingHandler
{
    
    private $eventName;
    
    private $secretKey;

    
    public function __construct(string $eventName, string $secretKey, $level = Logger::ERROR, bool $bubble = true)
    {
        if (!extension_loaded('curl')) {
            throw new MissingExtensionException('The curl extension is needed to use the IFTTTHandler');
        }

        $this->eventName = $eventName;
        $this->secretKey = $secretKey;

        parent::__construct($level, $bubble);
    }

    
    public function write(array $record): void
    {
        $postData = [
            "value1" => $record["channel"],
            "value2" => $record["level_name"],
            "value3" => $record["message"],
        ];
        $postString = Utils::jsonEncode($postData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://maker.ifttt.com/trigger/" . $this->eventName . "/with/key/" . $this->secretKey);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);

        Curl\Util::execute($ch);
    }
}
