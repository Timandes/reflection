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

class Enhancer71Test extends \PHPUnit\Framework\TestCase
{
    public function testProxy()
    {
        $baz = new BaseBaz();
        $bazProxy = Enhancer::createInstance(BaseBaz::class, function($object, \ReflectionMethod $method, array $args) use($baz) {
            echo 'Before invoking' . PHP_EOL;
            return $method->invokeArgs($baz, $args);
        });
        $actual = $bazProxy->bar(null);
        $this->assertEquals('bar', $actual);

        $expected = <<<'EOT'
Before invoking
BaseBaz::bar((nil))

EOT;
        $this->expectOutputString($expected);
        $this->assertTrue($bazProxy instanceof BaseBaz);
    }
}

class BaseBaz
{
    public function bar(?string $s, string $v = null)
    {
        if (is_null($s)) {
            $s = '(nil)';
        }
        echo 'BaseBaz::bar(' . $s . ')' . PHP_EOL;
        return 'bar';
    }
}
