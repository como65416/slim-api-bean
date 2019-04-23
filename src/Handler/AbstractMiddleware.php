<?php

namespace comoco\SlimApiBean\Handler;

use comoco\SlimApiBean\Utils\ResponseNormalizer;

abstract class AbstractMiddleware
{
    public function __invoke($request, $response, $next)
    {
        $data = $this->handle($request, $response, $next);
        return ResponseNormalizer::convert($response, $data);
    }

    /**
     * @param  Slim\Http\Request $request
     * @param  Slim\Http\Response $response
     * @param  callable $next
     * @return mixed
     */
    abstract public function handle($request, $response, $next);
}