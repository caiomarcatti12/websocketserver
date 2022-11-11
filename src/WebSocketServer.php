<?php

namespace CaioMarcatti12\WebSocketServer;

use CaioMarcatti12\Core\Factory\Annotation\Autowired;
use CaioMarcatti12\Core\Launcher\Annotation\Launcher;
use CaioMarcatti12\Core\Launcher\Enum\LauncherPriorityEnum;
use CaioMarcatti12\Core\Launcher\Interfaces\LauncherInterface;
use CaioMarcatti12\Core\Modules\Modules;
use CaioMarcatti12\Core\Modules\ModulesEnum;
use CaioMarcatti12\WebSocketServer\Interfaces\WebSocketServerRunnerInterface;

#[Launcher(LauncherPriorityEnum::AFTER_LOAD_APPLICATION)]
class WebSocketServer implements LauncherInterface
{
    #[Autowired]
    private WebSocketServerRunnerInterface $WebSocketServerRunner;

    public function handler(): void
    {
        if(Modules::isEnabled(ModulesEnum::WEBSOCKETSEVER))
            $this->WebSocketServerRunner->run();
    }
}

