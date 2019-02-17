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
 * Proxy Class Method Builder
 *
 * <p>
 * By reflection, we'll create a fragment filled with all
 * public methods detected from the instance of ReflectionClass
 * belonging to target class.
 * </p>
 *
 * @author Timandes White <timandes@php.net>
 */
class ProxyClassMethodBuilder
{
    public function buildParameterList(\ReflectionMethod $rm, bool $withTypes = true): string
    {
        $parameters = $rm->getParameters();
        $parts = [];
        foreach ($parameters as $rp) {
            $a = [];
            if ($withTypes) {
                if ($rp->hasType()) {
                    $a[] = $rp->getType()->getName();
                }
                $pbr = $rp->isPassedByReference()?'&':'';
            }
            $a[] = ($pbr??'') . '$' . $rp->getName();
            $parts[] = implode(' ', $a);
        }

        return implode(', ', $parts);
    }

    public function buildOverridingMethod(\ReflectionMethod $rm): string
    {
        $parts = ['public function'];

        $methodName = $rm->getName();
        $parts[] = $methodName;
        $parts[] = '(' . $this->buildParameterList($rm) . ')';

        if ($rm->hasReturnType()) {
            $rt = $rm->getReturnType();
            $parts[] = ': ' . $rt->getName();
        }

        $parameterList = $this->buildParameterList($rm, false);
        $body = <<<EOT
{
    return \$this->callTargetMethod('{$methodName}', [{$parameterList}]);
}
EOT;

        return implode(' ', $parts) . $body;
    }

    /**
     * Build fragment with all public methods of given instance of ReflectionClass
     *
     * @return string generate fragment
     */
    public function build(\ReflectionClass $rc): string
    {
        $methods = $rc->getMethods();

        $methodsDef = '';
        foreach ($methods as $rm) {
            if (!$rm->isPublic()) {
                continue;
            }

            $methodsDef .= $this->buildOverridingMethod($rm);
        }
        return $methodsDef;
    }
}
