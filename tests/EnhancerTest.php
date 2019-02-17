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

use Timandes\Reflection\Enhancer;

class EnhancerTest extends \PHPUnit\Framework\TestCase
{
    public function testEnhance()
    {
        $enhancer = new Enhancer();
        $enhancer->setSuperClass(BaseFoo::class);
        $enhancer->setCallback(function($target, \ReflectionMethod $method, array $args) {
            echo 'Before invoking' . PHP_EOL;
            return $method->invokeArgs($target, $args);
        });
        $enhancedFoo = $enhancer->create();
        $actual = $enhancedFoo->bar();
        $this->assertEquals('bar', $actual);

        $expected = <<<'EOT'
Before invoking
BaseFoo::bar()

EOT;
        $this->expectOutputString($expected);
        $this->assertTrue($enhancedFoo instanceof BaseFoo);
    }

    public function testProxy()
    {
        $foo = new BaseFoo();
        $fooProxy = Enhancer::createInstance(BaseFoo::class, function($object, \ReflectionMethod $method, array $args) use($foo) {
            echo 'Before invoking' . PHP_EOL;
            return $method->invokeArgs($foo, $args);
        });
        $actual = $fooProxy->bar();
        $this->assertEquals('bar', $actual);

        $expected = <<<'EOT'
Before invoking
BaseFoo::bar()

EOT;
        $this->expectOutputString($expected);
        $this->assertTrue($fooProxy instanceof BaseFoo);
    }
}

class BaseFoo
{
    public function bar()
    {
        echo 'BaseFoo::bar()' . PHP_EOL;
        return 'bar';
    }
}
