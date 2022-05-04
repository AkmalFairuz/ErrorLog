<?php

declare(strict_types=1);

namespace AkmalFairuz\ErrorLog;

use pocketmine\thread\Thread;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Internet;
use Threaded;
use function date;
use function fwrite;
use function json_encode;
use function min;
use function str_replace;
use function strlen;
use function substr;
use const CURLOPT_POSTFIELDS;

class LogWriter extends Thread{

    private Threaded $buffer;
    public bool $running = false;

    public function __construct(
        private string $log,
        private bool $logFileEnabled,
        private ?string $discordWebhook
    ) {
        if($this->discordWebhook === "") {
            $this->discordWebhook = null;
        }
        $this->buffer = new Threaded();
    }

    public function onRun(): void {
        $this->running = true;
        if($this->logFileEnabled){
            $stream = fopen($this->log, "ab");
            if(!is_resource($stream)){
                throw new AssumptionFailedError("Open File $this->log failed");
            }
        }
        while ($this->running) {
            $this->writeStream($stream ?? null);
            $this->synchronized(function () {
                if ($this->running) {
                    $this->wait();
                }
            });
        }
        if(isset($stream)){
            $this->writeStream($stream);
            fclose($stream);
        }
    }

    private function writeStream($stream): void {
        $lines = "";
        while(($line = $this->buffer->shift()) !== null) {
            /** @var string $line */
            $lines .= $line;
        }
        if($stream !== null) {
            fwrite($stream, $lines);
        }
        if(($webhook = $this->discordWebhook) !== null) {
            if(strlen(trim($lines)) === 0) { // don't send blank log
                return;
            }
            Internet::simpleCurl(
                $webhook,
                5,
                [
                    "Content-Type: application/json"
                ],
                [
                    CURLOPT_POSTFIELDS => json_encode([
                        "username" => "ErrorLog",
                        "embeds" => [
                            [
                                "color" => 16711680, // RED,
                                "description" => "```php\n" . str_replace("`", "\`", substr($lines, 0, min(strlen($lines), 1500))) . "\n```",
                                "timestamp" => date("Y-m-d")."T".date("H:i:s").".".date("v")."Z"
                            ]
                        ]
                    ])
                ]
            );
        }
    }

    public function close() {
        $this->running = false;
        $this->notify();
    }

    public function write(string $data): void {
        $this->buffer[] = $data;
        $this->notify();
    }
}