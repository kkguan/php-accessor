<?php

/*
 * This file is part of the PhpAccessor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpAccessor\Meta;

use DateTime;
use JsonSerializable;
use PhpAccessor\Method\AccessorMethod;

class ClassMetadata implements JsonSerializable
{
    protected string $project;

    protected string $classname;

    /** @var AccessorMethod[] */
    protected array $methods = [];

    protected DateTime $updateTime;

    public function __construct(string $project, string $classname)
    {
        $this->classname = $classname;
        $this->project = $project;
        $this->updateTime = new DateTime();
    }

    public function addMethod(AccessorMethod $method): static
    {
        $this->methods[] = $method;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $json = [];
        foreach ($this as $key => $value) {
            if ($value instanceof DateTime) {
                /* @var DateTime $value */
                $json[$key] = $value->format('Y-m-d H:i:s');
            } else {
                $json[$key] = $value;
            }
        }

        return $json;
    }
}
