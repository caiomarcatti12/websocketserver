<?php
namespace CaioMarcatti12\WebSocketServer\Adapter;

use CaioMarcatti12\CacheManager\CacheManager;
use CaioMarcatti12\Core\Bean\Objects\BeanCache;
use CaioMarcatti12\Core\Bean\Objects\BeanProxy;
use CaioMarcatti12\Core\Factory\Annotation\Autowired;
use CaioMarcatti12\Core\Factory\InstanceFactory;
use CaioMarcatti12\Core\Factory\Invoke;
use CaioMarcatti12\Core\Modules\Modules;
use CaioMarcatti12\Core\Modules\ModulesEnum;
use CaioMarcatti12\Core\Validation\Assert;
use CaioMarcatti12\Data\BodyLoader;
use CaioMarcatti12\Data\Request\Objects\Body;
use CaioMarcatti12\Data\Request\Objects\Header;
use CaioMarcatti12\Event\Interfaces\EventManagerInterface;
use CaioMarcatti12\WebSocketServer\Exception\ResponseTypeException;
use CaioMarcatti12\WebSocketServer\Exception\RouteNotFoundException;
use CaioMarcatti12\WebSocketServer\Interfaces\WebSocketServerRunnerInterface;
use CaioMarcatti12\WebSocketServer\Objects\Routes;
use CaioMarcatti12\WebSocketServer\RouterResponse;
use Ramsey\Uuid\Uuid;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use ReflectionClass;
use ReflectionMethod;

class SwooleAdapter implements WebSocketServerRunnerInterface
{
    #[Autowired]
    private CacheManager $cacheManager;

    public function run(): void
    {
        $context = $this;
        $server = new Server('0.0.0.0', '80');

        $server->on('start', function (Server $server){

        });

        $server->on('open', function (Server $server, Request $request) use ($context) {
            $context->cacheManager->set($request->fd, '');
        });

        // we can also run a regular HTTP server at the same time!
        $server->on('request', function (Request $request, Response $response) {
            $response->header('Content-Type', 'text/html');
            $response->end(file_get_contents(__DIR__ . '/websocket.html'));
        });

        $server->on('message', function (Server $server, Frame $frame) use($context) {

            $context->parseBody($frame);
            $context->parseHeader($frame);
            $context->parseCorrelationId();

            try {
                $requestUri = $context->parseRoute();

                $route = Routes::getRoute($requestUri);

                if(Assert::isEmpty($route)) throw new RouteNotFoundException($requestUri);

                $responseRoute = Invoke::new($route->getClass(), $route->getClassMethod());

                $responseRoute = $context->parseResponse($route->getClass(), $route->getClassMethod(), $responseRoute);
            }
            catch (\Throwable $throwable){
                $code = $throwable->getCode();
                if($code <= 0) $code = 500;

                $responseRoute = new RouterResponse(['error' => $throwable->getMessage()], $code);
            }

            $server->push($frame->fd, $responseRoute->response());

            if (Modules::isEnabled(ModulesEnum::EVENT) && $route !== null) {
                /** @var EventManagerInterface $eventManager */
                $eventManager = InstanceFactory::createIfNotExists(EventManagerInterface::class);
                $eventManager->dispatch();
            }
        });

        $server->on('close', function (Server $server, int $client) use ($context) {
            $context->cacheManager->del($client);
        });

        $server->start();
    }
    private function parseBody(Frame $frame): void{
        $bodyLoader = new BodyLoader();
        $bodyLoader->load(json_decode($frame->data, true));
    }

    private function parseRoute(): string{
        $path = Body::get('route', '');

        if(!str_starts_with($path, '/')) $path = '/'.$path;
        if(!str_ends_with($path, '/')) $path = $path.'/';

        $path = str_replace('///', '/', $path);
        $path = str_replace('//', '/', $path);

        return $path;
    }

    private function parseHeader(Frame $frame): void{
        Header::add('x-connection-ws-id', $frame->fd);
    }

    private function parseCorrelationId(): void{
        Header::add('x-correlation-id', Uuid::uuid4()->toString());
    }

    private function parseResponse(string $class, string $method, mixed $response): mixed {
        $reflectionClass = new ReflectionClass($class);

        /** @var ReflectionMethod $reflectionMethod */
        $reflectionMethod = $reflectionClass->getMethod($method);

        $returnTypeName = $this->getReturnTypeName($reflectionMethod);

        return $this->makeResponse($returnTypeName, $response);
    }

    private function getReturnTypeName(ReflectionMethod $reflectionMethod): string
    {
        $returnType = $reflectionMethod->getReturnType();

        if (Assert::isEmpty($returnType)) throw new ResponseTypeException();

        return $returnType->getName();
    }

    private function makeResponse(string $returnTypeName, mixed $response): mixed
    {
        BeanCache::destroy(RouterResponse::class);

        if (Assert::inArray($returnTypeName, [RouterResponse::class])) {
            return $response;
        } else if (!Assert::equals($returnTypeName, "void")) {
            return InstanceFactory::createIfNotExists(RouterResponse::class, [$response, 200], false);
        }

        $classProxyRouterInterface = BeanProxy::get(RouterResponse::class);

        if (Assert::inArray($classProxyRouterInterface, [RouterResponse::class])) {
            return null;
        }

        return InstanceFactory::createIfNotExists($classProxyRouterInterface, ['', 200], false);
    }
}