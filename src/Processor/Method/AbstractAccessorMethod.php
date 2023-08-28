<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Method;

use PhpAccessor\Processor\Method\Generator\GeneratorInterface;
use PhpParser\Node\Stmt;

abstract class AbstractAccessorMethod implements AccessorMethodInterface
{
    protected string $name = '';

    protected string $className;

    protected string $fieldName;

    /** @var string[] */
    protected array $fieldTypes;

    protected string $methodName;

    protected string $methodComment = '';

    protected FieldMetadata $fieldMetadata;

    /** @var GeneratorInterface[] */
    protected array $generators = [];

    /**
     * @var Stmt[]
     */
    protected array $body;

    /** @var string[] */
    protected array $returnTypes = [];

    public function jsonSerialize(): array
    {
        foreach ($this as $key => $value) {
            $json[$key] = $value;
        }

        if (isset($json['fieldMetadata'])) {
            unset($json['fieldMetadata']);
        }
        if (isset($json['generators'])) {
            unset($json['generators']);
        }
        if (isset($json['body'])) {
            unset($json['body']);
        }

        return $json;
    }

    public function setReturnTypes(array $returnTypes): static
    {
        $this->returnTypes = $returnTypes;
        return $this;
    }

    public function setBody(array $body): static
    {
        $this->body = $body;
        return $this;
    }

    public function addGenerator(GeneratorInterface $generator): void
    {
        $this->generators[] = $generator;
    }

    public function generate(): void
    {
        foreach ($this->generators as $generator) {
            $generator->generate($this->fieldMetadata, $this);
        }
    }

    public function setFieldMetadata(FieldMetadata $fieldMetadata): static
    {
        $this->fieldMetadata = $fieldMetadata;
        $this->fieldName = $fieldMetadata->getFieldName();
        $this->className = $fieldMetadata->getClassname();
        $this->fieldTypes = $fieldMetadata->getFieldTypes();

        return $this;
    }

    public function setMethodComment(string $methodComment): static
    {
        $this->methodComment = $methodComment;
        return $this;
    }

    public function setFieldTypes(array $fieldTypes): static
    {
        $this->fieldTypes = $fieldTypes;
        return $this;
    }

    public function setMethodName(string $methodName): static
    {
        $this->methodName = $methodName;
        return $this;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getFieldTypes(): array
    {
        return $this->fieldTypes;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMethodComment(): string
    {
        return $this->methodComment;
    }
}
