<?php

namespace PhpAT\Parser\Ast\Collector;

use PhpAT\Parser\Ast\Classmap\Classmap;
use PhpAT\Parser\Ast\FullClassName;
use PhpAT\Parser\Ast\Traverser\TraverseContext;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class InterfaceCollector extends NodeVisitorAbstract
{
    public function leaveNode(Node $node)
    {
        if (
            $node instanceof Node\Stmt\ClassLike
            && $node->name !== null
            && (isset($node->implements) && $node->implements !== null)
        ) {
            foreach ($node->implements as $implements) {
                if ($implements instanceof Node\Name\FullyQualified) {
                    Classmap::registerClassImplements(
                        TraverseContext::className(),
                        new FullClassName($implements->toString()),
                        $implements->getStartLine(),
                        $implements->getEndLine()
                    );
                }
            }
        }

        return $node;
    }
}
