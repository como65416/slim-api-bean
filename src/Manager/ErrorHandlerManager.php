<?php

namespace comoco\SlimApiBean\Manager;

use comoco\SlimApiBean\AbstractExceptionHandler;

class ErrorHandlerManager
{
    /**
     * @var AbstractErrorHandler
     */
    protected $default_error_handler;

    /**
     * @var array
     */
    protected $error_handler_map;

    /**
     * @var AbstractErrorHandler $default_error_handler
     * @var array $error_handler_map
     * [
     *     excetion classname => handler classname,
     *     ...
     * ]
     */
    public function __construct($default_error_handler, array $error_handler_map)
    {
        $this->default_error_handler = $default_error_handler;
        $this->error_handler_map = $error_handler_map;
    }

    /**
     * @param  Psr\Http\Message\RequestInterface $request
     * @param  Psr\Http\Message\ResponseInterface $response
     * @param  Exception $exception
     * @return mixed
     */
    public function __invoke($request, $response, $exception)
    {
        foreach ($this->error_handler_map as $classname => $handler)
        {
            if ($classname == get_class($exception)) {
                return $handler->__invoke($request, $response, $exception);
            }
        }
        return $this->default_error_handler->__invoke($request, $response, $exception);
    }
}
