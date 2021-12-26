<?php

namespace PhpAT\Selector\Storage;

use PhpAT\Parser\Ast\SrcNode;

class MatchingStorage
{
    /** @var array<string, array<SrcNode>>|null */
    private static array $nodesMatching = [];

    /**
     * @param array<SrcNode> $nodes
     */
    public static function registerMatchingNodes(string $key, array $nodes): void
    {
        self::$nodesMatching[$key] = $nodes;
    }

    /**
     * @return array<SrcNode>|null $nodes
     */
    public static function getMatchingNodes(string $key): ?array
    {
        return self::$nodesMatching[$key] ?? null;
    }
}
