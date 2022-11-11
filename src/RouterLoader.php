<?php

namespace CaioMarcatti12\WebSocketServer;

use CaioMarcatti12\Core\ExtractPhpNamespace;
use CaioMarcatti12\Core\Launcher\Annotation\Launcher;
use CaioMarcatti12\Core\Launcher\Enum\LauncherPriorityEnum;
use CaioMarcatti12\Core\Launcher\Interfaces\LauncherInterface;
use CaioMarcatti12\Core\Validation\Assert;
use CaioMarcatti12\WebSocketServer\Annotation\MessageMapping;
use CaioMarcatti12\WebSocketServer\Objects\Route;
use CaioMarcatti12\WebSocketServer\Objects\Routes;

#[Launcher(LauncherPriorityEnum::BEFORE_LOAD_APPLICATION)]
class RouterLoader implements LauncherInterface
{
    public function handler(): void
    {
        $filesApplication = ExtractPhpNamespace::getFilesApplication();
        $filesFramework = ExtractPhpNamespace::getFilesFramework();

        $this->parseFiles(array_merge($filesApplication, $filesFramework));
    }

    private  function parseFiles(array $files): void{
        foreach($files as $file){
            $reflectionClass = new \ReflectionClass($file);

            $reflectionAttributes = $reflectionClass->getAttributes(MessageMapping::class);

            if(Assert::isNotEmpty($reflectionAttributes)) {
                /** @var \ReflectionAttribute $attribute */
                $attribute = array_shift($reflectionAttributes);

                /** @var MessageMapping $instanceAttributeClass */
                $instanceAttributeClass = $attribute->newInstance();
                $routeClass = $instanceAttributeClass->getPath();

                /** @var \ReflectionMethod $reflectionMethod */
                foreach($reflectionClass->getMethods() as $reflectionMethod){
                    $reflectionAttributesMapping = $reflectionMethod->getAttributes(MessageMapping::class);

                    if(Assert::isNotEmpty($reflectionAttributesMapping)) {
                        /** @var \ReflectionAttribute $attribute */
                        $attributeMapping = array_shift($reflectionAttributesMapping);

                        /** @var MessageMapping $instanceAttributeClass */
                        $instanceAttributeClass = $attributeMapping->newInstance();

                        $routeMethod = $instanceAttributeClass->getPath();
                        $routeComplete = $routeClass.$routeMethod;

                        $this->addRoute($routeComplete, $reflectionClass->getName(), $reflectionMethod->getName());
                    }
                }
            }
        }
    }

    private function addRoute(string $uri, string $file, $method): void {
        $route = new Route($uri, $file, $method);
        Routes::add($route);
    }
}