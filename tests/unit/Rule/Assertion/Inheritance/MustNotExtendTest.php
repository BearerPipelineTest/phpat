<?php

namespace Tests\PhpAT\unit\Rule\Assertion\Inheritance;

use PhpAT\Parser\Ast\FullClassName;
use PhpAT\Rule\Assertion\Inheritance\MustNotExtend;
use Tests\PhpAT\unit\Rule\Assertion\AbstractAssertionTestCase;

class MustNotExtendTest extends AbstractAssertionTestCase
{
    public function dataProvider(): array
    {
        return [
            [
                FullClassName::createFromFQCN('Example\ClassExample'),
                [FullClassName::createFromFQCN('NotARealParent')],
                [],
                $this->getMap(),
                [true]
            ],
            //it fails because it extends Example\ParentClassExample
            [
                FullClassName::createFromFQCN('Example\ClassExample'),
                [FullClassName::createFromFQCN('Example\ParentClassExample')],
                [],
                $this->getMap(),
                [false]
            ]
       ];
    }
    protected function getTestedClassName(): string
    {
        return MustNotExtend::class;
    }
}
