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
    public function buildParameter(\ReflectionParameter $rp, bool $defExp): string
    {
        $a = [];

        $vlal = '';
        if ($defExp) {
            if ($rp->hasType()) {
                $a[] = $this->getTypeRepresentative($rp);
            }
            $vlal = $rp->isVariadic()?'...':'';
        }
        $pbr = $rp->isPassedByReference()?'&':'';
        $a[] = $pbr . $vlal . '$' . $rp->getName();
        if ($defExp
                && $rp->isDefaultValueAvailable()) {
            if ($rp->isDefaultValueConstant()) {
                $default = $rp->getDefaultValueConstantName();
            } else {
                $default = var_export($rp->getDefaultValue(), true);
            }
            $a[] = '=' . $default;
        }

        return implode(' ', $a);
    }

    /**
     * @param bool $defExp Definition expression or not
     */
    public function buildParameterList(\ReflectionMethod $rm, bool $defExp = true): string
    {
        $parameters = $rm->getParameters();
        $parts = [];
        foreach ($parameters as $rp) {
            if (!$defExp && $rp->isVariadic()) {
                // Skip last variable-length parameter
                continue;
            }
            $parts[] = $this->buildParameter($rp, $defExp);
        }

        return implode(', ', $parts);
    }

    public function getTypeRepresentative(\ReflectionParameter $rp): string
    {
        $rt = $rp->getType();
        $returnValue = $this->getTypeName($rt);
        if ($returnValue{0} == '?') {
            return $returnValue;
        }
        if ($rp->allowsNull()) {
            return '?' . $returnValue;
        }
        return $returnValue;
    }

    public function getTypeName(\ReflectionType $rt): string
    {
        if (PHP_VERSION_ID < 70100) {
            return $rt->__toString();
        }

        $returnValue = $rt->getName();
        if ($returnValue{0} == '?') {
            return $returnValue;
        }
        if ($rt->allowsNull()) {
            return '?' . $returnValue;
        }
        return $returnValue;
    }

    public function buildOverridingMethod(\ReflectionMethod $rm): string
    {
        $parts = ['public function'];

        $methodName = $rm->getName();
        $parts[] = $methodName;
        $parts[] = '(' . $this->buildParameterList($rm) . ')';

        $returnClause = 'return ';
        if ($rm->hasReturnType()) {
            $rt = $rm->getReturnType();
            $tn = $this->getTypeName($rt);
            $parts[] = ': ' . $tn;

            if ($tn == 'void') {
                $returnClause = '';
            }
        }

        $parameterList = $this->buildParameterList($rm, false);

        // Variable-length parameter
        $possibleArrayMergeStatements = $this->buildArrayMergeForVariadicParameter($rm, 'args');

        $body = <<<EOT
{
    \$args = [{$parameterList}];
    {$possibleArrayMergeStatements}
    {$returnClause}\$this->callTargetMethod('{$methodName}', \$args);
}
EOT;

        return implode(' ', $parts) . $body;
    }

    public function buildArrayMergeForVariadicParameter(\ReflectionMethod $rm, string $varName): string
    {
        $parameters = $rm->getParameters();
        if (!$parameters) {
            return '';
        }

        $lastParmeter = array_pop($parameters);
        if (!$lastParmeter->isVariadic()) {
            return '';
        }

        return "\${$varName} = array_merge(\${$varName}, \${$lastParmeter->getName()});";
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
            if ($rm->isConstructor()
                    || $rm->isDestructor()
                    || $rm->isStatic()) {
                continue;
            }

            $methodsDef .= $this->buildOverridingMethod($rm);
        }
        return $methodsDef;
    }
}
