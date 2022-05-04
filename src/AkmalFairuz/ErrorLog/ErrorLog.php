<?php

declare(strict_types=1);

namespace AkmalFairuz\ErrorLog;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use function usleep;
use const PTHREADS_INHERIT_NONE;

class ErrorLog extends PluginBase{

    private LogWriter $writer;

    public function onEnable(): void{
        $this->saveResource("config.yml");

        $cfg = $this->getConfig();
        $this->writer = new LogWriter(
            Server::getInstance()->getDataPath() . $cfg->getNested("log_file.path", "error.log"),
            $cfg->getNested("log_file.enabled", true),
            $cfg->getNested("discord.enabled", false) ? $cfg->getNested("discord.webhook") : null
        );
        $this->writer->start(PTHREADS_INHERIT_NONE);
        while(!$this->writer->running) {
            usleep(1000);
        }

        Server::getInstance()->getLogger()->addAttachment(
            new LogAttachment(
                $this->writer,
            )
        );
    }

    public function onDisable(): void{
        $this->writer->close();
    }
}