# Timandes's Reflection Library

## JDK-like Dynamic Proxy

```php
interface Foo
{
    public function foo();
}

interface Bar
{
    public function bar();
}

class DefaultFooBar implements Foo, Bar
{
    public function foo()
    {
        echo 'DefaultFooBar::foo()' . PHP_EOL;
        return 'foo';
    }
    public function bar()
    {
        echo 'DefaultFooBar::bar()' . PHP_EOL;
        return 'bar';
    }
}

use Timandes\Reflection\Proxy;

$fooBar = new DefaultFooBar();
$fooBarProxy = Proxy::newProxyInstance($fooBar, [Foo::class, Bar::class], function($proxy, \ReflectionMethod $method, array $args) use($fooBar) {
    echo "Before invoking" . PHP_EOL;
    return $method->invokeArgs($fooBar, $args);
});
$fooBarProxy->foo();
```

Output:

```
Before invoking
DefaultFooBar::foo()
```



## CGLib-like Dynamic Proxy

```php
class BaseFoo
{
    public function bar()
    {
        echo 'BaseFoo::bar()' . PHP_EOL;
        return 'bar';
    }
}

use Timandes\Reflection\Enhancer;

$foo = new BaseFoo();
$fooProxy = Enhancer::createInstance(BaseFoo::class, function($object, \ReflectionMethod $method, array $args) use($foo) {
    echo 'Before invoking' . PHP_EOL;
    return $method->invokeArgs($foo, $args);
});
$fooProxy->bar();
```

Output:

```
Before invoking
BaseFoo::bar()
```

