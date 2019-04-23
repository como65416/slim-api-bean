<?php

namespace comoco\SlimApiBean\Handler;

use comoco\SlimApiBean\Utils\ResponseNormalizer;

abstract class AbstractExceptionHandler
{
    public function __invoke($request, $response, $exception)
    {
        $data = $this->handle($request, $response, $exception);
        return ResponseNormalizer::convert($response, $data);
    }

    /**
     * @param  Slim\Http\Request $request
     * @param  Slim\Http\Response $response
     * @param  Exception $error
     * @return mixed
     */
    abstract public function handle($request, $response, $exception);
}
