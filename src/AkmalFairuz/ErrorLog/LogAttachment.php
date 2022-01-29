<?php

declare(strict_types=1);

namespace AkmalFairuz\ErrorLog;

class LogAttachment extends \ThreadedLoggerAttachment{

    public function __construct(private LogWriter $writer) {
    }

    public function log($level, $message){
        if($level >= \LogLevel::WARNING) {
            $this->writer->write($message);
        }
    }
}