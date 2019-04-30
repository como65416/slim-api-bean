<?php

namespace comoco\SlimApiBean\Manager;

use BadMethodCallException;
use RuntimeException;
use LogicException;
use comoco\SlimApiBean\Handler\AbstractExceptionHandler;
use comoco\SlimApiBean\Manager\ErrorHandlerManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ErrorHandlerManagerTest extends TestCase
{
    public function testInvoke()
    {
        $exception_handler1 = $this->createMock(AbstractExceptionHandler::class);
        $exception_handler1->method('__invoke')->willReturn(1);
        $exception_handler2 = $this->createMock(AbstractExceptionHandler::class);
        $exception_handler2->method('__invoke')->willReturn(2);
        $exception_handler3 = $this->createMock(AbstractExceptionHandler::class);
        $exception_handler3->method('__invoke')->willReturn(3);

        $mock_request = $this->createMock(RequestInterface::class);
        $mock_response = $this->createMock(ResponseInterface::class);
        $errorHandlerManager = new ErrorHandlerManager($exception_handler1, [
            RuntimeException::class => $exception_handler2,
            BadMethodCallException::class => $exception_handler3
        ]);
        $this->assertEquals(1, $errorHandlerManager->__invoke($mock_request, $mock_response, new LogicException));
        $this->assertEquals(2, $errorHandlerManager->__invoke($mock_request, $mock_response, new RuntimeException));
        $this->assertEquals(3, $errorHandlerManager->__invoke($mock_request, $mock_response, new BadMethodCallException));
    }
}