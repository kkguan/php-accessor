<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Processor\Method;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;

class SetterMethod extends AbstractMethod
{
    protected string $name = 'setter';
    /** @var string[] */
    private array $parameterTypes = [];

    public function init()
    {
        $this->generateMethodName();
        $this->generateParameterTypes();
    }

    private function generateMethodName()
    {
        $this->methodName = 'set' . $this->methodSuffix;
    }

    private function generateParameterTypes()
    {
        if (empty($this->fieldTypes)) {
            $this->parameterTypes[] = 'mixed';
        } else {
            $this->parameterTypes = $this->fieldTypes;
        }
    }

    public function buildMethod(): ClassMethod
    {
        $builder = new BuilderFactory();
        $param = $builder->param($this->fieldName);
        $param->setType(implode('|', $this->parameterTypes));
        $exp = new Expression(
            new Assign(
                $builder->propertyFetch($builder->var('this'), $this->fieldName),
                $builder->var($this->fieldName)
            )
        );

        return $builder
            ->method($this->methodName)
            ->makePublic()
            ->addParam($param)
            ->setReturnType('static')
            ->addStmt($exp)
            ->addStmt(new Return_($builder->var('this')))
            ->getNode();
    }
}
