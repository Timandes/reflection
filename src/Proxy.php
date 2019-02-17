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
 * Dynamic Proxy
 *
 * @author Timandes White <timandes@php.net>
 */
class Proxy
{
    /**
     * Create a proxy instance
     *
     * Parameter $interfaces is an array filled with interface names,
     * which will restrict total count of methods in new proxy instance.
     *
     * If a method in an interface that target is not implemented is called,
     * a ReflectionException(Trying to invoke abstract method) will be thrown
     * when trying to invoke that method in an callback.
     * This feature can be used to extend functionality of a existing class.
     *
     * If no interface is given, interfaces that implemented by proxy target
     * will be used.
     *
     * If proxy target does not implement any interface, all public methods
     * will be used by proxy instance.
     *
     * @param object $target Proxy target
     * @param array $interfaces Interfaces that proxy will implementing
     * @param callable $callback Proxy callback
     * @return object Proxy instance
     */
    public static function newProxyInstance($target, array $interfaces, callable $callback)
    {
        $className = 'AnonymousClass' . uniqid();
        $classBuilder = new ProxyClassBuilder();
        $classDef = $classBuilder->build($className, $target, $interfaces);
        eval($classDef);

        return new $className($target, $callback);
    }
}
