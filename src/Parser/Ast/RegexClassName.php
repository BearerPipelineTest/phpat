<?php

namespace PhpAT\Parser\Ast;

use PhpAT\Selector\Storage\MatchingStorage;

class RegexClassName implements ClassLike
{
    private string $originalExpression;
    private string $regex;

    public function __construct(string $expression)
    {
        $this->originalExpression = $expression;
        $this->regex = '/^'
            . str_replace(
                '*',
                '.*',
                preg_replace_callback(
                    '/([^*])/',
                    function ($m) {
                        return preg_quote($m[0], '/');
                    },
                    $expression
                )
            )
            . '$/i';
    }

    public function matches(string $name): bool
    {
        return (bool) preg_match($this->regex, $name);
    }

    public function getMatchingNodes(array $nodes): array
    {
        $known = MatchingStorage::getMatchingNodes($this->toString());
        if ($known !== null) {
            return $known;
        }

        $found = array_map(fn($n) => $nodes[$n], preg_grep($this->regex, array_keys($nodes)));
        MatchingStorage::registerMatchingNodes($this->toString(), $found);

        return $found;
    }

    public function toString(): string
    {
        return $this->originalExpression;
    }
}
