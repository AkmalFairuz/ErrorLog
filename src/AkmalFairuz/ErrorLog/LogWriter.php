<?php

declare(strict_types=1);

namespace AkmalFairuz\ErrorLog;

use pocketmine\thread\Thread;
use pocketmine\utils\AssumptionFailedError;
use Threaded;

class LogWriter extends Thread{

    private string $log;
    private Threaded $buffer;
    public bool $running = false;

    public function __construct(string $log) {
        $this->log = $log;
        $this->buffer = new Threaded();
    }

    public function onRun(): void {
        $this->running = true;
        $stream = fopen($this->log, 'ab');
        if (!is_resource($stream)) {
            throw new AssumptionFailedError("Open File $this->log failed");
        }
        while ($this->running) {
            $this->writeStream($stream);
            $this->synchronized(function () {
                if ($this->running) {
                    $this->wait();
                }
            });
        }
        $this->writeStream($stream);
        fclose($stream);
    }

    private function writeStream($stream): void {
        while ($this->buffer->count() > 0) {
            /** @var string $line */
            $line = $this->buffer->pop();
            fwrite($stream, $line);
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