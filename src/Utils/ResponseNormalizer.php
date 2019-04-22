<?php

namespace comoco\SlimApiBean\Utils;

use Psr\Http\Message\ResponseInterface;

class ResponseNormalizer
{
    /**
     * convert different data type to Response object
     *
     * @param  ResponseInterface $response
     * @param  mixed $data
     * @return ResponseInterface
     */
    public static function convert(ResponseInterface &$response, $data)
    {
        $data_type = gettype($data);
        if ($data_type == 'object' && is_subclass_of($data, ResponseInterface::class)) {
            return $data;
        }

        if (in_array($data_type, ['string', 'integer', 'double'])) {
            $response->write($data);
        } elseif (in_array($data_type, ['array', 'object'])) {
            $response = $response->withHeader('Content-Type', 'application/json');
            $response = $response->write(json_encode($data));
        }
        return $response;
    }
}
