<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor;

use PhpAccessor\Processor\Method\AccessorMethodInterface;
use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Trait_;

class TraitAccessor
{
    protected string $className;

    /** @var AccessorMethodInterface[] */
    private array $accessorMethods = [];

    public function __construct(string $classShortName)
    {
        $this->className = '_Proxy' . str_replace('\\', '_', $classShortName) . 'Accessor';
    }

    public function addAccessorMethod(AccessorMethodInterface $abstractMethod): static
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
