<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute;

use PhpAccessor\Attribute\Data as AttributeData;
use PhpAccessor\Processor\Attribute\Parameter\AccessorTypeHandler;
use PhpAccessor\Processor\Attribute\Parameter\NamingConventionHandler;
use PhpAccessor\Processor\Attribute\Parameter\ParameterHandlerInterface;
use PhpAccessor\Processor\Attribute\Parameter\PrefixConventionHandler;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Property;

/**
 * @internal
 */
class DataHandler implements AttributeHandlerInterface
{
    private static array $registeredHandlers = [
        NamingConventionHandler::class,
        AccessorTypeHandler::class,
        PrefixConventionHandler::class,
    ];

    /**
     * @var ParameterHandlerInterface[]
     */
    private array $parameterHandlers = [];

    private bool $isPending = false;

    public function __construct()
    {
        foreach (self::$registeredHandlers as $handler) {
            $this->parameterHandlers[$handler] = new $handler();
        }
    }

    public function processAttribute(Attribute $attribute, ?Property $property = null): void
    {
        if ($attribute->name->toString() != AttributeData::class || $property != null) {
            return;
        }

        $this->isPending = true;
        foreach ($attribute->args as $arg) {
            foreach ($this->parameterHandlers as $parameterHandler) {
                $parameterHandler->processParameter($arg);
            }
        }
    }

    public function isPending(): bool
    {
        return $this->isPending;
    }

    public function getParameterHandler(string $handlerClassname): ParameterHandlerInterface
    {
        return $this->parameterHandlers[$handlerClassname];
    }
}
