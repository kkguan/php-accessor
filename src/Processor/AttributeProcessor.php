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

    public function shouldProcess(): bool
    {
        return $this->getAttributeHandler(DataHandler::class)->isPending();
    }

    /**
     * Builds a method name from the given field name, field type, and accessor method type.
     *
     * @param string $fieldName the name of the field
     * @param array $fieldType the type of the field
     * @param string $accessorMethodType the type of the accessor method
     *
     * @return string the built method name
     */
    public function buildMethodNameFromField(string $fieldName, array $fieldType, string $accessorMethodType): string
    {
        $dataHandler = $this->getAttributeHandler(DataHandler::class);
        $prefix = $dataHandler->getParameterHandler(PrefixConventionHandler::class)->buildPrefix($fieldType, $accessorMethodType);
        $name = $dataHandler->getParameterHandler(NamingConventionHandler::class)->buildName($fieldName);

        return $prefix . $name;
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

    /**
     * Parses the given node.
     *
     * This method is responsible for parsing the given node. It does this by calling the parseAttributes method twice.
     * The first call processes the attributes of the node, and the second call processes the properties of the node.
     *
     * @param Node $node the node to parse
     */
    private function parse(Node $node): void
    {
        $this->parseAttributes($node->attrGroups, Attribute::class);
        $this->parseAttributes($node, Property::class);
    }

    private function parseAttributes(Node|array $nodes, string $class): void
    {
        $foundNodes = $this->nodeFinder->findInstanceOf($nodes, $class);
        foreach ($this->attributeHandlers as $attributeHandler) {
            $attributeHandler->processAttributes($foundNodes);
        }
    }

    private function getAttributeHandler(string $handlerClassname): ?AttributeHandlerInterface
    {
        return $this->attributeHandlers[$handlerClassname] ?? null;
    }
}
