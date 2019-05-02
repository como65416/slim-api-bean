<?php

namespace comoco\SlimApiBean\Handler;

use comoco\SlimApiBean\Utils\ResponseNormalizer;
use comoco\SlimApiBean\Handler\BaseHandler;

abstract class AbstractMiddleware extends BaseHandler
{
    /**
     * @param  Slim\Http\Request $request
     * @param  Slim\Http\Response $response
     * @param  callable $next
     * @return mixed
     */
    abstract public function handle($request, $response, $next);
}