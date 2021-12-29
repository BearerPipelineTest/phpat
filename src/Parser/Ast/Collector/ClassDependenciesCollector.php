<?php

namespace PhpAT\Parser\Ast\Collector;

use PhpAT\App\Configuration;
use PhpAT\Parser\Ast\Classmap\Classmap;
use PhpAT\Parser\Ast\FullClassName;
use PhpAT\Parser\Ast\Traverser\TraverseContext;
use PhpAT\Parser\Ast\Type\PhpStanDocTypeNodeResolver;
use PhpAT\Parser\Ast\Type\PhpType;
use PhpAT\Parser\Relation\AbstractRelation;
use PhpParser\Comment\Doc;
use PhpParser\NameContext;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Class ClassDependenciesCollector
 * @package PhpAT\Parser\Ast\Collector
 * Based on maglnet/ComposerRequireChecker UsedSymbolCollector
 * Copyright (c) 2015 Marco Pivetta | MIT License
 */
class ClassDependenciesCollector extends NodeVisitorAbstract
{
    /** @var array<AbstractRelation> */
    protected array $results = [];
    private Configuration $configuration;
    private PhpStanDocTypeNodeResolver $docTypeResolver;
    private NameContext $context;

    public function __construct(
        Configuration $configuration,
        PhpStanDocTypeNodeResolver $docTypeResolver,
        NameContext $context
    ) {
        $this->configuration   = $configuration;
        $this->docTypeResolver = $docTypeResolver;
        $this->context         = $context;
    }

    /**
     * @return array<AbstractRelation>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    public function beforeTraverse(array $nodes)
    {
        $this->results = [];

        return $nodes;
    }

    public function leaveNode(Node $node)
    {
        $this->recordClassExpressionUsage($node);
        $this->recordCatchUsage($node);
        //$this->recordFunctionCallUsage($node);
        $this->recordClassPropertyTypesUsage($node);
        $this->recordFunctionParameterTypesUsage($node);
        $this->recordFunctionReturnTypeUsage($node);
        //$this->recordConstantFetchUsage($node);
        $this->recordExtendsUsage($node);
        $this->recordImplementsUsage($node);
        $this->recordTraitUsage($node);
        $this->recordDocBlockUsage($node);
        $this->recordAttributeUsage($node);

        return $node;
    }

    private function recordClassExpressionUsage(Node $node)
    {
        if (
            $node instanceof Node\Expr\StaticCall
            || $node instanceof Node\Expr\StaticPropertyFetch
            || $node instanceof Node\Expr\ClassConstFetch
            || $node instanceof Node\Expr\New_
            || $node instanceof Node\Expr\Instanceof_
        ) {
            $this->registerTypeAsDependency($node->class);
        }
    }

    private function recordCatchUsage(Node $node)
    {
        if ($node instanceof Node\Stmt\Catch_) {
            foreach ($node->types as $type) {
                $this->registerTypeAsDependency($type);
            }
        }
    }

    private function recordExtendsUsage(Node $node)
    {
        if (
            $node instanceof Node\Stmt\ClassLike
            && isset($node->extends)
            && $node->extends instanceof Node\Name\FullyQualified
        ) {
            $this->registerTypeAsDependency($node->extends);
        }
    }

    private function recordImplementsUsage(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassLike) {
            /** @phpstan-ignore-next-line */
            foreach (array_filter($node->implements ?? []) as $implements) {
                $this->registerTypeAsDependency($implements);
            }
        }
    }

    private function recordTraitUsage(Node $node)
    {
        if ($node instanceof Node\Stmt\TraitUse) {
            foreach ($node->traits as $trait) {
                $this->registerTypeAsDependency($trait);
            }
        }
    }

    private function recordClassPropertyTypesUsage(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassLike) {
            foreach ($node->getProperties() as $property) {
                if ($property->type instanceof Node\Name\FullyQualified) {
                    $this->registerTypeAsDependency($property->type);
                }
            }
        }
    }

    private function recordFunctionParameterTypesUsage(Node $node): void
    {
        if ($node instanceof Node\Stmt\Function_ || $node instanceof Node\Stmt\ClassMethod) {
            foreach ($node->getParams() as $param) {
                $this->registerTypeAsDependency($param->type);
            }
        }
    }

    private function recordFunctionReturnTypeUsage(Node $node): void
    {
        if ($node instanceof Node\Stmt\Function_ || $node instanceof Node\Stmt\ClassMethod) {
            $this->registerTypeAsDependency($node->getReturnType());
        }
    }
    /*
        private function recordConstantFetchUsage(Node $node): void
        {
            if (
                $node instanceof Node\Expr\ConstFetch
                && !PhpType::isSpecialType($node->name->toString())
                && !PhpType::isBuiltinType($node->name->toString())
                && !PhpType::isCoreConstant($node->name->toString())
            ) {
                $this->registerTypeAsDependency($node->name);
            }
        }
    */
    private function recordDocBlockUsage(Node $node)
    {
        if ($this->configuration->getIgnoreDocBlocks()) {
            return;
        }

        $doc = $node->getDocComment();
        if (!$doc instanceof Doc) {
            return;
        }

        $names = $this->docTypeResolver->getBlockClassNames($this->context, $doc->getText());
        foreach ($names as $name) {
            $this->registerTypeAsDependency(new Node\Identifier($name));
        }
    }

    private function recordAttributeUsage(Node $node)
    {
        if ($node instanceof Node\AttributeGroup) {
            foreach ($node->attrs as $attr) {
                $this->registerTypeAsDependency($attr->name);
            }
        }
    }

    private function registerTypeAsDependency($type): void
    {
        if (
            ($type instanceof Node\Name\FullyQualified || $type instanceof Node\Identifier)
            && TraverseContext::className() !== null
        ) {
            Classmap::registerClassDepends(
                TraverseContext::className(),
                FullClassName::createFromFQCN($type->toString()),
                $type->getStartLine(),
                $type->getEndLine()
            );
            return;
        }

        if ($type instanceof Node\NullableType) {
            $this->registerTypeAsDependency($type->type);
            return;
        }

        if ($type instanceof Node\UnionType || $type instanceof Node\IntersectionType) {
            foreach ($type->types as $t) {
                $this->registerTypeAsDependency($t);
            }
        }
    }
}
