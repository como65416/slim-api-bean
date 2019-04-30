<?php

namespace comoco\SlimApiBean\Utils;

use comoco\SlimApiBean\Utils\SlimAppUtil;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

class SlimAppUtilTest extends TestCase
{
    public function testCreateMockRequestWithFile()
    {
        $uploaded_file1 = $this->createMock(UploadedFileInterface::class);
        $uploaded_file1->method('getSize')->willReturn(1);
        $uploaded_file2 = $this->createMock(UploadedFileInterface::class);
        $uploaded_file2->method('getSize')->willReturn(2);
        $uploaded_file3 = $this->createMock(UploadedFileInterface::class);
        $uploaded_file3->method('getSize')->willReturn(3);

        $request = SlimAppUtil::createMockRequest('POST', '/user?id=40', [
            'headers' => [
                'Authorization' => 'Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==',
                'Accept-Charset' => 'UTF-8'
            ],
            'body' => [
                'name' => 'Bob',
                'age' => 18,
                'photo' => $uploaded_file1,
                'attachments' => [
                    $uploaded_file2,
                    $uploaded_file3
                ]
            ]
        ]);

        $this->assertEquals([
            'name' => 'Bob',
            'age' => 18,
        ], $request->getParsedBody());
        $this->assertEquals(['Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ=='], $request->getHeader('Authorization'));
        $this->assertEquals(['UTF-8'], $request->getHeader('Accept-Charset'));
        $this->assertEquals(1, $request->getUploadedFiles()['photo']->getSize());
        $this->assertEquals([2, 3], array_map(function ($uploaded_file) {
            return $uploaded_file->getSize();
        }, $request->getUploadedFiles()['attachments']));
        $this->assertEquals(['id' => 40], $request->getQueryParams());
        $this->assertEquals('/user', $request->getUri()->getPath());
        $this->assertEquals('POST', $request->getMethod());
    }

    public function testCreateMockJsonRequest()
    {
        $request = SlimAppUtil::createMockRequest('POST', '/user?id=40', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => [
                'name' => 'Bob',
                'age' => 18
            ]
        ]);
        $this->assertEquals([
            'name' => 'Bob',
            'age' => 18
        ], json_decode((string) $request->getBody(), true));
    }

    public function testCreateMockFormUrlencodedRequest()
    {
        $request = SlimAppUtil::createMockRequest('POST', '/user?id=40', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'body' => [
                'name' => 'Bob',
                'age' => 18,
                'datas' => [
                    1,
                    2,
                    3
                ]
            ]
        ]);
        $this->assertEquals('name=Bob&age=18&datas%5B%5D=1&datas%5B%5D=2&datas%5B%5D=3', (string) $request->getBody());
    }
}
