# Slim Api Bean

一個將 Slim 封裝起來的 library，簡化一些流程上的操作。

### Document

參考：[文件](DOC.md)

### Api Bean Example

建立一個 index.php

```php
use comoco\SlimApiBean\Bean as SlimApiBean;
use comoco\SlimApiBean\Handler\AbstractAction;

class HomeAction extends AbstractAction
{
    public function handle($request, $response, $args)
    {
        return "Welcome";
    }
}

class HelloAction extends AbstractAction
{
    public function handle($request, $response, $args)
    {
        $datas = $request->getParsedBody();
        $name = $datas['name'];
        return "hello, {$name}!";
    }
}

$apiBean = new SlimApiBean;
$apiBean->bindAction(['GET', 'POST'], '/', new HomeAction)
    ->bindAction(['POST'], '/hello', new HelloAction)
    ->run();
```

#### 啟動

```bash
php -S localhost:8089
```

#### 發出 request

GET

```bash
curl -X GET localhost:8089
```

輸出：

```
Welcome
```

POST

```bash
curl -d "name=Bob" -X POST localhost:8089/hello
```

輸出：

```
hello, Bob!
```

### Test Example

```php
use comoco\SlimApiBean\Bean as SlimApiBean;
use comoco\SlimApiBean\Handler\AbstractAction;

class HelloAction extends AbstractAction
{
    public function handle($request, $response, $args)
    {
        $datas = $request->getParsedBody();
        $name = $datas['name'];
        return "hello, {$name}!";
    }
}

$apiBean = new SlimApiBean;
$apiBean->bindAction(['POST'], '/hello', new HelloAction);

$response = $apiBean->dryRun('POST', '/hello', [
    'headers' => [
        'Content-Type' => 'application/json'
    ],
    'body' => [
        'name' => 'Bob'
    ]
]);

$this->assertEquals(200, $response->getStatusCode());
$this->assertEquals('hello, Bob!', (string) $response->getBody());
```
