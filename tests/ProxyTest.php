<?php
/*
   Copyright 2019 Timandes White

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/

use Timandes\Reflection\Proxy;

class ProxyTest extends \PHPUnit\Framework\TestCase
{
    public function testWithAllInterfaces()
    {
        $fooBar = new DefaultFooBar();
        $fooBarProxy = Proxy::newProxyInstance($fooBar, [Foo::class, Bar::class], function($proxy, \ReflectionMethod $method, array $args) use($fooBar) {
            echo "Before invoking" . PHP_EOL;
            return $method->invokeArgs($fooBar, $args);
        });
        $actual = $fooBarProxy->foo();
        $this->assertEquals('foo', $actual);
        $v = $w = 0;
        $actual = $fooBarProxy->bar(2, $v, $w);
        $this->assertEquals('bar', $actual);
        $this->assertEquals(1, $v);
        $this->assertEquals(1, $w);

        $expected = <<<'EOT'
Before invoking
DefaultFooBar::foo()
Before invoking
DefaultFooBar::bar(2, 1)

EOT;
        $this->expectOutputString($expected);
        $this->assertTrue($fooBar instanceof Foo);
        $this->assertTrue($fooBar instanceof Bar);
    }

    public function testWithoutInterface()
    {
        $baz = new SomeBaz();
        $bazProxy = Proxy::newProxyInstance($baz, [], function($proxy, \ReflectionMethod $method, array $args) use($baz) {
            echo "Before invoking" . PHP_EOL;
            return $method->invokeArgs($baz, $args);
        });
        $actual = $bazProxy->baz();
        $this->assertEquals('baz', $actual);

        $expected = <<<'EOT'
Before invoking
SomeBaz::baz()

EOT;
        $this->expectOutputString($expected);
        $this->assertFalse($baz instanceof Foo);
        $this->assertFalse($baz instanceof Bar);
    }
}

interface Foo
{
    public function foo();
}

interface Bar
{
    public function bar(int $i = 1, int &$j = 2, int &...$l): string;
}

class DefaultFooBar implements Foo, Bar
{
    public function foo()
    {
        echo 'DefaultFooBar::foo()' . PHP_EOL;
        return 'foo';
    }
    public function bar(int $i = 1, int &$j = 2, int &...$l): string
    {
        if (isset($l[0])) {
            $l[0]++;
        }
        $j++;
        echo 'DefaultFooBar::bar(' . $i . ', ' . $j . ')' . PHP_EOL;
        return 'bar';
    }
}

class SomeBaz
{
    public function baz()
    {
        echo 'SomeBaz::baz()' . PHP_EOL;
        return 'baz';
    }
}
