<?php

namespace comoco\SlimApiBean\Utils;

use comoco\SlimApiBean\Utils\ResponseNormalizer;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response;

class ResponseNormalizerTest extends TestCase
{
    public function testConvertArray()
    {
        $old_response = new Response;
        $new_response = ResponseNormalizer::convert($old_response, [
            'name' => 'Bob',
            'age' => 18
        ]);

        $this->assertEquals([
            'name' => 'Bob',
            'age' => 18
        ], json_decode((string) $new_response->getBody(), true));
        $this->assertEquals(['application/json'], $new_response->getHeaders()['Content-Type']);
    }

    public function testConvertString()
    {
        $old_response = new Response;
        $new_response = ResponseNormalizer::convert($old_response, "hello, world");
        $this->assertEquals("hello, world", (string) $new_response->getBody());
    }

    public function testConvertPsr7Response()
    {
        $old_response = new Response;
        $main_response = new Response;
        $main_response = $main_response->withStatus(404);
        $new_response = ResponseNormalizer::convert($old_response, $main_response);
        $this->assertEquals($main_response, $new_response);
    }
}
