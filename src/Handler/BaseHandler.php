<?php

namespace comoco\SlimApiBean\Handler;

use comoco\SlimApiBean\Utils\ResponseNormalizer;

abstract class BaseHandler
{
    public function __invoke($request, $response, ...$args)
    {
        $handle_func_args = array_merge([$request, $response], $args);
        $data = call_user_func_array([$this, 'handle'], $handle_func_args);
        return ResponseNormalizer::convert($response, $data);
    }
}
