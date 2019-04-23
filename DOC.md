# Documentation

※ 以下所有的 `$request` 與 `$response` 可以使用的函數參考 [PSR-7](https://github.com/php-fig/http-message) 的 RequestInterface 與 ResponseInterface

### 建立

使用預設的設定

```php
use comoco\SlimApiBean\Bean as SlimApiBean;

$apiBean = new SlimApiBean;
```

使用其他 slim 的設定 (可用的設定參考Slim文件：[Application Configuration](http://www.slimframework.com/docs/v3/objects/application.html#application-configuration))

```php
use comoco\SlimApiBean\Bean as SlimApiBean;

$config = [
    'settings' => [
        'displayErrorDetails' => true,
    ]
];
$apiBean = new SlimApiBean($config);
```

### 啟動

```php
$apiBean->run();
```

----

## class 介紹

### Action

榜定指定 Url 處理 request 與 response

可使用的 Pattern 參考 [Router](http://www.slimframework.com/docs/v3/objects/router.html)

※ handle 函數 return 的 type 如果是 object 或 array，Content-Type 會自動轉換成 `application/json; charset=utf-8`，其他資料型態會將直接將資料寫進 response 的 body

Example

```php
use comoco\SlimApiBean\Handler\AbstractAction;

class BookAction extends AbstractAction
{
    public function handle($request, $response, $args)
    {
        $id = $args['id'];
        // ...
    }
}

$apiBean->bindAction(['GET'], '/books/{id}', new BookAction);
$apiBean->run();
```

### Middleware

在指定的 URL 才使用該 Middleware

```php
use comoco\SlimApiBean\Handler\AbstractMiddleware;

class MyMiddleware extends AbstractMiddleware
{
    public function handle($request, $response, $next)
    {
        // BEFORE: do something...
        $response = $next($request, $response);
        // AFTER: do something...

        return $response;
    }
}

$apiBean->bindAction(['GET'], '/books/{id}', $action, [
    new MyMiddleware
]);
```

全域的 Middleware

```php
use comoco\SlimApiBean\Handler\AbstractMiddleware;

class MyMiddleware extends AbstractMiddleware
{
    public function handle($request, $response, $next)
    {
        // BEFORE: do something...
        $response = $next($request, $response);
        // AFTER: do something...

        return $response;
    }
}

$apiBean->addGlobalMiddleware([new MyMiddleware]);
```

### Exception Hander

設定預設 的 Exception Handler (如果沒有設定會使用 slim 預設的 error handler)

```php
use comoco\SlimApiBean\Handler\AbstractExceptionHandler;

class DefaultErrorHandler extends AbstractExceptionHandler
{
    public function handle($request, $response, $exception)
    {
        return $response->withStatus(500)
            ->write("something wrong...");
    }
}
$apiBean->setDefaultExceptionHandler(new DefaultErrorHandler);
```

設定特定 Exception 使用的 Exception Handler

```php
use comoco\SlimApiBean\Handler\AbstractExceptionHandler;

class MyErrorHandler extends AbstractExceptionHandler
{
    public function handle($request, $response, $exception)
    {
        return $response->withStatus(503)
            ->write("Unavailable");
    }
}
$apiBean->bindExceptionHandler([InvalidArgumentException::class, DomainException::class], new MyErrorHandler);
```

### Page Not Found Handler

```php
use comoco\SlimApiBean\Handler\AbstractNotFoundHandler;

class PageNotFoundHandler extends AbstractNotFoundHandler
{
    public function handle($request, $response)
    {
        return $response->withStatus(404)
            ->write("page not found");
    }
}
$apiBean->setPageNotFoundHandler(new PageNotFoundHandler);
```

### Method Not Allowed

```php
use comoco\SlimApiBean\Handler\AbstractNotAllowedHandler;

class MethodNotAllowedHandler extends AbstractNotAllowedHandler
{
    public function handle($request, $response, $methods)
    {
        return $response->withStatus(405)
            ->write("not allow");
    }
}
$apiBean->setNotAllowedHandler(new MethodNotAllowedHandler);
```

### Runtime Error Handler

```php
use comoco\SlimApiBean\Handler\AbstractRuntimeErrorHandler;

class RuntimeErrorHandler extends AbstractRuntimeErrorHandler
{
    public function handle($request, $response, $error)
    {
        return $response->withStatus(500)
            ->write("error");
    }
}
$apiBean->setRuntimeErrorHandler(new RuntimeErrorHandler);
```
