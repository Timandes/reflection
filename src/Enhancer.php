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

namespace Timandes\Reflection;

/**
 * Class Enhancer
 *
 * @author Timandes White <timandes@php.net>
 */
class Enhancer
{
    private $superClassName = '';
    private $callback = null;
    private $classMethodBuilder = null;

    public function __construct()
    {
        $this->classMethodBuilder = new ProxyClassMethodBuilder();
    }

    public function setSuperClass(string $className)
    {
        $this->superClassName = $className;
    }

    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    public function create()
    {
        $className = 'AnonymousClass' . uniqid();

        $superClassRC = new \ReflectionClass($this->superClassName);
        $overridingMethodsDef = $this->classMethodBuilder->build($superClassRC);

        $classDef = <<<EOT
class {$className} extends {$this->superClassName}
{
    private \$callback = null;

    public function __construct(\$callback)
    {
        \$this->callback = \$callback;
    }

    public function callTargetMethod(\$name, \$args)
    {
        \$rc = new \ReflectionClass('{$this->superClassName}');
        \$rm = \$rc->getMethod(\$name);
        if (!\$rm) {
            throw new \BadMethodCallException();
        }
        if (!\$rm->isPublic()) {
            throw new \BadMethodCallException();
        }

        if (\$this->callback instanceof MethodInterceptor) {
            return \$this->callback->intercept(\$this, \$rm, \$args);
        } else {
            return call_user_func(\$this->callback, \$this, \$rm, \$args);
        }
    }

    {$overridingMethodsDef}
}
EOT;
        eval($classDef);

        return new $className($this->callback);
    }

    public static function createInstance(string $superClassName, callable $callback)
    {
        $enhancer = new Enhancer();
        $enhancer->setSuperClass($superClassName);
        $enhancer->setCallback($callback);
        return $enhancer->create();
    }
}
