<?php

declare(strict_types=1);

namespace AkmalFairuz\ErrorLog;

use pocketmine\utils\TextFormat;
use function date;
use const PHP_EOL;

class LogAttachment extends \ThreadedLoggerAttachment{

    public function __construct(
        private LogWriter $writer
    ) {
    }

    public function log($level, $message){
        switch($level) {
            case \LogLevel::WARNING:
            case \LogLevel::ERROR:
            case \LogLevel::ALERT:
            case \LogLevel::CRITICAL:
            case \LogLevel::EMERGENCY:
                $this->writer->write(date("Y-m-d") . " " . TextFormat::clean($message) . PHP_EOL);
                break;
        }
    }
}