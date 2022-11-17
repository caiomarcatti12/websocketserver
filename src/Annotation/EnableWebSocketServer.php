<?php

namespace CaioMarcatti12\WebSocketServer\Annotation;


use Attribute;
use CaioMarcatti12\Core\Bean\Annotation\AliasFor;
use CaioMarcatti12\Core\Bean\Enum\BeanType;
use CaioMarcatti12\Core\Modules\Modules;
use CaioMarcatti12\Core\Modules\ModulesEnum;
use CaioMarcatti12\WebSocketServer\Adapter\SwooleAdapter;

#[AliasFor(BeanType::CONTROLLER)]
#[Attribute(Attribute::TARGET_CLASS)]
class EnableWebSocketServer
{
    private string $adapter = '';

    public function __construct(string $adapter = SwooleAdapter::class)
    {
        $this->adapter = $adapter;

        Modules::enable(ModulesEnum::WEBSOCKETSERVER);
    }

    public function getAdapter(): string
    {
        return $this->adapter;
    }
}