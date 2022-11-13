<?php

namespace CaioMarcatti12\WebSocketServer;

use CaioMarcatti12\Core\Factory\Annotation\Autowired;
use CaioMarcatti12\Core\Launcher\Annotation\Launcher;
use CaioMarcatti12\Core\Launcher\Enum\LauncherPriorityEnum;
use CaioMarcatti12\Core\Launcher\Interfaces\LauncherInterface;
use CaioMarcatti12\Core\Modules\Modules;
use CaioMarcatti12\Core\Modules\ModulesEnum;
use CaioMarcatti12\Core\Shared\Interfaces\ServerRunInterface;
use CaioMarcatti12\WebSocketServer\Interfaces\WebSocketServerRunnerInterface;

class WebSocketServer implements ServerRunInterface
{
    #[Autowired]
    private WebSocketServerRunnerInterface $WebSocketServerRunner;

    public function run(): void
    {
        if(Modules::isEnabled(ModulesEnum::WEBSOCKETSERVER))
            $this->WebSocketServerRunner->run();
    }
}

