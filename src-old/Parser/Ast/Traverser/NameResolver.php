<?php

namespace PHPatOld\Parser\Ast\Traverser;

use PHPatOld\Parser\Ast\Type\PhpType;
use PhpParser\Node\Name;
use PhpParser\NodeVisitor\NameResolver as PhpParserNameResolver;

class NameResolver extends PhpParserNameResolver
{
    protected function resolveName(Name $name, int $type): Name
    {
        if (PhpType::isBuiltinType($name->toString())) {
            return $name;
        }

        return parent::resolveName($name, $type);
    }
}
