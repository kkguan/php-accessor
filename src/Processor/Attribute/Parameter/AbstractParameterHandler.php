<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute\Parameter;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;

abstract class AbstractParameterHandler implements ParameterHandlerInterface
{
    protected mixed $config;

    public function processParameter(Arg $parameter): void
    {
        $parameterName = $parameter->name->name;
        $parameterValue = $parameter->value;

        if (! $this->shouldProcess($parameterName, $parameterValue)
            || ! ($parameterValue instanceof ClassConstFetch)
            || ! ($parameterValue->name instanceof Identifier)) {
            return;
        }

        $this->config = $this->getConfigValueFromClassConstants($parameterValue->name->name) ?? $this->config;
    }

    /**
     * Determines whether the given parameter should be processed.
     *
     * This method should be implemented by subclasses to provide specific
     * logic for determining whether a parameter should be processed based
     * on its name and value.
     *
     * @param string $parameterName the name of the parameter
     * @param Expr $parameterValue the value of the parameter
     * @return bool returns true if the parameter should be processed, false otherwise
     */
    abstract protected function shouldProcess(string $parameterName, Expr $parameterValue): bool;

    /**
     * Returns the class name of the specific parameter handler.
     *
     * This method should be implemented by subclasses to provide the specific
     * class name for the parameter handler. This class name is used in the
     * `getConfigValueFromClassConstants` method to fetch the configuration value.
     *
     * @return string the class name of the specific parameter handler
     */
    abstract protected function getClassName(): string;

    private function getConfigValueFromClassConstants(string $name): mixed
    {
        return defined($this->getClassName() . '::' . $name)
            ? constant($this->getClassName() . '::' . $name)
            : null;
    }
}
