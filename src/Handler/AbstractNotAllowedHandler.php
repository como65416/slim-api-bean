<?php

namespace comoco\SlimApiBean\Handler;

use comoco\SlimApiBean\Utils\ResponseNormalizer;

abstract class AbstractNotAllowedHandler
{
    public function __invoke($request, $response, $methods)
    {
        $data = $this->handle($request, $response, $methods);
        return ResponseNormalizer::convert($response, $data);
    }

    /**
     * @param  Slim\Http\Request $request
     * @param  Slim\Http\Response $response
     * @param  Exception $error
     * @return mixed
     */
    abstract public function handle($request, $response, $methods);
}
