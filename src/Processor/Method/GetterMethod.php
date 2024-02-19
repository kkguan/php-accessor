<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Method;

use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\ClassMethod;

class GetterMethod extends AbstractAccessorMethod
{
    protected string $name = AccessorMethodType::GETTER;

    public function buildMethod(): ClassMethod
    {
        $builder = new BuilderFactory();
        $method = $builder->method($this->methodName)
            ->makePublic()
            ->setReturnType(implode('|', $this->returnTypes))
            ->setDocComment($this->methodComment)
            ->addStmts($this->body);

        return $method->getNode();
    }
}
