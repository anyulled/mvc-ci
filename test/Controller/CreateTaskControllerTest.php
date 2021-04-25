<?php

namespace SallePW\Controller;

use DI\Container;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use SallePW\Model\Repository\TaskRepository;
use SallePW\Model\UseCase\CreateTaskUseCase;
use SallePW\Repository\MysqlTaskRepository;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use UnexpectedValueException;

class CreateTaskControllerTest extends TestCase
{
    protected App $app;
    protected Container $container;

    public function testCreateTaskController()
    {
        $request = $this->createJsonRequest("POST", "/task", ["title" => "test", "content" => "content"]);

        $response = $this->app->handle($request);

        $this->assertSame(303, $response->getStatusCode());
        $this->assertSame([0 => "/task"], $response->getHeader("Location"));

    }

    protected function createJsonRequest(
        string $method,
        UriInterface|string $uri,
        array $data = null
    ): ServerRequestInterface
    {
        $request = $this->createRequest($method, $uri);

        if ($data !== null) {
            $request = $request->withParsedBody($data);
        }

        return $request->withHeader('Content-Type', 'application/json');
    }

    protected function createRequest(string $method, UriInterface|string $uri, array $serverParams = []): ServerRequestInterface
    {
        return (new ServerRequestFactory())->createServerRequest($method, $uri, $serverParams);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $container = new Container();
        $container->set(TaskRepository::class, fn(ContainerInterface $ci) => $this->mock(MysqlTaskRepository::class));
        $container->set(CreateTaskUseCase::class, fn(ContainerInterface $ci) => new CreateTaskUseCase($ci->get(TaskRepository::class)));
        $this->app = AppFactory::create(container: $container);
        $cont = $this->app->getContainer();
        require_once __DIR__ . '/../../config/routing.php';
        addRoutes($this->app);
        if ($cont === null) {
            throw new UnexpectedValueException("Container not initialized");
        }
        $this->container = $cont;
    }

    protected function mock(string $class): MockObject
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf("Class not found: %s", $class));
        }
        $mock = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->container->set($class, $mock);
        return $mock;
    }

}
