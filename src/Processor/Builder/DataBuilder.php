<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Processor\Builder;

use PhpAccessor\Attribute\Data as AttributeData;
use PhpAccessor\Processor\Attribute\Data;
use PhpParser\Node\Attribute;

class DataBuilder
{
    private Attribute $attribute;

    public function setAttribute(Attribute $attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function build(): ?Data
    {
        if (AttributeData::class != $this->attribute->name->toString()) {
            return null;
        }

        $data = new Data();
        $namingConventionBuilder = new NamingConventionBuilder();
        foreach ($this->attribute->args as $arg) {
            switch ($arg->name->name) {
                case 'namingConvention':
                    $namingConventionBuilder->setClassConstFetch($arg->value);
                    break;
                default:
                    break;
            }
        }
        $data->setNamingConvention($namingConventionBuilder->build());

        return $data;
    }
}
