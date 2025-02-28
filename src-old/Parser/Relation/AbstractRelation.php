<?php

namespace PHPatOld\Parser\Relation;

use PHPatOld\Parser\Ast\FullClassName;

class AbstractRelation
{
    public FullClassName $relatedClass;
    public int $startLine;
    public int $endLine;

    public function __construct(FullClassName $relatedClass, int $startLine, int $endLine)
    {
        $this->relatedClass = $relatedClass;
        $this->startLine    = $startLine;
        $this->endLine      = $endLine;
    }
}
