<?php

namespace comoco\SlimApiBean\Handler;

use comoco\SlimApiBean\Utils\ResponseNormalizer;
use comoco\SlimApiBean\Handler\BaseHandler;

abstract class AbstractNotFoundHandler extends BaseHandler
{
    /**
     * @param  Slim\Http\Request $request
     * @param  Slim\Http\Response $response
     * @param  Exception $error
     * @return mixed
     */
    abstract public function handle($request, $response);
}
