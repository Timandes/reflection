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
 * Proxy Class Builder
 *
 * @author Timandes White <timandes@php.net>
 */
class ProxyClassBuilder
{
    private $classMethodBuilder = null;

    public function __construct()
    {
        $this->classMethodBuilder = new ProxyClassMethodBuilder();
    }

    public function build(string $className, $target, array $interfaceNames = []): string
    {
        $targetRC = new \ReflectionClass($target);

        if ($interfaceNames) {
            $interfaces = [];
            foreach ($interfaceNames as $in) {
                $interfaces[] = new \ReflectionClass($in);
            }
        } else {
            $interfaces = $targetRC->getInterfaces();
            $interfaceNames = $targetRC->getInterfaceNames();
        }

        if ($interfaceNames) {
            $interfaceList = implode(', ', $interfaceNames);
            $implementsClause = " implements {$interfaceList}";

            $overridingMethodsDef = '';
            foreach ($interfaces as $interface) {
                $overridingMethodsDef .= $this->classMethodBuilder->build($interface);
            }
        } else {
            $implementsClause = '';

            $overridingMethodsDef = $this->classMethodBuilder->build($targetRC);
        }

        $classDefLine = "class {$className} extends AbstractProxy{$implementsClause}";

        $interfaceArrayClause = var_export($interfaceNames, true);
        $classDef = <<<EOT
use \Timandes\Reflection\AbstractProxy;

{$classDefLine}
{
    private \$target = null;
    private \$callback = null;

    public function __construct(\$target, callable \$callback)
    {
        \$this->target = \$target;
        \$this->callback = \$callback;
    }

    protected function getCallback()
    {
        return \$this->callback;
    }

    protected function getTarget()
    {
        return \$this->target;
    }

    protected function getInterfaces()
    {
        return {$interfaceArrayClause};
    }

    {$overridingMethodsDef}
}
EOT;
        return $classDef;
    }
}
