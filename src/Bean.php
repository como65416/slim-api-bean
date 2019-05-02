<?php

namespace comoco\SlimApiBean;

use Psr\Http\Message\UploadedFileInterface;
use comoco\SlimApiBean\Handler\AbstractAction;
use comoco\SlimApiBean\Handler\AbstractExceptionHandler;
use comoco\SlimApiBean\Handler\AbstractNotAllowedHandler;
use comoco\SlimApiBean\Handler\AbstractNotFoundHandler;
use comoco\SlimApiBean\Handler\AbstractRuntimeErrorHandler;
use comoco\SlimApiBean\Utils\SlimAppUtil;

class Bean
{
    /**
     * @var array|null @see http://www.slimframework.com/docs/v3/objects/application.html#application-configuration
     */
    protected $slim_config = null;

    /**
     * @var array structure:
     * [
     *     [
     *         'uri' => uri,
     *         'methods' => [
     *             method,
     *             method,
     *             ...
     *         ],
     *         'action' => AbstractAction,
     *         'middlewares' => [
     *             AbstractMiddleware,
     *             ...
     *         ]
     *     ],
     *     ...
     * ]
     */
    protected $action_config = null;

    /**
     * @var array array of AbstractMiddleware
     */
    protected $global_middlewares = [];

    /**
     * @var AbstractExceptionHandler
     */
    protected $default_exception_handler = null;

    /**
     * @var AbstractNotFoundHandler
     */
    protected $page_not_found_error_handler = null;

    /**
     * @var AbstractRuntimeErrorHandler
     */
    protected $runtime_error_handler = null;

    /**
     * @var AbstractNotAllowedHandler
     */
    protected $not_allowed_error_handler = null;

    /**
     * Exception and Error handler map
     * @var array
     * [
     *     excetion classname => AbstractErrorHandler,
     *     excetion classname => AbstractErrorHandler,
     *     ...
     * ]
     */
    protected $expection_error_handler_map = [];

    /**
     * @param array $config slim config @see http://www.slimframework.com/docs/v3/objects/application.html#application-configuration
     */
    public function __construct(array $config = null)
    {
        $this->slim_config = $config;
    }

    /**
     * @param array $middlewares array of AbstractMiddleware
     * @return self
     */
    public function addGlobalMiddleware(array $middlewares)
    {
        $this->global_middlewares = array_merge($this->global_middlewares, $middlewares);
        return $this;
    }

    /**
     * @param  array          $methods     array of method
     * @param  string         $uri         uri route pattern
     * @param  AbstractAction $action
     * @param  array          $middlewares array of AbstractMiddleware
     * @return self
     */
    public function bindAction(array $methods, $uri, AbstractAction $action, array $middlewares = [])
    {
        $this->action_config[] = [
            'uri' => $uri,
            'methods' => $methods,
            'action' => $action,
            'middlewares' => $middlewares
        ];
        return $this;
    }

    /**
     * @param  array array of Exception name
     * @param  AbstractExceptionHandler $error_handler
     * @return self
     */
    public function bindExceptionHandler(array $exception_classnames, AbstractExceptionHandler $error_handler)
    {
        foreach ($exception_classnames as $classname) {
            $this->expection_error_handler_map[$classname] = $error_handler;
        }
        return $this;
    }

    /**
     * @param AbstractExceptionHandler $handler
     * @return self
     */
    public function setDefaultExceptionHandler(AbstractExceptionHandler $handler)
    {
        $this->default_exception_handler = $handler;
        return $this;
    }

    /**
     * @param AbstractNotFoundHandler $handler
     * @return self
     */
    public function setPageNotFoundHandler(AbstractNotFoundHandler $handler)
    {
        $this->page_not_found_error_handler = $handler;
        return $this;
    }

    /**
     * @param AbstractRuntimeErrorHandler $handler
     * @return self
     */
    public function setRuntimeErrorHandler(AbstractRuntimeErrorHandler $handler)
    {
        $this->runtime_error_handler = $handler;
        return $this;
    }

    /**
     * @param AbstractNotAllowedHandler $handler
     * @return self
     */
    public function setNotAllowedHandler(AbstractNotAllowedHandler $handler)
    {
        $this->not_allowed_error_handler = $handler;
        return $this;
    }

    /**
     * start run app
     */
    public function run()
    {
        $config = $this->generateConfig();
        $app = SlimAppUtil::createSlimApp($config);
        $app->run();
    }

    /**
     * @param  string $method
     * @param  string $uri
     * @param  array  $params
     * [
     *     'headers' => [
     *         header_name => array|string|int,
     *         header_name => array|string|int,
     *         ...
     *     ],
     *     'body' => [
     *         name => string|array|UploadedFileInterface,
     *         name => string|array|UploadedFileInterface,
     *         ...
     *     ]
     * ]
     * @return Psr\Http\Message\ResponseInterface
     */
    public function dryRun($method, $uri, $params = [])
    {
        $slim_config = $this->generateConfig();
        $app = SlimAppUtil::createSlimApp($slim_config);
        $request = SlimAppUtil::createMockRequest($method, $uri, $params);
        $container = $app->getContainer();
        $container['request'] = $request;
        return $app->run(true);
    }

    /**
     * generate config for create slim app
     *
     * @return array @see comoco\SlimApiBean\Utils\SlimAppUtil createSlimApp function `$config`
     */
    protected function generateConfig()
    {
        $config = [];
        $config['slim_config'] = $this->slim_config;
        $config['action_config'] = $this->action_config;
        $config['global_middlewares'] = $this->global_middlewares;
        $config['default_exception_handler'] = $this->default_exception_handler;
        $config['runtime_error_handler'] = $this->runtime_error_handler;
        $config['page_not_found_error_handler'] = $this->page_not_found_error_handler;
        $config['expection_error_handler_map'] = $this->expection_error_handler_map;
        $config['not_allowed_error_handler'] = $this->not_allowed_error_handler;
        return $config;
    }
}
