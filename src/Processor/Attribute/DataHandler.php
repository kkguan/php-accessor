<?php

declare(strict_types=1);
/**
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpAccessor\Processor\Attribute;

class DataHandler implements AttributeHandlerInterface
{
    private NamingConvention $namingConvention;

    private AccessorType $accessorType;

    public function __construct()
    {
        $this->namingConvention = new NamingConvention();
        $this->accessorType = new AccessorType();
    }

    public function setParameter(object $parameter)
    {
        if ($parameter instanceof NamingConvention) {
            $this->namingConvention = $parameter;
        } elseif ($parameter instanceof AccessorType) {
            $this->accessorType = $parameter;
        }
    }

    public function setNamingConvention(NamingConvention $namingConvention): self
    {
        $this->namingConvention = $namingConvention;

        return $this;
    }

    public function getNamingConvention(): NamingConvention
    {
        return $this->namingConvention;
    }

    public function getAccessorType(): AccessorType
    {
        return $this->accessorType;
    }

    public function setAccessorType(AccessorType $accessorType): DataHandler
    {
        $this->accessorType = $accessorType;
        return $this;
    }
}
