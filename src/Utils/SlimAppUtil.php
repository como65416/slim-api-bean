<?php

namespace comoco\SlimApiBean\Utils;

use comoco\SlimApiBean\Manager\ErrorHandlerManager;
use Slim\App;
use Slim\Http\Uri;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\UploadedFile;
use Psr\Http\Message\UploadedFileInterface;

class SlimAppUtil
{
    /**
     * @param  array $config
     * [
     *     'slim_config' => slim config(array)|null,
     *     'action_config' => [
     *         [
     *             'uri' => uri,
     *             'methods' => [
     *                 method,
     *                 method,
     *                 ...
     *             ],
     *             'action' => AbstractAction,
     *             'middlewares' => [
     *                 AbstractMiddleware,
     *                 ...
     *             ]
     *         ],
     *         ...
     *     ],
     *     'global_middlewares' => [
     *         AbstractMiddleware,
     *         AbstractMiddleware,
     *         ...
     *     ],
     *     'default_exception_handler => AbstractErrorHandler,
     *     'page_not_found_error_handler' => AbstractErrorHandler,
     *     'runtime_error_handler' => AbstractErrorHandler,
     *     'expection_error_handler_map' => [
     *         excetion classname => AbstractErrorHandler,
     *         excetion classname => AbstractErrorHandler,
     *         ...
     *     ]
     * ]
     * @return Slim\App
     */
    public static function createSlimApp($config)
    {
        $app = (!empty($config['slim_config'])) ? new App($config['slim_config']) : new App;

        // add action
        $action_configs = (!empty($config['action_config'])) ? $config['action_config'] : [] ;
        foreach ($action_configs as $data) {
            $uri = $data['uri'];
            $methods = $data['methods'];
            $action = $data['action'];
            $middlewares = $data['middlewares'];
            $route = $app->map($methods, $uri, $action);
            foreach ($middlewares as $middleware) {
                $route->add($middleware);
            }
        }

        // add middleware
        foreach ($config['global_middlewares'] as $middleware) {
            $app->add($middleware);
        }

        // replace default error handler to ErrorHandlerManager
        $contaner = $app->getContainer();
        $default_exception_handler = (!empty($config['default_exception_handler'])) ? $config['default_exception_handler'] : $contaner['errorHandler'] ;
        $expection_error_handler_map = (!empty($config['expection_error_handler_map'])) ? $config['expection_error_handler_map'] : [] ;
        unset($contaner['errorHandler']);
        $contaner['errorHandler'] = function ($c) use ($default_exception_handler, $expection_error_handler_map) {
            return new ErrorHandlerManager($default_exception_handler, $expection_error_handler_map);
        };

        // replace special error handler
        $handler_map = [
            'notFoundHandler' => 'page_not_found_error_handler',
            'phpErrorHandler' => 'runtime_error_handler',
            'notAllowedHandler' => 'not_allowed_error_handler'
        ];
        foreach ($handler_map as $container_key => $config_key) {
            if (!empty($config[$config_key])) {
                $handler = $config[$config_key];
                $contaner[$container_key] = function ($c) use ($handler) {
                    return $handler;
                };
            }
        }

        return $app;
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
    public static function createMockRequest($method, $uri, $params)
    {
        $uri = Uri::createFromString($uri);

        $cookies = [];
        $body_datas = (!empty($params['body'])) ?  $params['body'] : [] ;
        $header_fields = (!empty($params['headers'])) ? $params['headers'] : [];
        $uploaded_files = static::createUploadedFiles($body_datas);
        $headers = static::createHeader($header_fields, $uploaded_files);
        $parsed_body = static::toParsedBody($body_datas);
        $request_body = static::createRequestBody($parsed_body, $headers);
        $server_params = Environment::mock()->all();

        $request = new Request($method, $uri, $headers, $cookies, $server_params, $request_body, $uploaded_files);
        if (!empty($parsed_body)) {
            $request = $request->withParsedBody($parsed_body);
        }
        return $request;
    }

    /**
     * @param array $header_fields
     * [
     *     header_name => array|string,
     *     header_name => array|string,
     *     ...
     * ]
     * @return Headers
     */
    protected static function createHeader($header_fields, array $uploadedFiles)
    {
        $headers = new Headers;
        foreach ($header_fields as $field_name => $field_value) {
            $headers->set($field_name, $field_value);
        }
        // set default content type
        if (empty($headers->get("Content-Type"))) {
            $type = (!empty($uploadedFiles)) ? 'multipart/form-data' : 'application/x-www-form-urlencoded' ;
            $headers->set('Content-Type', $type);
        }
        return $headers;
    }

    /**
     * @param  array   $body_datas
     * [
     *     name => string|array|UploadedFileInterface,
     *     name => string|array|UploadedFileInterface,
     *     ...
     * ]
     * @return array
     */
    protected static function toParsedBody($body_datas)
    {
        foreach ($body_datas as $filed_name => $filed_data) {
            if (
                (is_array($filed_data) && is_subclass_of(reset($filed_data), UploadedFileInterface::class)) ||
                (is_subclass_of($filed_data, UploadedFileInterface::class))
            ) {
                unset($body_datas[$filed_name]);
            }
        }
        return $body_datas;
    }

    /**
     * @param  array   $body_datas
     * [
     *     name => string|array|int,
     *     name => string|array|int,
     *     ...
     * ]
     * @param  Headers $headers
     * @return RequestBody
     */
    protected static function createRequestBody(array $parsed_body, Headers $headers)
    {
        $body = new RequestBody();
        $content_type = (!empty($headers->get("Content-Type"))) ? strtolower($headers->get("Content-Type")[0]) : '' ;
        if ($content_type == 'application/json') {
            $body->write(json_encode($parsed_body));
        } else if ($content_type == 'application/x-www-form-urlencoded') {
            $datas = [];
            foreach ($parsed_body as $filed_name => $filed_data) {
                if (is_array($filed_data)) {
                    foreach ($filed_data as $value) {
                        $datas[] .= urlencode($filed_name . "[]") . "=" . urlencode($value);
                    }
                } else {
                    $datas[] .= urlencode($filed_name) . "=" . urlencode($filed_data);
                }
            }
            $body->write(implode("&", $datas));
        }
        return $body;
    }

    /**
     * @param  array   $body_datas
     * [
     *     name => string|array|UploadedFileInterface,
     *     name => string|array|UploadedFileInterface,
     *     ...
     * ]
     * @return array array
     */
    protected static function createUploadedFiles(array $body_datas)
    {
        $uploadedFiles = [];
        foreach ($body_datas as $filed_name => $filed_data) {
            if (is_array($filed_data) && is_subclass_of(reset($filed_data), UploadedFileInterface::class)) {
                foreach ($filed_data as $file) {
                    $uploadedFiles[$filed_name][] = $file;
                }
            }

            if (is_subclass_of($filed_data, UploadedFileInterface::class)) {
                $uploadedFiles[$filed_name] = $body_datas[$filed_name];
            }
        }
        return $uploadedFiles;
    }
}
