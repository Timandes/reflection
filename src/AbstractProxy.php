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
 * Abstract Proxy
 *
 * <p>
 * All dynamic proxy class extends this abstract class.
 * </p>
 *
 * @author Timandes White <timandes@php.net>
 */
abstract class AbstractProxy
{
    protected function callTargetMethod(string $name, array $args)
    {
        $target = $this->getTarget();
        $rc = new \ReflectionClass($target);
        $method = $this->findMethodByReflectionClass($rc, $name);
        if ($method) {
            return $this->callBack($method, $args);
        }

        $interfaces = $this->getInterfaces();
        foreach ($interfaces as $interface) {
            $method = $this->findMethod($interface, $name);
            if ($method) {
                return $this->callBack($method, $args);
            }
        }

        throw new \BadMethodCallException();
    }

    private function callBack(\ReflectionMethod $rm, array $args)
    {
        $callback = $this->getCallback();
        return call_user_func($callback, $this, $rm, $args);
    }

    private function findMethod(string $interface, string $name)
    {
        $rc = new \ReflectionClass($interface);
        return $this->findMethodByReflectionClass($rc, $name);
    }

    private function findMethodByReflectionClass(\ReflectionClass $rc, string $name)
    {
        try {
            return $rc->getMethod($name);
        } catch (\ReflectionException $e) {
            return null;
        }
    }

    abstract protected function getCallback();
    abstract protected function getInterfaces();
    abstract protected function getTarget();
}
