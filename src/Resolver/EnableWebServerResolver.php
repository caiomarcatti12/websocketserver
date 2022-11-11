<?php

namespace CaioMarcatti12\WebSocketServer\Resolver;

use CaioMarcatti12\Core\Bean\Annotation\AnnotationResolver;
use CaioMarcatti12\Core\Bean\Interfaces\ClassResolverInterface;
use CaioMarcatti12\Core\Bean\Objects\BeanProxy;
use CaioMarcatti12\WebSocketServer\Annotation\EnableWebSocketServer;
use CaioMarcatti12\WebSocketServer\Interfaces\WebSocketServerRunnerInterface;
use ReflectionClass;

#[AnnotationResolver(EnableWebSocketServer::class)]
class EnableWebServerResolver  implements ClassResolverInterface
{
    public function handler(object &$instance): void
    {
        $reflectionClass = new ReflectionClass($instance);

        $attributes = $reflectionClass->getAttributes(EnableWebSocketServer::class);

        /** @var EnableWebSocketServer $attribute */
        $attribute = ($attributes[0]->newInstance());

        BeanProxy::add(WebSocketServerRunnerInterface::class, $attribute->getAdapter());
    }
}