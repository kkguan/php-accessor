<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Processor;

use PhpAccessor\Processor\Method\AccessorMethod;
use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Trait_;

class TraitAccessor
{
    protected string $className;

    /** @var AccessorMethod[] */
    private array $accessorMethods = [];

    public function __construct(string $classShortName)
    {
        $this->className = '_Proxy' . $classShortName . 'Accessor';
    }

    public function addAccessorMethod(AccessorMethod $abstractMethod): static
    {
        $this->accessorMethods[] = $abstractMethod;

        return $this;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function buildTrait(): Trait_
    {
        $builder = new BuilderFactory();
        $trait = $builder->trait($this->className);
        foreach ($this->accessorMethods as $accessorMethod) {
            $trait->addStmt($accessorMethod->buildMethod());
        }

        return $trait->getNode();
    }
}
