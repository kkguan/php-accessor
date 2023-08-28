<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor;

use PhpAccessor\Processor\Attribute\AttributeHandlerInterface;
use PhpAccessor\Processor\Attribute\Builder\AttributeBuilderInterface;
use PhpAccessor\Processor\Attribute\Builder\DataBuilder;
use PhpAccessor\Processor\Attribute\Builder\DefaultNullBuilder;
use PhpAccessor\Processor\Attribute\Builder\OverlookBuilder;
use PhpAccessor\Processor\Attribute\DataHandler;
use PhpAccessor\Processor\Attribute\DefaultNullHandler;
use PhpAccessor\Processor\Attribute\OverlookHandler;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;

class AttributeProcessor
{
    /**
     * @var AttributeBuilderInterface[]
     */
    private array $classAttributeBuilders = [
        DataHandler::class => DataBuilder::class,
        DefaultNullHandler::class => DefaultNullBuilder::class,
    ];

    /**
     * @var AttributeBuilderInterface[]
     */
    private array $propertyAttributeBuilders = [
        OverlookHandler::class => OverlookBuilder::class,
        DefaultNullHandler::class => DefaultNullBuilder::class,
    ];

    /**
     * @var AttributeHandlerInterface[]
     */
    private array $classAttributeHandlers = [];

    /**
     * @var AttributeHandlerInterface[][]
     */
    private array $propertyAttributeHandlers = [];

    private NodeFinder $nodeFinder;

    public function __construct(Node $node)
    {
        $this->nodeFinder = new NodeFinder();

        $this->parseClassAttribute($node);
        $this->parsePropertyAttribute($node);
    }

    public function isPending(): bool
    {
        return $this->getClassAttributeHandler(DataHandler::class) != null;
    }

    public function buildMethodSuffixFromField(string $fieldName): string
    {
        $handler = $this->getClassAttributeHandler(DataHandler::class);
        return $handler->getNamingConvention()->buildName($fieldName);
    }

    public function ignoreProperty(Property $property): bool
    {
        $ignore = true;
        foreach ($property->props as $prop) {
            if (! $this->getPropertyAttributeHandler($prop->name->name, OverlookHandler::class)?->isOverlook()) {
                $ignore = false;
                break;
            }
        }

        return $ignore;
    }

    public function isDefaultNull(string $fieldName): bool
    {
        if ($this->getClassAttributeHandler(DefaultNullHandler::class)) {
            return true;
        }

        if ($this->getPropertyAttributeHandler($fieldName, DefaultNullHandler::class)) {
            return true;
        }

        return false;
    }

    public function shouldGenerateGetter(): bool
    {
        return $this->getClassAttributeHandler(DataHandler::class)->getAccessorType()->shouldGenerateGetter();
    }

    public function shouldGenerateSetter(): bool
    {
        return $this->getClassAttributeHandler(DataHandler::class)->getAccessorType()->shouldGenerateSetter();
    }

    private function getClassAttributeHandler(string $handlerClassname): ?AttributeHandlerInterface
    {
        return $this->classAttributeHandlers[$handlerClassname] ?? null;
    }

    private function getPropertyAttributeHandler(string $propertyName, string $handlerClassname): ?AttributeHandlerInterface
    {
        return $this->propertyAttributeHandlers[$propertyName][$handlerClassname] ?? null;
    }

    private function parseClassAttribute(Node $node): void
    {
        /** @var Attribute[] $attributes */
        $attributes = $this->nodeFinder->findInstanceOf($node->attrGroups, Attribute::class);
        foreach ($this->classAttributeBuilders as $handlerClassname => $attributeClassBuilder) {
            $builder = new $attributeClassBuilder();
            foreach ($attributes as $attribute) {
                $handler = $builder->setAttribute($attribute)->build();
                if ($handler == null) {
                    continue;
                }

                $this->classAttributeHandlers[$handlerClassname] = $handler;
                break;
            }
        }
    }

    private function parsePropertyAttribute(Node $node): void
    {
        /** @var Property[] $properties */
        $properties = $this->nodeFinder->findInstanceOf($node, Property::class);
        if (empty($properties)) {
            return;
        }

        foreach ($this->propertyAttributeBuilders as $handlerClassname => $attributeClassBuilder) {
            $builder = new $attributeClassBuilder();
            foreach ($properties as $property) {
                /** @var Attribute[] $attributes */
                $attributes = $this->nodeFinder->findInstanceOf($property->attrGroups, Attribute::class);
                foreach ($attributes as $attribute) {
                    $handler = $builder->setAttribute($attribute)->build();
                    if ($handler == null) {
                        continue;
                    }

                    foreach ($property->props as $prop) {
                        $this->propertyAttributeHandlers[$prop->name->name][$handlerClassname] = $handler;
                    }
                }
            }
        }
    }
}
