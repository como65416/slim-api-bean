<?php

namespace comoco\SlimApiBean\Handler;

use AspectMock\Test as AspectMock;
use comoco\SlimApiBean\Handler\BaseHandler;
use comoco\SlimApiBean\Utils\ResponseNormalizer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BaseHandlerTest extends TestCase
{
    public function tearDown()
    {
        AspectMock::clean();
    }

    public function testInvoke()
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $hander = $this->getMockBuilder(BaseHandler::class)
            ->setMethods(["handle"])
            ->disableOriginalConstructor()
            ->getMock();
        $hander->method('handle')
            ->with(
                $this->equalTo($request),
                $this->equalTo($response),
                $this->equalTo(1),
                $this->equalTo(2)
            )
            ->will($this->returnValue(3));
        $normalizer = AspectMock::double(ResponseNormalizer::class, ['convert' => 4]);

        $this->assertEquals(4, $hander->__invoke($request, $response, 1, 2));
        $normalizer->verifyInvoked('convert', [$response, 3]);
    }
}