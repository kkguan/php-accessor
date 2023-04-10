<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Processor\Method;

use PhpAccessor\DocReader\PhpDocReaderManager;
use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use ReflectionProperty;

class GetterMethod extends AbstractMethod
{
    protected string $name = 'getter';
    /** @var string[] */
    protected array $returnTypes = [];

    public function init()
    {
        $this->generateMethodName();
        $this->generateReturnTypes();
        $this->generateMethodComment();
    }

    private function generateMethodName()
    {
        $this->methodName = 'get' . $this->methodSuffix;
    }

    private function generateReturnTypes()
    {
        if (empty($this->fieldTypes)) {
            $this->returnTypes[] = 'mixed';
        } else {
            $this->returnTypes = $this->fieldTypes;
        }
    }

    public function generateMethodComment()
    {
        if (!empty($this->fieldTypes) && !\in_array('array', $this->fieldTypes)) {
            return;
        }

        $property = new ReflectionProperty($this->className, $this->fieldName);
        $propertyType = PhpDocReaderManager::getInstance()->getPropertyType($property);
        if (empty($propertyType)) {
            return;
        }

        $this->methodComment = <<<DOC
/**
    * @return {$propertyType}
    */
DOC;
    }

    public function buildMethod(): ClassMethod
    {
        $builder = new BuilderFactory();

        return $builder
            ->method($this->methodName)
            ->makePublic()
            ->setReturnType(implode('|', $this->returnTypes))
            ->setDocComment($this->methodComment)
            ->addStmt(
                new Return_(
                    $builder->propertyFetch($builder->var('this'), $this->fieldName)
                )
            )->getNode();
    }
}
