<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute\Builder;

use PhpAccessor\Attribute\Data as AttributeData;
use PhpAccessor\Processor\Attribute\DataHandler;
use PhpParser\Node\Attribute;

class DataBuilder implements AttributeBuilderInterface
{
    private Attribute $attribute;

    /** @var AttributeParameterBuilder[] */
    private array $attributeParameterBuilders = [];

    public function __construct()
    {
        $this->attributeParameterBuilders[] = new NamingConventionBuilder();
        $this->attributeParameterBuilders[] = new AccessorTypeBuilder();
    }

    public function setAttribute(Attribute $attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function build(): ?DataHandler
    {
        if ($this->attribute->name->toString() != AttributeData::class) {
            return null;
        }

        $data = new DataHandler();
        foreach ($this->attribute->args as $arg) {
            foreach ($this->attributeParameterBuilders as $attributeParameterBuilder) {
                if ($arg->name->name != $attributeParameterBuilder->getName()) {
                    continue;
                }

                $attributeParameterBuilder->prepare($arg->value);
                $data->setParameter($attributeParameterBuilder->build());
            }
        }

        return $data;
    }
}
