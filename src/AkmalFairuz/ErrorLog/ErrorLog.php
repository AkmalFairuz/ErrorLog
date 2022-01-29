<?php

declare(strict_types=1);

namespace AkmalFairuz\ErrorLog;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class ErrorLog extends PluginBase{

    private LogWriter $writer;

    public function onEnable(): void{
        $this->writer = new LogWriter(Server::getInstance()->getDataPath() . "error.log");
        $this->getLogger()->addAttachment(new LogAttachment($this->writer));
    }

    public function onDisable(): void{
        $this->writer->close();
    }
}