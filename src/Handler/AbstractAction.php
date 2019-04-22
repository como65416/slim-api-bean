<?php

namespace comoco\SlimApiBean\Handler;

use comoco\SlimApiBean\Utils\ResponseNormalizer;

abstract class AbstractAction
{
    public function __invoke($request, $response, $args)
    {
        $data = $this->handle($request, $response, $args);
        return ResponseNormalizer::convert($response, $data);
    }

    /**
     * @param  Slim\Http\Request $request
     * @param  Slim\Http\Response $response
     * @param  array $args
     * @return mixed
     */
    abstract public function handle($request, $response, $args);
}
