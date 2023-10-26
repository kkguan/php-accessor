<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor;

use PhpAccessor\Processor\Attribute\AttributeHandlerInterface;
use PhpAccessor\Processor\Attribute\DataHandler;
use PhpAccessor\Processor\Attribute\DefaultNullHandler;
use PhpAccessor\Processor\Attribute\OverlookHandler;
use PhpAccessor\Processor\Attribute\Parameter\AccessorTypeHandler;
use PhpAccessor\Processor\Attribute\Parameter\NamingConventionHandler;
use PhpAccessor\Processor\Attribute\Parameter\PrefixConventionHandler;
use PhpAccessor\Processor\Method\AccessorMethodType;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;

/**
 * @internal
 */
class AttributeProcessor
{
    private static array $registeredHandlers = [
        DataHandler::class,
        DefaultNullHandler::class,
        OverlookHandler::class,
    ];

    /**
     * @var AttributeHandlerInterface[]
     */
    private array $attributeHandlers = [];

    private NodeFinder $nodeFinder;

    public function __construct(Node $node)
    {
        $this->nodeFinder = new NodeFinder();
        $this->initHandlers();
        $this->parse($node);
    }

    /**
     * check if the class is pending to generate accessor methods.
     */
    public function isPending(): bool
    {
        return $this->getAttributeHandler(DataHandler::class)->isPending();
    }

    public function buildMethodNameFromField(string $fieldName, array $fieldType, string $accessorMethodType): string
    {
        $handler = $this->getAttributeHandler(DataHandler::class);
        return $handler->getParameterHandler(PrefixConventionHandler::class)->buildPrefix($fieldType, $accessorMethodType) .
            $handler->getParameterHandler(NamingConventionHandler::class)->buildName($fieldName);
    }

    /**
     * Whether to generate accessor methods for the property.
     */
    public function isIgnored(Property $property): bool
    {
        return $this->getAttributeHandler(OverlookHandler::class)->isOverlook($property);
    }

    public function isDefaultNull(string $fieldName): bool
    {
        return $this->getAttributeHandler(DefaultNullHandler::class)->isDefaultNull($fieldName);
    }

    public function shouldGenMethod(string $accessorMethodType): bool
    {
        return match ($accessorMethodType) {
            AccessorMethodType::GETTER => $this->getAttributeHandler(DataHandler::class)->getParameterHandler(AccessorTypeHandler::class)->shouldGenerateGetter(),
            AccessorMethodType::SETTER => $this->getAttributeHandler(DataHandler::class)->getParameterHandler(AccessorTypeHandler::class)->shouldGenerateSetter(),
            default => false,
        };
    }

    private function initHandlers(): void
    {
        foreach (self::$registeredHandlers as $handlerClassname) {
            $this->attributeHandlers[$handlerClassname] = new $handlerClassname();
        }
    }

    private function parse(Node $node): void
    {
        // Parse class attributes
        $this->parseClassAttributes($node);
        // Parse property attributes
        $this->parsePropertyAttributes($node);
    }

    private function parseClassAttributes(Node $node): void
    {
        /** @var Attribute[] $attributes */
        $attributes = $this->nodeFinder->findInstanceOf($node->attrGroups, Attribute::class);
        foreach ($this->attributeHandlers as $attributeHandler) {
            $this->processAttributesByHandler($attributeHandler, $attributes);
        }
    }

    private function parsePropertyAttributes(Node $node): void
    {
        /** @var Property[] $properties */
        $properties = $this->nodeFinder->findInstanceOf($node, Property::class);
        foreach ($this->attributeHandlers as $attributeHandler) {
            foreach ($properties as $property) {
                /** @var Attribute[] $attributes */
                $attributes = $this->nodeFinder->findInstanceOf($property->attrGroups, Attribute::class);
                $this->processAttributesByHandler($attributeHandler, $attributes, $property);
            }
        }
    }

    /**
     * @param Attribute[] $attributes
     */
    private function processAttributesByHandler(AttributeHandlerInterface $attributeHandler, array $attributes, ?Property $property = null): void
    {
        foreach ($attributes as $attribute) {
            $attributeHandler->processAttribute($attribute, $property);
        }
    }

    private function getAttributeHandler(string $handlerClassname): ?AttributeHandlerInterface
    {
        return $this->attributeHandlers[$handlerClassname] ?? null;
    }
}
